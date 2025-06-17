<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SchoolYear;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportCardService
{
    protected $templatePath = 'report-cards/REPORT_CARD.xlsx';
    protected $storageDisk = 'local';
    protected $classRecordBasePath = 'class-records';

    public function generate($studentId, $schoolYearId)
    {
        try {
            Log::info('Generating report card', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
            ]);

            // Fetch student and school year
            $student = Student::findOrFail($studentId);
            $schoolYear = SchoolYear::findOrFail($schoolYearId);

            // Fetch student status and adviser info
            $studentInfo = $this->getStudentInfo($studentId, $schoolYearId);
            if (!$studentInfo) {
                throw new Exception('Student or adviser information not found.');
            }

            // Fetch subject grades
            $subjectGrades = $this->getSubjectGrades($studentId, $schoolYearId, $studentInfo->student_name, $studentInfo->sex);
            if (empty($subjectGrades)) {
                throw new Exception('No class records found for the specified student and school year.');
            }

            // Load and populate spreadsheet
            $spreadsheet = $this->loadTemplate();
            $this->populateFrontSheet($spreadsheet, $studentInfo, $schoolYear);
            $this->populateBackSheet($spreadsheet, $subjectGrades);

            // Save report card
            $filePath = $this->saveReportCard($studentId, $studentInfo, $schoolYear, $subjectGrades);
            $fileName = basename($filePath);
            $downloadUrl = asset($filePath);
            $download = '<a href="' . $downloadUrl . '" target="_blank" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>';

            Log::info('Report card generated', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
                'file' => $filePath,
            ]);

            return [
                'valid' => true,
                'msg' => 'Report card generated successfully.',
                'file_path' => $filePath,
                'file_name' => $fileName,
                'download' => $download,
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate report card', [
                'student_id' => $studentId,
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to generate report card: ' . $e->getMessage(),
            ];
        }
    }

    protected function getStudentInfo($studentId, $schoolYearId)
    {
        return DB::table('students')
            ->join('student_statuses', 'students.id', '=', 'student_statuses.student_id')
            ->join('advisers', 'student_statuses.adviser_id', '=', 'advisers.id')
            ->join('teachers', 'advisers.teacher_id', '=', 'teachers.id')
            ->where('students.id', $studentId)
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
                    teachers.first_name, ' ',
                    COALESCE(LEFT(teachers.middle_name, 1) || '.', ''), ' ',
                    teachers.last_name, ' ',
                    COALESCE(teachers.extension_name, '')
                ) as teacher_name")
            )
            ->first();
    }

    protected function getSubjectGrades($studentId, $schoolYearId, $studentName, $sex)
    {
        $records = DB::table('class_records')
            ->join('teacher_subject_loads', 'class_records.teacher_subject_load_id', '=', 'teacher_subject_loads.id')
            ->join('teachers', 'teacher_subject_loads.teacher_id', '=', 'teachers.id')
            ->join('subjects', 'teacher_subject_loads.subject_id', '=', 'subjects.id')
            ->where('class_records.student_id', $studentId)
            ->where('class_records.school_year_id', $schoolYearId)
            ->select(
                'subjects.subject_name',
                'teacher_subject_loads.id as teacher_subject_load_id',
                'teacher_subject_loads.grade_level',
                'teacher_subject_loads.section',
                'teachers.last_name as teacher_last_name',
                'teacher_subject_loads.school_year_id'
            )
            ->distinct()
            ->get();

        $subjectGrades = [];
        foreach ($records as $record) {
            $fileName = "({$record->school_year_id})" . strtoupper(str_replace(' ', '_', $record->teacher_last_name)) .
                "-Grade{$record->grade_level}-{$record->section}-{$record->subject_name}.xlsx";
            $filePath = public_path("{$this->classRecordBasePath}/{$record->teacher_subject_load_id}/{$fileName}");

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
            $startRow = ($sex === 'Male') ? 13 : 64;
            $endRow = ($sex === 'Male') ? 62 : 113;

            for ($i = $startRow; $i <= $endRow; $i++) {
                $studentNameFromSheet = trim($sheet->getCell("B{$i}")->getCalculatedValue());
                if (strtolower($studentNameFromSheet) === strtolower($studentName)) {
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

        return $subjectGrades;
    }

    protected function loadTemplate()
    {
        $templateFullPath = public_path($this->templatePath);

        if (!file_exists($templateFullPath)) {
            throw new Exception('Report card template file not found at ' . $templateFullPath);
        }

        return IOFactory::load($templateFullPath);
    }

    protected function populateFrontSheet($spreadsheet, $studentInfo, $schoolYear)
    {
        $sheet = $spreadsheet->getSheetByName('Front');
        if (!$sheet) {
            throw new Exception('Sheet "Front" not found in the template.');
        }

        $age = $studentInfo->birthday ? Carbon::parse($studentInfo->birthday)->age : 'N/A';
        if ($age === 'N/A') {
            Log::warning('Birthday is not set or invalid for student_id: ' . $studentInfo->student_id);
        }

        $sheet->setCellValue('P12', strtoupper($studentInfo->student_name));
        $sheet->setCellValue('W13', strtoupper($studentInfo->student_id));
        $sheet->setCellValue('P13', strtoupper($age));
        $sheet->setCellValue('T13', strtoupper($studentInfo->sex));
        $sheet->setCellValue('Q14', strtoupper($studentInfo->grade_level));
        $sheet->setCellValue('U14', strtoupper($studentInfo->section));
        $sheet->setCellValue('R15', strtoupper($schoolYear->school_year));
        $sheet->setCellValue('U22', strtoupper($studentInfo->teacher_name));
        $sheet->setCellValue('U32', strtoupper($studentInfo->teacher_name));
    }

    protected function populateBackSheet($spreadsheet, $subjectGrades)
    {
        $sheet = $spreadsheet->getSheetByName('Back');
        $rowMap = [
            'FILIPINO' => 5,
            'ENGLISH' => 7,
            'MATHEMATICS' => 9,
            'SCIENCE' => 11,
            'ARALING PANLIPUNAN' => 13,
            'EDUKASYON SA PAGPAPAKATAO' => 21,
            'T.L.E' => 23,
        ];

        foreach ($subjectGrades as $grades) {
            $subjectName = strtoupper(trim($grades['subject_name']));
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
    }

    protected function saveReportCard($studentId, $studentInfo, $schoolYear, $subjectGrades)
    {
        $fileName = "({$schoolYear->school_year})" . strtoupper(str_replace(' ', '_', $studentInfo->student_name)) .
            "-Grade{$studentInfo->grade_level}-{$studentInfo->section}.xlsx";
        $dirPath = public_path("report-cards/{$studentId}");
        $filePath = "{$dirPath}/{$fileName}";

        // Ensure directory exists
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $spreadsheet = $this->loadTemplate(); // fresh template
        $this->populateFrontSheet($spreadsheet, $studentInfo, $schoolYear);
        $this->populateBackSheet($spreadsheet, $subjectGrades);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return str_replace(public_path(), '', $filePath); // Return relative path
    }
}
