<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    /**
     * Retrieve student grades for a given grade level and section.
     *
     * @param int $grade_level
     * @param string $section
     * @return JsonResponse
     */
    public function getStudentGrades($grade_level, $section): JsonResponse
    {
        try {
            // Step 1: Validate input parameters
            $validator = Validator::make([
                'grade_level' => $grade_level,
                'section' => $section,
            ], [
                'grade_level' => 'required|in:7,8,9,10,11,12',
                'section' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Invalid grade level or section.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Step 2: Validate user session and retrieve student LRN
            if (!auth()->user()) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'User session not found.',
                ], 401);
            }

            $user = auth()->user();
            if (!$user || $user->role !== 'student') {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Unauthorized access or user not found.',
                ], 403);
            }
            $student_lrn = $user->username;

            // Step 3: Fetch subject loads for the grade level and section
            $subjectLoads = DB::table('teacher_subject_loads')
                ->join('student_statuses', function ($join) {
                    $join->on('teacher_subject_loads.school_year_id', '=', 'student_statuses.school_year_id')
                         ->on('teacher_subject_loads.section', '=', 'student_statuses.section')
                         ->on('teacher_subject_loads.grade_level', '=', 'student_statuses.grade_level');
                })
                ->join('students', 'student_statuses.student_id', '=', 'students.id')
                ->where('students.student_lrn', $student_lrn)
                ->where('student_statuses.grade_level', $grade_level)
                ->where('student_statuses.section', $section)
                ->where('student_statuses.status', 'ENROLLED')
                ->select('teacher_subject_loads.id as teacher_subject_load_id')
                ->get();

            // Step 4: Process each subject load
            $grades = [];
            foreach ($subjectLoads as $load) {
                $subjectRecord = $this->fetchSubjectRecord($load->teacher_subject_load_id, $student_lrn);
                if (!$subjectRecord) {
                    continue;
                }

                $gradeData = $this->fetchGradeData($subjectRecord);
                if ($gradeData) {
                    $grades[] = $gradeData;
                }
            }

            // Step 5: Return grades or not-found message
            if (empty($grades)) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'No grades found for the specified grade level and section.',
                ], 404);
            }

            return response()->json([
                'valid' => true,
                'data' => $grades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving grades', [
                'error' => $e->getMessage(),
                'grade_level' => $grade_level,
                'section' => $section,
                'student_lrn' => $student_lrn ?? 'N/A',
            ]);
            return response()->json([
                'valid' => false,
                'msg' => 'An error occurred while retrieving the grades.',
            ], 500);
        }
    }

    /**
     * Fetch detailed subject record for a given teacher subject load and student LRN.
     *
     * @param int $teacher_subject_load_id
     * @param string $student_lrn
     * @return object|null
     */
    private function fetchSubjectRecord($teacher_subject_load_id, $student_lrn)
    {
        return DB::table('class_records')
            ->join('teacher_subject_loads', 'class_records.teacher_subject_load_id', '=', 'teacher_subject_loads.id')
            ->join('teachers', 'teacher_subject_loads.teacher_id', '=', 'teachers.id')
            ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
            ->join('students', 'class_records.student_id', '=', 'students.id')
            ->where('class_records.teacher_subject_load_id', $teacher_subject_load_id)
            ->where('students.student_lrn', $student_lrn)
            ->select(
                'subjects.subject_code',
                'subjects.subject_name',
                'teacher_subject_loads.grade_level',
                'teacher_subject_loads.section',
                'teacher_subject_loads.school_year_id',
                'class_records.teacher_subject_load_id',
                'students.sex',
                'teachers.last_name',
                DB::raw("CONCAT(
                    teachers.first_name, ' ',
                    COALESCE(teachers.middle_name, ''), ' ',
                    teachers.last_name, ' ',
                    COALESCE(teachers.extension_name, '')
                ) as teacher_name"),
                DB::raw("CONCAT(
                    students.last_name, ', ',
                    students.first_name, ' ',
                    COALESCE(students.middle_name, '')
                ) as student_name")
            )
            ->distinct()
            ->first();
    }

    /**
     * Fetch grade data from the Excel file for a given subject record.
     *
     * @param object $subjectRecord
     * @return array|null
     */
    private function fetchGradeData($subjectRecord)
    {
        // Construct a safer file name using teacher_subject_load_id
        $fileName = "classrecord_{$subjectRecord->teacher_subject_load_id}_{$subjectRecord->school_year_id}_" .
                    strtoupper(str_replace(' ', '_', $subjectRecord->subject_name)) . '.xlsx';
        $filePath = public_path("classrecord/{$subjectRecord->teacher_subject_load_id}/{$fileName}");

        if (!file_exists($filePath)) {
            Log::warning("Grade file not found", [
                'file_path' => $filePath,
                'teacher_subject_load_id' => $subjectRecord->teacher_subject_load_id,
            ]);
            return null;
        }

        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('SUMMARY OF QUARTERLY GRADES');

            if (!$sheet) {
                Log::warning("Sheet 'SUMMARY OF QUARTERLY GRADES' not found in file", [
                    'file_path' => $filePath,
                ]);
                return null;
            }

            $subjectName = $sheet->getCell('W9')->getCalculatedValue();
            $startRow = ($subjectRecord->sex === 'Male') ? 13 : 64;
            $endRow = ($subjectRecord->sex === 'Male') ? 62 : 113;

            // Search for student dynamically
            for ($i = $startRow; $i <= $endRow; $i++) {
                $studentNameFromSheet = trim($sheet->getCell("B{$i}")->getCalculatedValue());
                if (strtolower(trim($studentNameFromSheet)) === strtolower(trim($subjectRecord->student_name))) {
                    return [
                        'subject_name' => $subjectName,
                        'subject_code' => $subjectRecord->subject_code,
                        'teacher_name' => $subjectRecord->teacher_name,
                        '1st_quarter' => $sheet->getCell("F{$i}")->getCalculatedValue(),
                        '2nd_quarter' => $sheet->getCell("J{$i}")->getCalculatedValue(),
                        '3rd_quarter' => $sheet->getCell("N{$i}")->getCalculatedValue(),
                        '4th_quarter' => $sheet->getCell("R{$i}")->getCalculatedValue(),
                        'final_grade' => $sheet->getCell("V{$i}")->getCalculatedValue(),
                        'remarks' => $sheet->getCell("Z{$i}")->getCalculatedValue(),
                    ];
                }
            }

            Log::warning("Student not found in grade sheet", [
                'student_name' => $subjectRecord->student_name,
                'file_path' => $filePath,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Error processing grade file", [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
