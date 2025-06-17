<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherSubjectLoad;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ReportCardController extends Controller
{
    public function generateReportCard($studentId, $schoolYearId)
    {
        try {
            Log::info('Generating report card', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
            ]);

            // Fetch student class records
            $studentClassRecords = DB::table('class_records')
                ->join('teacher_subject_loads', 'class_records.teacher_subject_load_id', '=', 'teacher_subject_loads.id')
                ->join('teachers', 'teacher_subject_loads.teacher_id', '=', 'teachers.id')
                ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
                ->join('students', 'class_records.student_id', '=', 'students.id')
                ->where('class_records.student_id', $studentId)
                ->where('class_records.school_year_id', $schoolYearId)
                ->select(
                    'subjects.subject_name',
                    'teacher_subject_loads.grade_level',
                    'teacher_subject_loads.section',
                    'teacher_subject_loads.school_year_id',
                    'teacher_subject_loads.id as teacher_subject_load_id',
                    'teachers.last_name as teacher_last_name',
                    'students.sex',
                    DB::raw("CONCAT(
                    students.last_name,
                    ', ',
                    students.first_name,
                    ' ',
                    COALESCE(students.middle_name, '')
                ) as student_name")
                )
                ->distinct()
                ->get();

            if ($studentClassRecords->isEmpty()) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'No class records found for the specified student and school year.',
                ], 404);
            }

            $subjectGrades = [];

            foreach ($studentClassRecords as $record) {
                $folderDIR = $record->teacher_subject_load_id;
                $fileName = "({$record->school_year_id})" . strtoupper(str_replace(' ', '_', $record->teacher_last_name)) .
                    "-Grade{$record->grade_level}-{$record->section}-{$record->subject_name}.xlsx";
                $filePath = public_path("classrecord/{$folderDIR}/{$fileName}");

                if (!file_exists($filePath)) {
                    Log::warning("Class record file not found: {$filePath}");
                    continue;
                }

                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getSheetByName('SUMMARY OF QUARTERLY GRADES');

                if (!$sheet) {
                    Log::warning("Sheet not found: 'SUMMARY OF QUARTERLY GRADES' in {$filePath}");
                    continue;
                }

                $subjectName = $sheet->getCell('W9')->getCalculatedValue();
                $startRow = ($record->sex === 'Male') ? 13 : 64;
                $endRow = ($record->sex === 'Male') ? 62 : 113;

                for ($i = $startRow; $i <= $endRow; $i++) {
                    $studentNameFromSheet = trim($sheet->getCell("B{$i}")->getCalculatedValue());
                    if (strtolower($studentNameFromSheet) === strtolower($record->student_name)) {
                        $subjectGrades[] = [
                            'subject_name' => $subjectName,
                            '1st_quarter' => $sheet->getCell("F{$i}")->getCalculatedValue(),
                            '2nd_quarter' => $sheet->getCell("J{$i}")->getCalculatedValue(),
                            '3rd_quarter' => $sheet->getCell("N{$i}")->getCalculatedValue(),
                            '4th_quarter' => $sheet->getCell("R{$i}")->getCalculatedValue(),
                            'final_grade' => $sheet->getCell("V{$i}")->getCalculatedValue(),
                            'remarks' => $sheet->getCell("Z{$i}")->getCalculatedValue(),
                        ];
                        break;
                    }
                }
            }

            // Fetch student info
            $studentInfo = Student::where('students.id', $studentId)
                ->join('student_statuses', 'students.id', '=', 'student_statuses.student_id')
                ->join('advisers', 'student_statuses.adviser_id', '=', 'advisers.id')
                ->join('teachers', 'advisers.teacher_id', '=', 'teachers.id')
                ->where('student_statuses.school_year_id', $schoolYearId)
                ->select(
                    'student_statuses.grade_level',
                    'student_statuses.section',
                    'student_statuses.school_year_id',
                    'students.id as student_id',
                    'students.sex',
                    'students.birthday',
                    DB::raw("CONCAT(
                    students.last_name,
                    ', ',
                    students.first_name,
                    ' ',
                    COALESCE(students.middle_name, '')
                ) as student_name"),
                    DB::raw("CONCAT(
                        teachers.last_name,
                        ', ',
                        teachers.first_name,
                        ' ',
                        COALESCE(teachers.middle_name, '')
                    ) as teacher_name")
                )
                ->first();

            if (!$studentInfo) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Student or adviser information not found.',
                ], 404);
            }

            // Load report card template
            $templatePath = public_path('reportcard/REPORT_CARD.xlsx');
            if (!file_exists($templatePath)) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Report card template file not found.',
                ], 404);
            }

            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getSheetByName('Front');

            if (!$sheet) {
                return response()->json([
                    'valid' => false,
                    'msg' => 'Sheet "Front" not found in the template.',
                ], 500);
            }

            // Calculate age
            $age = $studentInfo->birthday ? Carbon::parse($studentInfo->birthday)->age : null;
            if (!$age) {
                Log::warning('Birthday is not set or invalid for student_id: ' . $studentId);
            }

            // Populate Front sheet
            $schoolYear = SchoolYear::findOrFail($schoolYearId)->school_year;
            $sheet->setCellValue('P12', strtoupper($studentInfo->student_name));
            $sheet->setCellValue('W13', strtoupper($studentInfo->student_id));
            $sheet->setCellValue('P13', strtoupper($age ?? 'N/A'));
            $sheet->setCellValue('T13', strtoupper($studentInfo->sex));
            $sheet->setCellValue('Q14', strtoupper($studentInfo->grade_level));
            $sheet->setCellValue('U14', strtoupper($studentInfo->section));
            $sheet->setCellValue('R15', strtoupper($schoolYear));
            $sheet->setCellValue('U22', strtoupper($studentInfo->teacher_name));
            $sheet->setCellValue('U32', strtoupper($studentInfo->teacher_name));

            // Populate Back sheet
            $sheet = $spreadsheet->getSheetByName('Back');
            foreach ($subjectGrades as $grades) {
                $subjectName = strtoupper(trim($grades['subject_name']));
                $rowMap = [
                    'FILIPINO' => 5,
                    'ENGLISH' => 7,
                    'MATHEMATICS' => 9,
                    'SCIENCE' => 11,
                    'ARALING PANLIPUNAN' => 13,
                    'EDUKASYON SA PAGPAPAKATAO' => 21,
                    'T.L.E' => 23,
                ];

                $row = $rowMap[$subjectName] ?? null;
                if ($row) {
                    $sheet->setCellValue("B{$row}", strtoupper($grades['1st_quarter']));
                    $sheet->setCellValue("C{$row}", strtoupper($grades['2nd_quarter']));
                    $sheet->setCellValue("D{$row}", strtoupper($grades['3rd_quarter']));
                    $sheet->setCellValue("E{$row}", strtoupper($grades['4th_quarter']));
                    $sheet->setCellValue("F{$row}", strtoupper($grades['final_grade']));
                    $sheet->setCellValue("G{$row}", strtoupper($grades['remarks']));
                }
            }

            // Save report card
            $directoryPath = public_path("reportcard/{$studentId}");
            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0777, true);
            }

            $fileName = "({$schoolYear})" . strtoupper(str_replace(' ', '_', $studentInfo->student_name)) .
                "-Grade{$studentInfo->grade_level}-{$studentInfo->section}.xlsx";
            $newFilePath = "{$directoryPath}/{$fileName}";

            $writer = new Xlsx($spreadsheet);
            $writer->save($newFilePath);

            // Generate download URL
            $downloadUrl = route('reportCards.downloadReportCard', [
                'studentId' => $studentId,
                'schoolYearId' => $schoolYearId
            ]);
            $download = '<a href="' . $downloadUrl . '" target="_blank" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>';

            Log::info('Report card generated', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
                'file' => $newFilePath
            ]);

            return response()->json([
                'valid' => true,
                'msg' => 'Report card generated successfully.',
                'file_path' => asset("reportcard/{$studentId}/{$fileName}"),
                'file_name' => $fileName,
                'download' => $download,
            ]);
        } catch (Exception $e) {
            Log::error('Error generating report card', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'valid' => false,
                'msg' => 'Failed to generate report card: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadReportCard($studentId, $schoolYearId)
    {
        try {
            $student = Student::findOrFail($studentId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);
            $studentStatus = $student->currentStatus()->where('school_year_id', $schoolYearId)->first();

            if (!$studentStatus) {
                abort(404, 'Student status not found for the specified school year.');
            }

            $fileName = "({$schoolYear->school_year})" . strtoupper(str_replace(' ', '_', $student->full_name_with_extension)) .
                "-Grade{$studentStatus->grade_level}-{$studentStatus->section}.xlsx";
            $filePath = public_path("reportcard/{$studentId}/{$fileName}");

            if (!file_exists($filePath)) {
                abort(404, 'Report card file not found.');
            }

            return response()->download($filePath, $fileName);
        } catch (Exception $e) {
            Log::error('Error downloading report card', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to download report card.');
        }
    }
}
