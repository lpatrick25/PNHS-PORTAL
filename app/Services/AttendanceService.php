<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentStatus;
use App\Models\TeacherSubjectLoad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class AttendanceService
{
    public function index($teacherId = null, $schoolYearId = null, $subjectLoadId = null)
    {
        try {
            Log::info('Fetching attendance records', [
                'teacher_id' => $teacherId,
                'school_year_id' => $schoolYearId,
                'subject_load_id' => $subjectLoadId,
            ]);

            $query = Attendance::with(['student', 'schoolYear', 'teacherSubjectLoad.subject', 'teacherSubjectLoad.teacher']);

            if ($teacherId) {
                $query->whereHas('teacherSubjectLoad', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                });
            }

            if ($schoolYearId) {
                $query->where('school_year_id', $schoolYearId);
            }

            if ($subjectLoadId) {
                $query->where('subject_load_id', $subjectLoadId);
            }

            $attendances = $query->get();

            $formattedAttendances = $attendances->map(function ($attendance, $key) {
                $actions = '';
                $actions .= '<button type="button" class="btn btn-md btn-primary" title="Update" onclick="editAttendance(' . $attendance->id . ')"><i class="fa fa-edit"></i></button>';
                $actions .= '<button type="button" class="btn btn-md btn-danger ml-1" title="Delete" onclick="deleteAttendance(' . $attendance->id . ')"><i class="fa fa-trash"></i></button>';

                return [
                    'count' => $key + 1,
                    'student_name' => $attendance->student->full_name ?? 'N/A',
                    'subject_name' => $attendance->teacherSubjectLoad->subject->subject_name ?? 'N/A',
                    'teacher_name' => $attendance->teacherSubjectLoad->teacher->full_name_with_extension ?? 'N/A',
                    'school_year' => $attendance->schoolYear->school_year ?? 'N/A',
                    'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                    'status' => ucfirst($attendance->status),
                    'remarks' => $attendance->remarks ?? 'None',
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched attendance records', ['count' => count($formattedAttendances)]);
            return $formattedAttendances;
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance records', [
                'teacher_id' => $teacherId,
                'school_year_id' => $schoolYearId,
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function bySubjectLoad($subjectLoadId)
    {
        try {
            Log::info('Fetching attendance by subject load', ['subject_load_id' => $subjectLoadId]);

            $attendances = Attendance::where('subject_load_id', $subjectLoadId)
                ->groupBy('attendance_date')
                ->select(
                    'attendance_date',
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as number_of_present'),
                    DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as number_of_late'),
                    DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as number_of_absent')
                )
                ->get();

            $formattedAttendances = $attendances->map(function ($item, $key) {
                $actions = '<button type="button" class="btn btn-md btn-primary" title="View Students" onclick="viewStudents(\'' . $item->attendance_date . '\')"><i class="fa fa-users"></i></button>';
                return [
                    'count' => $key + 1,
                    'attendance_date' => $item->attendance_date->format('Y-m-d'),
                    'number_of_present' => (int)$item->number_of_present,
                    'number_of_late' => (int)$item->number_of_late,
                    'number_of_absent' => (int)$item->number_of_absent,
                    'action' => $actions
                ];
            })->toArray();

            Log::info('Successfully fetched attendance by subject load', ['count' => count($formattedAttendances)]);
            return $formattedAttendances;
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance by subject load', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function byDate($subjectLoadId, $attendanceDate)
    {
        try {
            Log::info('Fetching attendance by date', ['subject_load_id' => $subjectLoadId, 'attendance_date' => $attendanceDate]);

            $subjectLoad = TeacherSubjectLoad::findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            $students = Student::whereHas('currentStatus.adviser', function ($query) use ($subjectLoad, $schoolYear) {
                $query->where('grade_level', $subjectLoad->grade_level)
                    ->where('section', $subjectLoad->section)
                    ->where('school_year_id', $schoolYear->id);
            })->with(['attendances' => function ($query) use ($attendanceDate, $subjectLoadId) {
                $query->where('attendance_date', $attendanceDate)
                    ->where('subject_load_id', $subjectLoadId);
            }])->get();

            $formattedStudents = $students->map(function ($student, $key) {
                $attendance = $student->attendances->first();
                $status = $attendance ? ucfirst($attendance->status) : 'No Record';
                $actions = '<button type="button" class="btn btn-md btn-primary" title="Update" onclick="updateStatus(' . $attendance->id . ', \'' . addslashes($student->full_name) . '\')"><i class="fa fa-edit"></i></button>';
                return [
                    'id' => $student->id,
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="Student Image" src="' . ($student->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'student_lrn' => $student->student_lrn ?? 'N/A',
                    'student_name' => $student->full_name ?? 'N/A',
                    'attendance_status' => $status,
                    'action' => $actions
                ];
            });

            Log::info('Successfully fetched attendance by date', ['count' => count($formattedStudents)]);
            return $formattedStudents;
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance by date', [
                'subject_load_id' => $subjectLoadId,
                'attendance_date' => $attendanceDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function generate($subjectLoadId, $attendanceDate)
    {
        Log::info('Generating attendance', ['subject_load_id' => $subjectLoadId, 'attendance_date' => $attendanceDate]);
        try {
            Log::info('Generating attendance', ['subject_load_id' => $subjectLoadId, 'attendance_date' => $attendanceDate]);

            $subjectLoad = TeacherSubjectLoad::findOrFail($subjectLoadId);
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            $students = StudentStatus::whereHas('adviser', function ($query) use ($subjectLoad, $schoolYear) {
                $query->where('grade_level', $subjectLoad->grade_level)
                    ->where('section', $subjectLoad->section)
                    ->where('school_year_id', $schoolYear->id);
            })->get();

            if ($students->isEmpty()) {
                throw new Exception('No students found for this subject load’s section.');
            }

            $existingCount = Attendance::where('subject_load_id', $subjectLoadId)
                ->where('attendance_date', date('Y-m-d', strtotime($attendanceDate)))
                ->count();

            if ($existingCount > 0) {
                return [
                    'valid' => false,
                    'msg' => 'Attendance for this date and subject load already exists.'
                ];
            }

            DB::transaction(function () use ($students, $subjectLoadId, $attendanceDate, $schoolYear) {
                foreach ($students as $student) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'school_year_id' => $schoolYear->id,
                        'attendance_date' => $attendanceDate,
                        'status' => 'absent', // Default to absent
                        'subject_load_id' => $subjectLoadId,
                    ]);
                }
            });

            Log::info('Attendance generated successfully', ['subject_load_id' => $subjectLoadId, 'attendance_date' => $attendanceDate]);
            return [
                'valid' => true,
                'msg' => 'Attendance generated successfully for ' . count($students) . ' students.'
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate attendance', [
                'subject_load_id' => $subjectLoadId,
                'attendance_date' => $attendanceDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new attendance record', ['student_id' => $data['student_id']]);

            $this->validateAttendance($data);

            $attendance = DB::transaction(function () use ($data) {
                return Attendance::create($data);
            });

            Log::info('Attendance record stored successfully', ['attendance_id' => $attendance->id]);

            return [
                'valid' => true,
                'msg' => 'Attendance recorded successfully.',
                'attendance' => $attendance->load(['student', 'schoolYear', 'teacherSubjectLoad']),
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'attendances_unique')) {
                Log::error('Duplicate attendance record detected', ['data' => $data]);
                return [
                    'valid' => false,
                    'msg' => 'Attendance for this student on this date and subject has already been recorded.',
                ];
            }
            Log::error('Failed to store attendance record', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to record attendance. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to store attendance record', [
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

    public function update($attendanceId, array $data)
    {
        try {
            Log::info('Attempting to update attendance record', ['attendance_id' => $attendanceId]);

            $this->validateAttendance($data, $attendanceId);

            $attendance = DB::transaction(function () use ($attendanceId, $data) {
                $attendance = Attendance::findOrFail($attendanceId);
                $attendance->update($data);
                return $attendance;
            });

            Log::info('Attendance record updated successfully', ['attendance_id' => $attendanceId]);

            return [
                'valid' => true,
                'msg' => 'Attendance updated successfully.',
                'attendance' => $attendance->load(['student', 'schoolYear', 'teacherSubjectLoad']),
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'attendances_unique')) {
                Log::error('Duplicate attendance record detected', ['data' => $data]);
                return [
                    'valid' => false,
                    'msg' => 'Attendance for this student on this date and subject has already been recorded.',
                ];
            }
            Log::error('Failed to update attendance record', [
                'attendance_id' => $attendanceId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update attendance. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to update attendance record', [
                'attendance_id' => $attendanceId,
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

    public function destroy($attendanceId)
    {
        try {
            Log::info('Attempting to delete attendance record', ['attendance_id' => $attendanceId]);

            DB::transaction(function () use ($attendanceId) {
                $attendance = Attendance::findOrFail($attendanceId);
                $attendance->delete();
            });

            Log::info('Attendance record deleted successfully', ['attendance_id' => $attendanceId]);

            return [
                'valid' => true,
                'msg' => 'Attendance record deleted successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete attendance record', [
                'attendance_id' => $attendanceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to delete attendance record: ' . $e->getMessage(),
            ];
        }
    }

    public function processRfidAttendance(array $data)
    {
        try {
            Log::info('Processing RFID attendance', ['rfid_no' => $data['rfid_no']]);

            // Map RFID tag to student
            $student = Student::where('rfid_no', $data['rfid_no'])->first();
            if (!$student) {
                throw new Exception('Student not found for RFID tag.');
            }

            // Get current school year
            $schoolYear = SchoolYear::where('current', true)->firstOrFail();

            // Validate subject load
            $subjectLoad = TeacherSubjectLoad::findOrFail($data['subject_load_id']);
            if (!$this->studentBelongsToSubjectLoadSection($student, $subjectLoad)) {
                throw new Exception('Student is not enrolled in this subject’s section.');
            }

            // Determine status
            $scanTime = Carbon::now();
            $status = $this->determineAttendanceStatus($scanTime, $subjectLoad);

            $attendanceData = [
                'student_id' => $student->id,
                'school_year_id' => $schoolYear->id,
                'attendance_date' => $data['attendance_date'],
                'status' => $status,
                'remarks' => $data['remarks'] ?? null,
                'subject_load_id' => $subjectLoad->id,
            ];

            // Check if attendance exists
            $existingAttendance = Attendance::where('student_id', $student->id)
                ->where('attendance_date', $data['attendance_date'])
                ->where('subject_load_id', $subjectLoad->id)
                ->first();

            if ($existingAttendance) {
                // Update existing attendance
                $response = $this->update($existingAttendance->id, $attendanceData);
            } else {
                // Create new attendance
                $response = $this->store($attendanceData);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Failed to process RFID attendance', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to process RFID attendance: ' . $e->getMessage(),
            ];
        }
    }

    protected function validateAttendance(array $data, $attendanceId = null)
    {
        // Ensure student is in the subject load’s section
        $subjectLoad = TeacherSubjectLoad::findOrFail($data['subject_load_id']);
        $student = Student::findOrFail($data['student_id']);
        if (!$this->studentBelongsToSubjectLoadSection($student, $subjectLoad)) {
            throw new Exception('Student is not enrolled in this subject’s section.');
        }

        // Check for duplicate attendance (student, date, subject load)
        $query = Attendance::where('student_id', $data['student_id'])
            ->where('attendance_date', $data['attendance_date'])
            ->where('subject_load_id', $data['subject_load_id']);

        if ($attendanceId) {
            $query->where('id', '!=', $attendanceId);
        }

        if ($query->exists()) {
            throw new Exception('Attendance for this student on this date and subject has already been recorded.');
        }
    }

    protected function studentBelongsToSubjectLoadSection(Student $student, TeacherSubjectLoad $subjectLoad)
    {
        $studentSection = $student->currentStatus;
        Log::info('Checking if student belongs to subject load section', [
            'student_id' => $student->id,
            'subject_load_id' => $subjectLoad->id,
            'student_section' => $studentSection ? $studentSection->section : 'N/A',
            'subject_load_section' => $subjectLoad->section,
        ]);
        return $studentSection &&
            $studentSection->grade_level == $subjectLoad->grade_level &&
            $studentSection->section == $subjectLoad->section;
    }

    protected function determineAttendanceStatus(Carbon $scanTime, TeacherSubjectLoad $subjectLoad)
    {
        $classStartTime = Carbon::parse($subjectLoad->schedule_start_time ?? '08:00:00');
        $lateThreshold = $classStartTime->copy()->addMinutes(15);

        if ($scanTime->greaterThan($lateThreshold)) {
            return 'present';
        }

        return 'present';
    }
}
