<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use App\Models\TeacherSubjectLoad;
use App\Models\Attendance;
use App\Models\ClassRecord;
use App\Models\StudentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class StudentNavigationController extends Controller
{
    /**
     * Display the student dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function viewDashboard()
    {
        try {
            // Ensure the user is authenticated and is a student
            if (!Auth::check() || Auth::user()->role !== User::ROLE_STUDENT) {
                return redirect()->route('login')->with('error', 'Unauthorized access.');
            }

            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                Log::error('Student record not found for user', ['user_id' => $user->id]);
                return redirect()->route('login')->with('error', 'Student profile not found.');
            }

            // Fetch current student status
            $currentStatus = $student->currentStatus()->with('schoolYear')->first();

            // Initialize dashboard data
            $dashboard = [
                'enrolled_subjects' => 0,
                'grade_level' => 'N/A',
                'section' => 'N/A',
                'school_year' => 'N/A',
                'attendance_count' => 0,
                'class_record_count' => 0,
                'average_grade' => 'N/A',
            ];

            if ($currentStatus && $currentStatus->schoolYear && $currentStatus->status === 'ENROLLED') {
                // Fetch enrolled subjects
                $dashboard['enrolled_subjects'] = TeacherSubjectLoad::where('grade_level', $currentStatus->grade_level)
                    ->where('section', $currentStatus->section)
                    ->where('school_year_id', $currentStatus->school_year_id)
                    ->count();

                // Set grade level, section, and school year
                $dashboard['grade_level'] = $currentStatus->grade_level;
                $dashboard['section'] = $currentStatus->section;
                $dashboard['school_year'] = $currentStatus->schoolYear->school_year;

                // Fetch attendance count
                $dashboard['attendance_count'] = Attendance::where('student_id', $student->id)
                    ->where('school_year_id', $currentStatus->school_year_id)
                    ->count();

                // Fetch class record count
                $dashboard['class_record_count'] = ClassRecord::where('student_id', $student->id)
                    ->where('school_year_id', $currentStatus->school_year_id)
                    ->count();

                // Fetch grades and calculate average
                $gradesResponse = $this->getStudentGrades($currentStatus->grade_level, $currentStatus->section);
                if ($gradesResponse->getStatusCode() === 200) {
                    $grades = json_decode($gradesResponse->getContent(), true)['data'] ?? [];
                    $finalGrades = array_filter(array_column($grades, 'final_grade'), 'is_numeric');
                    $dashboard['average_grade'] = !empty($finalGrades) ? round(array_sum($finalGrades) / count($finalGrades), 2) : 'N/A';
                }
            }

            return view('student.dashboard', compact('dashboard'));
        } catch (\Exception $e) {
            Log::error('Error loading student dashboard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while loading the dashboard.');
        }
    }
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

            return response()->json($grades);
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
            ->join('school_years', 'teacher_subject_loads.school_year_id', '=', 'school_years.id')
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
                'school_years.school_year as school_year',
                DB::raw("CONCAT(
                    teachers.first_name, ' ',
                    COALESCE(teachers.middle_name, ''), ' ',
                    teachers.last_name, ' ',
                    COALESCE(teachers.extension_name, '')
                ) as teacher_name"),
                DB::raw("UPPER(TRIM(CONCAT(
                    students.first_name, ' ',
                    IF(students.middle_name IS NOT NULL AND students.middle_name != '', CONCAT(students.middle_name, ' '), ''),
                    students.last_name,
                    IF(students.extension_name IS NOT NULL AND students.extension_name != '', CONCAT(' ', students.extension_name), '')
                ))) as student_name")
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
        // $fileName = "classrecord_{$subjectRecord->teacher_subject_load_id}_{$subjectRecord->school_year_id}_" .
        //     strtoupper(str_replace(' ', '_', $subjectRecord->subject_name)) . '.xlsx';

        $fileName = '(' . $subjectRecord->school_year . ')' . strtoupper(str_replace(' ', '_', $subjectRecord->last_name)) .
            '-Grade' . $subjectRecord->grade_level . '-' . $subjectRecord->section . '-' . $subjectRecord->subject_name . '.xlsx';
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

    public function viewAttendances()
    {
        return view('student.attendances');
    }

    public function getStudentAttendance()
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== \App\Models\User::ROLE_STUDENT) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Unauthorized access or student not found.',
                ], 403);
            }

            $student = $user->student;
            if (!$student) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Student profile not found.',
                ], 404);
            }

            $attendanceSummary = Attendance::query()
                ->join('teacher_subject_loads', 'attendances.subject_load_id', '=', 'teacher_subject_loads.id')
                ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
                ->where('attendances.student_id', $student->id)
                ->select(
                    'teacher_subject_loads.grade_level',
                    'teacher_subject_loads.section',
                    'subjects.subject_code',
                    'subjects.subject_name',
                    DB::raw('SUM(CASE WHEN attendances.status = "PRESENT" THEN 1 ELSE 0 END) as total_present'),
                    DB::raw('COUNT(attendances.id) as total_attendance')
                )
                ->groupBy(
                    'teacher_subject_loads.grade_level',
                    'teacher_subject_loads.section',
                    'subjects.subject_code',
                    'subjects.subject_name'
                )
                ->orderBy('teacher_subject_loads.grade_level')
                ->orderBy('teacher_subject_loads.section')
                ->get();

            if ($attendanceSummary->isEmpty()) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'No attendance records found for the student.',
                ]);
            }

            $grouped = $attendanceSummary->groupBy(function ($record) {
                return "{$record->grade_level}-{$record->section}";
            });

            $response = [];

            foreach ($grouped as $key => $records) {
                [$gradeLevel, $section] = explode('-', $key);
                $subjects = [];

                foreach ($records as $record) {
                    $percentage = $record->total_attendance > 0
                        ? round(($record->total_present / $record->total_attendance) * 100, 2)
                        : 0;

                    $subjects[] = [
                        'subject_code' => $record->subject_code,
                        'subject_name' => $record->subject_name,
                        'attendance_summary' => "{$record->total_present} / {$record->total_attendance}",
                        'attendance_percentage' => $percentage,
                    ];
                }

                $response[] = [
                    'grade_level' => $gradeLevel,
                    'section' => $section,
                    'subjects' => $subjects,
                ];
            }

            return response()->json([
                'valid' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student attendance: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'msg' => 'Failed to retrieve student attendance. Please try again later.',
            ], 500);
        }
    }

    public function grades()
    {
        $user = auth()->user();
        $student = $user->student;

        $gradeLevels = StudentStatus::where('student_id', $student->id)
            ->where('status', 'ENROLLED')
            ->get();

        if ($gradeLevels->isEmpty()) {
            return redirect()->back()->with('error', 'No enrolled grade level found for the student.');
        }

        return view('student.grades', compact('gradeLevels'));
    }

    public function classRecords()
    {
        return view('student.class-records');
    }

    public function getStudentSubject()
    {
        try {
            // Get the authenticated user and their student record
            $user = Auth::user();
            if (!$user || $user->role !== \App\Models\User::ROLE_STUDENT) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Unauthorized access or student not found.',
                ], 403);
            }

            $student = $user->student;
            if (!$student) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Student profile not found.',
                ], 404);
            }

            // Fetch subjects for the student based on their current status
            $subjects = TeacherSubjectLoad::query()
                ->join('student_statuses', function ($join) {
                    $join->on('teacher_subject_loads.school_year_id', '=', 'student_statuses.school_year_id')
                        ->on('teacher_subject_loads.grade_level', '=', 'student_statuses.grade_level')
                        ->on('teacher_subject_loads.section', '=', 'student_statuses.section');
                })
                ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
                ->join('teachers', 'teacher_subject_loads.teacher_id', '=', 'teachers.id')
                ->where('student_statuses.student_id', $student->id)
                ->select(
                    'teacher_subject_loads.id as teacher_subject_load_id',
                    'subjects.subject_code',
                    'subjects.subject_name',
                    'teachers.first_name',
                    'teachers.middle_name',
                    'teachers.last_name',
                    'teachers.extension_name'
                )
                ->get();

            if ($subjects->isEmpty()) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'No subjects found for the student.',
                ]);
            }

            // Map the results to the desired response format
            $response = $subjects->map(function ($item, $index) {
                // Use FullNameTrait to get teacher's full name
                $teacher = new \App\Models\Teacher([
                    'first_name' => $item->first_name,
                    'middle_name' => $item->middle_name,
                    'last_name' => $item->last_name,
                    'extension_name' => $item->extension_name,
                ]);

                return [
                    'count' => $index + 1,
                    'subject_code' => $item->subject_code,
                    'subject_name' => $item->subject_name,
                    'teacher_name' => $teacher->full_name_with_extension,
                    'action' => '<button class="btn btn-md btn-primary" title="View Records" onclick="view(' . $item->teacher_subject_load_id . ')"><i class="fa fa-eye"></i></button>',
                ];
            })->toArray();

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error fetching student subjects: ' . $e->getMessage());

            return response()->json([
                'valid' => false,
                'msg' => 'Failed to retrieve student subjects. Please try again later.',
            ], 500);
        }
    }

    public function viewStudentRecords($teacher_subject_load_id)
    {
        try {
            // Get the authenticated user and their student record
            $user = Auth::user();
            if (!$user || $user->role !== \App\Models\User::ROLE_STUDENT) {
                abort(403, 'Unauthorized access or student not found.');
            }

            $student = $user->student;
            if (!$student) {
                abort(404, 'Student profile not found.');
            }

            // Fetch TeacherSubjectLoad details
            $subjectTeacher = TeacherSubjectLoad::query()
                ->join('teachers', 'teacher_subject_loads.teacher_id', '=', 'teachers.id')
                ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
                ->join('school_years', 'teacher_subject_loads.school_year_id', '=', 'school_years.id')
                ->where('teacher_subject_loads.id', $teacher_subject_load_id)
                ->select(
                    'teacher_subject_loads.id as teacher_subject_load_id',
                    'teacher_subject_loads.grade_level',
                    'teacher_subject_loads.section',
                    'school_years.school_year',
                    'subjects.subject_code',
                    'subjects.subject_name',
                    'teachers.first_name',
                    'teachers.middle_name',
                    'teachers.last_name',
                    'teachers.extension_name'
                )
                ->first();

            if (!$subjectTeacher) {
                abort(404, 'Subject load not found.');
            }

            // Verify the student is enrolled in this class
            // $isEnrolled = StudentStatus::where('student_id', $student->id)
            //     ->where('school_year_id', $subjectTeacher->school_year_id)
            //     ->where('grade_level', $subjectTeacher->grade_level)
            //     ->where('section', $subjectTeacher->section)
            //     ->exists();

            // if (!$isEnrolled) {
            //     abort(403, 'You are not enrolled in this class.');
            // }

            // Use FullNameTrait for teacher name
            $teacher = new \App\Models\Teacher([
                'first_name' => $subjectTeacher->first_name,
                'middle_name' => $subjectTeacher->middle_name,
                'last_name' => $subjectTeacher->last_name,
                'extension_name' => $subjectTeacher->extension_name,
            ]);
            $subjectTeacher->teacher_name = $teacher->full_name_with_extension;

            return view('student.view-class-records', compact('subjectTeacher'));
        } catch (\Exception $e) {
            Log::error('Error fetching student class records: ' . $e->getMessage());
            abort(500, 'Failed to retrieve class records. Please try again later.');
        }
    }
}
