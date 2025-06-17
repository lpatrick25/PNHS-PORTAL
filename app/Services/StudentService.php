<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentService
{
    public function index()
    {
        try {
            Log::info('Fetching all students for index');
            $students = Student::all();

            $formattedStudents = $students->map(function ($student, $key) {
                return [
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($student->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'rfid_no' => $student->rfid_no,
                    'student_lrn' => $student->student_lrn,
                    'student_name' => $student->full_name_with_extension,
                    'contact' => $student->contact,
                    'email' => $student->email,
                    'action' => '<a href="' . route('admin.updateStudent', ['studentLRN' => $student->student_lrn]) . '" type="button" class="btn btn-md btn-primary" title="Update"><i class="fa fa-edit"></i></a>',
                ];
            })->toArray();

            Log::info('Successfully fetched students', ['count' => count($formattedStudents)]);
            return $formattedStudents;
        } catch (Exception $e) {
            Log::error('Failed to fetch students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new student', ['student_lrn' => $data['student_lrn']]);

            $student = DB::transaction(function () use ($data) {
                $user = User::create([
                    'username' => $data['student_lrn'],
                    'password' => Hash::make($data['student_lrn']),
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ]);

                return $user->student()->create($data);
            });

            Log::info('Student stored successfully', ['student_lrn' => $student->student_lrn, 'user_id' => $student->user_id]);

            return [
                'valid' => true,
                'msg' => 'Student added successfully.',
                'student' => $student,
            ];
        } catch (Exception $e) {
            Log::error('Failed to store student', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add student. Please try again later.',
            ];
        }
    }

    public function update($studentLRN, array $data)
    {
        try {
            Log::info('Attempting to update student', ['student_lrn' => $studentLRN]);

            $student = DB::transaction(function () use ($studentLRN, $data) {
                $student = Student::where('student_lrn', $studentLRN)->firstOrFail();
                $student->update($data);
                return $student;
            });

            Log::info('Student updated successfully', ['student_lrn' => $studentLRN]);

            return [
                'valid' => true,
                'msg' => 'Student updated successfully.',
                'student' => $student,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update student', [
                'student_lrn' => $studentLRN,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update student. Please try again later.',
            ];
        }
    }

    public function updateAvatar($studentLRN, $file)
    {
        try {
            Log::info('Attempting to update student avatar', ['student_lrn' => $studentLRN]);

            $imageUrl = DB::transaction(function () use ($studentLRN, $file) {
                $student = Student::where('student_lrn', $studentLRN)->firstOrFail();
                $student->addMedia($file)->toMediaCollection('avatar');
                return $student->getFirstMediaUrl('avatar', 'thumb');
            });

            Log::info('Student avatar updated successfully', ['student_lrn' => $studentLRN, 'image_url' => $imageUrl]);

            return [
                'valid' => true,
                'msg' => 'Avatar updated successfully.',
                'image' => $imageUrl,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update student avatar', [
                'student_lrn' => $studentLRN,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update avatar. Please try again later.',
            ];
        }
    }
}
