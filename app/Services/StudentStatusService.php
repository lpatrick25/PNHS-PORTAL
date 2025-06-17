<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentStatusService
{
    public function getAdviserStudents($adviserId, $schoolYearId)
    {
        try {
            Log::info('Fetching students for adviser', ['adviser_id' => $adviserId, 'school_year_id' => $schoolYearId]);
            $students = StudentStatus::with(['student', 'adviser', 'schoolYear'])
                ->where('adviser_id', $adviserId)
                ->where('school_year_id', $schoolYearId)
                ->get();

            $formattedStudents = $students->map(function ($studentStatus, $key) {
                $actions = '';
                $actions .= '<button class="btn btn-md btn-danger" onclick="removeStudentFromAdviser(' . $studentStatus->id . ')" title="Remove"><i class="fa fa-trash"></i></button>';
                // $actions .= '<button class="btn btn-md btn-warning" onclick="dropStudentFromAdviser(' . $studentStatus->id . ')" title="Drop"><i class="fa fa-trash"></i></button>';

                return [
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($studentStatus->student->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'student_lrn' => $studentStatus->student->student_lrn,
                    'student_name' => $studentStatus->student->full_name_with_extension,
                    'status' => $studentStatus->status,
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched students for adviser', ['count' => count($formattedStudents)]);
            return $formattedStudents;
        } catch (Exception $e) {
            Log::error('Failed to fetch students for adviser', [
                'adviser_id' => $adviserId,
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new student status', ['student_id' => $data['student_id']]);

            $studentStatus = DB::transaction(function () use ($data) {
                return StudentStatus::create($data);
            });

            Log::info('Student status stored successfully', ['student_status_id' => $studentStatus->id]);

            return [
                'valid' => true,
                'msg' => 'Student assigned to adviser successfully.',
                'student_status' => $studentStatus->load(['student', 'adviser', 'schoolYear']),
            ];
        } catch (Exception $e) {
            Log::error('Failed to store student status', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to assign student to adviser. Please try again later.',
            ];
        }
    }

    public function destroy($studentStatusId)
    {
        try {
            Log::info('Attempting to delete student status', ['student_status_id' => $studentStatusId]);

            DB::transaction(function () use ($studentStatusId) {
                $studentStatus = StudentStatus::findOrFail($studentStatusId);
                $studentStatus->delete();
            });

            Log::info('Student status deleted successfully', ['student_status_id' => $studentStatusId]);

            return [
                'valid' => true,
                'msg' => 'Student removed from adviser successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete student status', [
                'student_status_id' => $studentStatusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to remove student from adviser: ' . $e->getMessage(),
            ];
        }
    }

    public function getNotEnrolled($schoolYearId)
    {
        try {
            Log::info('Fetching students not enrolled for school year', ['school_year_id' => $schoolYearId]);
            $students = Student::whereDoesntHave('studentStatuses', function ($query) use ($schoolYearId) {
                $query->where('school_year_id', $schoolYearId);
            })->select('id', 'first_name', 'middle_name', 'last_name', 'extension_name')->get();

            $students = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'full_name_with_extension' => $student->full_name_with_extension,
                ];
            });

            Log::info('Successfully fetched not enrolled students', ['count' => count($students)]);
            return $students;
        } catch (Exception $e) {
            Log::error('Failed to fetch not enrolled students', [
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}
