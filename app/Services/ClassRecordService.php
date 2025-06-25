<?php

namespace App\Services;

use App\Models\ClassRecord;
use App\Models\Student;
use App\Models\TeacherSubjectLoad;
use App\Models\SchoolYear;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ClassRecordService
{
    public function index($teacherId = null, $schoolYearId = null, $subjectLoadId = null)
    {
        try {
            Log::info('Fetching class records', [
                'teacher_id' => $teacherId,
                'school_year_id' => $schoolYearId,
                'subject_load_id' => $subjectLoadId,
            ]);

            $query = ClassRecord::with(['student', 'schoolYear', 'teacherSubjectLoad.subject', 'teacherSubjectLoad.teacher']);

            if ($teacherId) {
                $query->whereHas('teacherSubjectLoad', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                });
            }

            if ($schoolYearId) {
                $query->where('school_year_id', $schoolYearId);
            }

            if ($subjectLoadId) {
                $query->where('teacher_subject_load_id', $subjectLoadId);
            }

            $records = $query->get();

            $formattedRecords = $records->map(function ($record, $key) {
                $actions = '<button type="button" class="btn btn-md btn-primary" title="Edit" onclick="editRecord(' . $record->id . ')"><i class="fa fa-edit"></i></button>';
                return [
                    'count' => $key + 1,
                    'student_name' => $record->student->full_name_with_extension ?? 'N/A',
                    'subject_name' => $record->subjectLoad->subject->subject_name ?? 'N/A',
                    'records_name' => $record->records_name,
                    'records_type' => $record->records_type,
                    'quarter' => $record->quarter,
                    'student_score' => $record->student_score,
                    'total_score' => $record->total_score ?? 'N/A',
                    'school_year' => $record->schoolYear->school_year ?? 'N/A',
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched class records', ['count' => count($formattedRecords)]);
            return $formattedRecords;
        } catch (Exception $e) {
            Log::error('Failed to fetch class records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function bySubjectLoad($subjectLoadId, $quarter)
    {
        try {
            Log::info('Fetching class records by subject load and quarter', [
                'subject_load_id' => $subjectLoadId,
                'quarter' => $quarter,
            ]);

            $classRecords = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                ->where('quarter', $quarter)
                ->get();
            if ($classRecords->isEmpty()) {
                Log::info('No class records found for subject load', [
                    'subject_load_id' => $subjectLoadId,
                    'quarter' => $quarter,
                ]);
                return ['students' => [], 'scores' => []];
            }

            $subjectLoad = TeacherSubjectLoad::findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();
            $current = $subjectLoad->school_year_id === $schoolYear->id;

            $students = Student::whereHas('currentStatus.adviser', function ($query) use ($subjectLoad, $schoolYear) {
                $query->where('grade_level', $subjectLoad->grade_level)
                    ->where('section', $subjectLoad->section);
                // ->where('school_year_id', $schoolYear->id);
            })->with(['classRecords' => function ($query) use ($subjectLoadId, $quarter) {
                $query->where('teacher_subject_load_id', $subjectLoadId)
                    ->where('quarter', $quarter);
            }])->orderBy('last_name', 'asc');

            $score = [
                'writtenWorks' => array_fill(0, 10, ['score' => null, 'id' => null, 'quarter' => $quarter]),
                'performanceTasks' => array_fill(0, 10, ['score' => null, 'id' => null, 'quarter' => $quarter]),
                'quarterlyAssessment' => ['score' => null, 'id' => null, 'quarter' => $quarter],
            ];

            if (auth()->user()->role === User::ROLE_STUDENT) {
                $students = $students->where('id', auth()->user()->student->id)->get();
            } else {
                $students = $students->get();
            }

            $students = $students->sortBy(function ($student) {
                return $student->sex === 'Male' ? 0 : 1;
            });

            $formattedStudents = $students->map(function ($student) use ($quarter, &$score) {
                $writtenWorks = array_fill(0, 10, 0);
                $writtenWorksIds = array_fill(0, 10, null);
                $performanceTasks = array_fill(0, 10, 0);
                $performanceTasksIds = array_fill(0, 10, null);
                $quarterlyAssessment = 0;
                $quarterlyAssessmentId = null;

                foreach ($student->classRecords as $record) {
                    if ($record->records_type === 'Written Works') {
                        $index = (int)str_replace('Written Works ', '', $record->records_name) - 1;
                        if ($index >= 0 && $index < 10) {
                            $writtenWorks[$index] = $record->student_score;
                            $writtenWorksIds[$index] = $record->id;
                            $score['writtenWorks'][$index] = [
                                'score' => $record->total_score,
                                'id' => $record->id,
                                'quarter' => $quarter,
                            ];
                        }
                    } elseif ($record->records_type === 'Performance Tasks') {
                        $index = (int)str_replace('Performance Tasks ', '', $record->records_name) - 1;
                        if ($index >= 0 && $index < 10) {
                            $performanceTasks[$index] = $record->student_score;
                            $performanceTasksIds[$index] = $record->id;
                            $score['performanceTasks'][$index] = [
                                'score' => $record->total_score,
                                'id' => $record->id,
                                'quarter' => $quarter,
                            ];
                        }
                    } elseif ($record->records_type === 'Quarterly Assessment') {
                        $quarterlyAssessment = $record->student_score;
                        $quarterlyAssessmentId = $record->id;
                        $score['quarterlyAssessment'] = [
                            'score' => $record->total_score,
                            'id' => $record->id,
                            'quarter' => $quarter,
                        ];
                    }
                }

                return [
                    'name' => $student->full_name_with_extension,
                    'writtenWorks' => $writtenWorks,
                    'writtenWorksRecordsID' => $writtenWorksIds,
                    'performanceTasks' => $performanceTasks,
                    'performanceTasksRecordsID' => $performanceTasksIds,
                    'quarterlyAssessment' => $quarterlyAssessment,
                    'quarterlyAssessmentRecordsID' => $quarterlyAssessmentId,
                ];
            })->values()->toArray();

            Log::info('Successfully fetched class records by subject load', ['count' => count($formattedStudents), 'current' => $current]);
            return ['students' => $formattedStudents, 'scores' => $score, 'current' => $current];
        } catch (Exception $e) {
            Log::error('Failed to fetch class records by subject load', [
                'subject_load_id' => $subjectLoadId,
                'quarter' => $quarter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['students' => [], 'scores' => []];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Storing class record', ['data' => $data]);

            $this->validateRecord($data);

            $record = DB::transaction(function () use ($data) {
                return ClassRecord::create($data);
            });

            Log::info('Class record stored', ['id' => $record->id]);
            return [
                'valid' => true,
                'msg' => 'Class record created successfully.',
                'record' => $record->load(['student', 'schoolYear', 'teacherSubjectLoad']),
            ];
        } catch (Exception $e) {
            Log::error('Failed to store class record', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function update($id, array $data)
    {
        try {
            Log::info('Updating class record', ['id' => $id, 'data' => $data]);

            $record = ClassRecord::findOrFail($id);
            if (isset($data['student_score']) && $record->total_score !== null && $data['student_score'] > $record->total_score) {
                throw new Exception('Student score cannot exceed total score of ' . $record->total_score);
            }

            $this->validateRecord($data, $id);

            DB::transaction(function () use ($record, $data) {
                $record->update($data);
            });

            Log::info('Class record updated', ['id' => $id]);
            return [
                'valid' => true,
                'msg' => 'Class record updated successfully.',
                'record' => $record->refresh()->load(['student', 'schoolYear', 'teacherSubjectLoad']),
            ];
        } catch (Exception $e) {
            Log::error('Failed to update class record', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function updateScore($id, $score)
    {
        try {
            Log::info('Updating class record score', ['id' => $id, 'score' => $score]);

            $record = ClassRecord::findOrFail($id);
            if ($record->total_score === null) {
                throw new Exception('Total score must be set before updating student score.');
            }
            if ($score > $record->total_score) {
                throw new Exception('Student score cannot exceed total score of ' . $record->total_score);
            }

            DB::transaction(function () use ($record, $score) {
                $record->update(['student_score' => $score]);
            });

            Log::info('Class record score updated', ['id' => $id]);
            return [
                'valid' => true,
                'msg' => 'Score updated successfully.',
                'record' => $record->refresh(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to update class record score', [
                'id' => $id,
                'score' => $score,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function updateTotalScore($recordsName, $quarter, $subjectLoadId, $score)
    {
        try {
            Log::info('Updating total score', [
                'records_name' => $recordsName,
                'quarter' => $quarter,
                'subject_load_id' => $subjectLoadId,
                'score' => $score,
            ]);

            $updated = DB::transaction(function () use ($recordsName, $quarter, $subjectLoadId, $score) {
                return ClassRecord::where('records_name', $recordsName)
                    ->where('quarter', $quarter)
                    ->where('teacher_subject_load_id', $subjectLoadId)
                    ->update(['total_score' => $score]);
            });

            if ($updated === 0) {
                throw new Exception('No records found to update.');
            }

            Log::info('Total score updated', ['records_name' => $recordsName, 'quarter' => $quarter]);
            return [
                'valid' => true,
                'msg' => 'Total score updated successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to update total score', [
                'records_name' => $recordsName,
                'quarter' => $quarter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function generate($subjectLoadId, $quarter)
    {
        try {
            Log::info('Generating class records', ['subject_load_id' => $subjectLoadId, 'quarter' => $quarter]);

            $subjectLoad = TeacherSubjectLoad::findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            $students = Student::whereHas('currentStatus.adviser', function ($query) use ($subjectLoad, $schoolYear) {
                $query->where('grade_level', $subjectLoad->grade_level)
                    ->where('section', $subjectLoad->section)
                    ->where('school_year_id', $schoolYear->id);
            })->get();

            if ($students->isEmpty()) {
                throw new Exception('No students found for this subject load’s section.');
            }

            // Check if records already exist for this quarter
            $existingCount = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                ->where('quarter', $quarter)
                ->count();

            if ($existingCount > 0) {
                return [
                    'valid' => false,
                    'msg' => 'Class records for this quarter and subject load already exist.',
                ];
            }

            // Determine previous quarter
            $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
            $currentIndex = array_search($quarter, $quarters);

            if ($currentIndex === false) {
                throw new Exception('Invalid quarter specified.');
            }

            if ($currentIndex > 0) {
                $previousQuarter = $quarters[$currentIndex - 1];

                // Check if records exist for the previous quarter
                $previousQuarterCount = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                    ->where('quarter', $previousQuarter)
                    ->count();

                if ($previousQuarterCount === 0) {
                    return [
                        'valid' => false,
                        'msg' => "Cannot generate records for {$quarter}. No class records found for {$previousQuarter}.",
                    ];
                }

                // Verify that all students have graded Quarterly Assessment in the previous quarter
                $ungradedCount = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                    ->where('quarter', $previousQuarter)
                    ->where('records_type', 'Quarterly Assessment')
                    ->where('student_score', '<=', 0)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->count();

                if ($ungradedCount > 0) {
                    return [
                        'valid' => false,
                        'msg' => "Cannot generate records for {$quarter}. Some students have ungraded Quarterly Assessments for {$previousQuarter}.",
                    ];
                }
            }

            // Proceed with generating records
            DB::transaction(function () use ($students, $subjectLoadId, $schoolYear, $quarter) {
                foreach ($students as $student) {
                    for ($i = 1; $i <= 10; $i++) {
                        ClassRecord::create([
                            'records_name' => "Written Works $i",
                            'student_id' => $student->id,
                            'teacher_subject_load_id' => $subjectLoadId,
                            'school_year_id' => $schoolYear->id,
                            'student_score' => 0,
                            'records_type' => 'Written Works',
                            'quarter' => $quarter,
                        ]);
                        ClassRecord::create([
                            'records_name' => "Performance Tasks $i",
                            'student_id' => $student->id,
                            'teacher_subject_load_id' => $subjectLoadId,
                            'school_year_id' => $schoolYear->id,
                            'student_score' => 0,
                            'records_type' => 'Performance Tasks',
                            'quarter' => $quarter,
                        ]);
                    }
                    ClassRecord::create([
                        'records_name' => 'Quarterly Assessment',
                        'student_id' => $student->id,
                        'teacher_subject_load_id' => $subjectLoadId,
                        'school_year_id' => $schoolYear->id,
                        'student_score' => 0,
                        'records_type' => 'Quarterly Assessment',
                        'quarter' => $quarter,
                    ]);
                }
            });

            Log::info('Class records generated', ['subject_load_id' => $subjectLoadId, 'quarter' => $quarter]);

            return [
                'valid' => true,
                'msg' => 'Class records generated successfully for ' . count($students) . ' students.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate class records', [
                'subject_load_id' => $subjectLoadId,
                'quarter' => $quarter,
                'entity' => 'ClassRecord',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'valid' => false,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function export($subjectLoadId)
    {
        try {
            Log::info('Exporting class records', ['subject_load_id' => $subjectLoadId]);

            // Fetch subject load details
            $subjectLoad = TeacherSubjectLoad::with(['teacher', 'subject', 'schoolYear'])->findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            // Load template
            $templatePath = public_path('classrecord/ClassRecord.xls');
            if (!file_exists($templatePath)) {
                throw new Exception('Template file not found.');
            }
            $spreadsheet = IOFactory::load($templatePath);

            // Format teacher name
            $teacher = $subjectLoad->teacher;
            $teacherFullName = $teacher->full_name_with_extension;

            // Populate INPUT DATA sheet
            $sheet = $spreadsheet->getSheetByName('INPUT DATA');
            $sheet->setCellValue('G4', strtoupper('REGION-VII'));
            $sheet->setCellValue('O4', strtoupper('LEYTE'));
            $sheet->setCellValue('X5', strtoupper('303414'));
            $sheet->setCellValue('G5', strtoupper('PALALE NATIONAL HIGH SCHOOL'));
            $sheet->setCellValue('K7', strtoupper($subjectLoad->grade_level . '-' . $subjectLoad->section));
            $sheet->setCellValue('S7', strtoupper($teacherFullName));
            $sheet->setCellValue('AG7', strtoupper($subjectLoad->subject->subject_name));
            $sheet->setCellValue('AG5', strtoupper($subjectLoad->schoolYear->school_year));

            // Populate SUMMARY OF QUARTERLY GRADES
            $sheet = $spreadsheet->getSheetByName('SUMMARY OF QUARTERLY GRADES');
            $sheet->setCellValue('F10', '');
            $sheet->setCellValue('J10', '');
            $sheet->setCellValue('N10', '');
            $sheet->setCellValue('R10', '');

            // Initialize arrays for student names and row mapping
            $maleNames = [];
            $femaleNames = [];
            $nameRow = [];

            // Write data to Excel
            $this->writeToExcelRecordsTypeScore($spreadsheet, $subjectLoadId);
            $this->writeToExcelStudentName($spreadsheet, $subjectLoadId, $maleNames, $femaleNames, $nameRow);
            $this->writeToExcelStudentScore($spreadsheet, $subjectLoadId, $nameRow);

            // Create directory correctly under "classrecord" (singular)
            $directoryPath = public_path("classrecord/{$subjectLoadId}");
            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0777, true);
            }

            // Create filename and save path
            $fileName = "({$subjectLoad->schoolYear->school_year})" .
                strtoupper(str_replace(' ', '_', $teacher->last_name)) .
                "-Grade{$subjectLoad->grade_level}-{$subjectLoad->section}-{$subjectLoad->subject->subject_name}.xlsx";

            $filePath = "classrecord/{$subjectLoadId}/{$fileName}";
            $writer = new Xlsx($spreadsheet);
            $writer->save(public_path($filePath)); // <-- Fix: use public_path here

            // Generate download URL
            $downloadUrl = route('classRecords.download', ['subjectLoadId' => $subjectLoadId, 'fileName' => $fileName]);
            $download = '<a href="' . $downloadUrl . '" target="_blank" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>';

            Log::info('Class records exported', ['subject_load_id' => $subjectLoadId, 'file' => $filePath]);
            return [
                'valid' => true,
                'msg' => 'Class records exported successfully.',
                'file_path' => asset("classrecord/{$subjectLoadId}/{$fileName}"),
                'file_name' => $fileName,
                'download' => $download,
            ];
        } catch (Exception $e) {
            Log::error('Failed to export class records', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to export class records: ' . $e->getMessage(),
            ];
        }
    }

    protected function writeToExcelStudentName($spreadsheet, $subjectLoadId, &$maleNames, &$femaleNames, &$nameRow)
    {
        try {
            $subjectLoad = TeacherSubjectLoad::findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            $students = Student::whereHas('currentStatus.adviser', function ($query) use ($subjectLoad, $schoolYear) {
                $query->where('grade_level', $subjectLoad->grade_level)
                    ->where('section', $subjectLoad->section)
                    ->where('school_year_id', $schoolYear->id);
            })->select('id', 'sex', 'first_name', 'middle_name', 'last_name', 'extension_name')->orderBy('last_name', 'asc')->get()->unique('id');

            $maleCell = 12;
            $femaleCell = 63;
            $sheet = $spreadsheet->getSheetByName('INPUT DATA');

            foreach ($students as $student) {
                $studentName = $this->formatFullName($student->first_name, $student->middle_name, $student->last_name, $student->extension_name);
                if ($student->sex === 'Male') {
                    if (!in_array($studentName, $maleNames)) {
                        $cell = 'B' . $maleCell;
                        $sheet->setCellValue($cell, strtoupper($studentName));
                        $maleNames[] = $studentName;
                        $nameRow[$studentName] = $maleCell;
                        $maleCell++;
                    }
                } elseif ($student->sex === 'Female') {
                    if (!in_array($studentName, $femaleNames)) {
                        $cell = 'B' . $femaleCell;
                        $sheet->setCellValue($cell, strtoupper($studentName));
                        $femaleNames[] = $studentName;
                        $nameRow[$studentName] = $femaleCell;
                        $femaleCell++;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error writing student names to Excel', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function writeToExcelRecordsTypeScore($spreadsheet, $subjectLoadId)
    {
        try {
            $records = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                ->join('students', 'class_records.student_id', '=', 'students.id')
                ->select(
                    'total_score',
                    'quarter',
                    'records_type',
                    'records_name',
                    DB::raw("CONCAT(
                        students.last_name,
                        ', ',
                        students.first_name,
                        ' ',
                        COALESCE(students.middle_name, '')
                    ) as student_name")
                )
                ->orderByRaw("FIELD(records_type, 'Written Works', 'Performance Tasks', 'Quarterly Assessment')")
                ->orderBy('student_name', 'asc')
                ->get()
                ->groupBy('quarter');

            $quarterToSheetMap = [
                '1st Quarter' => 'Q1',
                '2nd Quarter' => 'Q2',
                '3rd Quarter' => 'Q3',
                '4th Quarter' => 'Q4',
            ];

            foreach ($quarterToSheetMap as $quarter => $sheetName) {
                if ($records->has($quarter)) {
                    $this->writeToExcelRecordsTypeScoreByQuarter($records->get($quarter), $spreadsheet, $sheetName);
                } else {
                    Log::info("No records found for $quarter.");
                }
            }
        } catch (Exception $e) {
            Log::error('Error writing record type scores to Excel', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function writeToExcelRecordsTypeScoreByQuarter($records, $spreadsheet, $sheetName)
    {
        try {
            $writtenWorksCells = ['F10', 'G10', 'H10', 'I10', 'J10', 'K10', 'L10', 'M10', 'N10', 'O10'];
            $performanceTasksCells = ['S10', 'T10', 'U10', 'V10', 'W10', 'X10', 'Y10', 'Z10', 'AA10', 'AB10'];
            $assessmentCell = 'AF10';

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                throw new Exception("Sheet not found: $sheetName");
            }

            $writtenWorksIndex = 0;
            $performanceTasksIndex = 0;

            foreach ($records as $record) {
                switch ($record->records_type) {
                    case 'Written Works':
                        if (isset($writtenWorksCells[$writtenWorksIndex])) {
                            $sheet->setCellValue($writtenWorksCells[$writtenWorksIndex], $record->total_score);
                            $writtenWorksIndex++;
                        }
                        break;
                    case 'Performance Tasks':
                        if (isset($performanceTasksCells[$performanceTasksIndex])) {
                            $sheet->setCellValue($performanceTasksCells[$performanceTasksIndex], $record->total_score);
                            $performanceTasksIndex++;
                        }
                        break;
                    case 'Quarterly Assessment':
                        $sheet->setCellValue($assessmentCell, $record->total_score);
                        break;
                    default:
                        Log::warning("Unknown records_type: {$record->records_type} in $sheetName");
                }
            }
        } catch (Exception $e) {
            Log::error("Error writing data to $sheetName", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function writeToExcelStudentScore($spreadsheet, $subjectLoadId, $nameRow)
    {
        try {
            $records = ClassRecord::where('teacher_subject_load_id', $subjectLoadId)
                ->join('students', 'class_records.student_id', '=', 'students.id')
                ->select(
                    'class_records.quarter',
                    'class_records.records_type',
                    'class_records.student_score',
                    'class_records.student_id',
                    DB::raw("CONCAT(
                        students.last_name,
                        ', ',
                        students.first_name,
                        ' ',
                        COALESCE(students.middle_name, '')
                    ) as student_name"),
                    'class_records.student_id',
                )
                ->orderByRaw("FIELD(records_type, 'Written Works', 'Performance Tasks', 'Quarterly Assessment')")
                ->orderBy('student_name', 'asc')
                ->get()
                ->groupBy('quarter');

            $quarterKeys = [
                '1st Quarter' => 'Q1',
                '2nd Quarter' => 'Q2',
                '3rd Quarter' => 'Q3',
                '4th Quarter' => 'Q4',
            ];

            foreach ($quarterKeys as $quarterName => $sheetName) {
                if ($records->has($quarterName)) {
                    $this->writeToExcelStudentScoreByQuarter($records->get($quarterName), $spreadsheet, $nameRow);
                } else {
                    Log::info("No records found for $quarterName.");
                }
            }
        } catch (Exception $e) {
            Log::error('Error writing student scores to Excel', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function writeToExcelStudentScoreByQuarter($records, $spreadsheet, $nameRow)
    {
        try {
            $tempName = '';
            $writtenWorksCol = '';
            $performanceTasksCol = '';

            foreach ($records as $record) {
                $sheet = match ($record->quarter) {
                    '1st Quarter' => $spreadsheet->getSheetByName('Q1'),
                    '2nd Quarter' => $spreadsheet->getSheetByName('Q2'),
                    '3rd Quarter' => $spreadsheet->getSheetByName('Q3'),
                    '4th Quarter' => $spreadsheet->getSheetByName('Q4'),
                    default => null,
                };

                if (!$sheet) {
                    Log::warning("Sheet not found for quarter: {$record->quarter}");
                    continue;
                }

                $student = Student::where('id', $record->student_id)
                    ->select('first_name', 'middle_name', 'last_name', 'extension_name')
                    ->first();

                if ($tempName !== $student->full_name_with_extension) {
                    $writtenWorksCol = 'F';
                    $performanceTasksCol = 'S';
                    $tempName = $student->full_name_with_extension;
                }

                if ($record->records_type === 'Written Works') {
                    if ($record->student_score !== 0) {
                        $sheet->setCellValue($writtenWorksCol . $nameRow[$student->full_name_with_extension], $record->student_score);
                        $writtenWorksCol++;
                    }
                } elseif ($record->records_type === 'Performance Tasks') {
                    if ($record->student_score !== 0) {
                        $sheet->setCellValue($performanceTasksCol . $nameRow[$student->full_name_with_extension], $record->student_score);
                        $performanceTasksCol++;
                    }
                } elseif ($record->records_type === 'Quarterly Assessment') {
                    if ($record->student_score !== 0) {
                        $sheet->setCellValue('AF' . $nameRow[$student->full_name_with_extension], $record->student_score);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error writing student scores by quarter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function validateRecord(array $data, $id = null)
    {
        $subjectLoad = TeacherSubjectLoad::findOrFail($data['teacher_subject_load_id']);
        $student = Student::findOrFail($data['student_id']);
        $schoolYear = SchoolYear::findOrFail($data['school_year_id']);

        if (!$this->studentBelongsToSubjectLoadSection($student, $subjectLoad)) {
            throw new Exception('Student is not enrolled in this subject’s section.');
        }

        $query = ClassRecord::where('student_id', $data['student_id'])
            ->where('teacher_subject_load_id', $data['teacher_subject_load_id'])
            ->where('records_name', $data['records_name'])
            ->where('quarter', $data['quarter']);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            throw new Exception('Record already exists for this student, subject, and quarter.');
        }
    }

    protected function studentBelongsToSubjectLoadSection(Student $student, TeacherSubjectLoad $subjectLoad)
    {
        $studentSection = $student->currentStatus;
        return $studentSection &&
            $studentSection->grade_level == $subjectLoad->grade_level &&
            $studentSection->section == $subjectLoad->section;
    }

    protected function formatFullName($firstName, $middleName, $lastName, $extensionName)
    {
        $name = trim("{$firstName} {$middleName} {$lastName}");
        $extension = trim($extensionName);
        $name = $name === '' ? null : $name;
        $extension = $extension === '' ? '' : " {$extension}";
        return $name . $extension;
    }
}
