<?php

namespace App\Services;

use App\Models\Adviser;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class TeacherService
{
    public function index()
    {
        try {
            Log::info('Fetching all teachers for index');
            $teachers = Teacher::with('user')->get();

            $formattedTeachers = $teachers->map(function ($teacher, $key) {
                $isAdviser = Adviser::where('teacher_id', $teacher->id)->exists();
                $role = $isAdviser ? 'Adviser' : ucfirst($teacher->user->role);
                $actions = '';
                $actions .= '<a href="' . route('admin.updateTeacher', ['teacherId' => $teacher->id]) . '" type="button" class="btn btn-md btn-primary" title="Update"><i class="fa fa-edit"></i></a>';
                if ($isAdviser) {
                    $actions .= '<button type="button" class="btn btn-md btn-warning" title="Update Adviser" onclick="setAdviser(' . $teacher->id . ', true)"><i class="fa fa-user-edit"></i></button>';
                } else {
                    $actions .= '<button type="button" class="btn btn-md btn-success" title="Set Adviser" onclick="setAdviser(' . $teacher->id . ', false)"><i class="fa fa-user-plus"></i></button>';
                }

                return [
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($teacher->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'teacher_name' => $teacher->full_name_with_extension,
                    'contact' => $teacher->contact,
                    'email' => $teacher->email,
                    'role' => $role,
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched teachers', ['count' => count($formattedTeachers)]);
            return $formattedTeachers;
        } catch (Exception $e) {
            Log::error('Failed to fetch teachers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new teacher', ['email' => $data['email']]);

            $teacher = DB::transaction(function () use ($data) {
                $user = User::create([
                    'username' => $data['email'],
                    'password' => Hash::make($data['email']),
                    'role' => User::ROLE_TEACHER,
                    'is_active' => true,
                ]);

                return $user->teacher()->create($data);
            });

            Log::info('Teacher stored successfully', ['teacher_id' => $teacher->id, 'user_id' => $teacher->user_id]);

            return [
                'valid' => true,
                'msg' => 'Teacher added successfully.',
                'teacher' => $teacher,
            ];
        } catch (Exception $e) {
            Log::error('Failed to store teacher', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add teacher. Please try again later.',
            ];
        }
    }

    public function update($teacherId, array $data)
    {
        try {
            Log::info('Attempting to update teacher', ['teacher_id' => $teacherId]);

            $teacher = DB::transaction(function () use ($teacherId, $data) {
                $teacher = Teacher::findOrFail($teacherId);
                $teacher->update($data);
                return $teacher;
            });

            Log::info('Teacher updated successfully', ['teacher_id' => $teacherId]);

            return [
                'valid' => true,
                'msg' => 'Teacher updated successfully.',
                'teacher' => $teacher,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update teacher', [
                'teacher_id' => $teacherId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update teacher. Please try again later.',
            ];
        }
    }

    public function updateAvatar($teacherId, $file)
    {
        try {
            Log::info('Attempting to update teacher avatar', ['teacher_id' => $teacherId]);

            $imageUrl = DB::transaction(function () use ($teacherId, $file) {
                $teacher = Teacher::findOrFail($teacherId);
                $teacher->addMedia($file)->toMediaCollection('avatar');
                return $teacher->getFirstMediaUrl('avatar', 'thumb');
            });

            Log::info('Teacher avatar updated successfully', ['teacher_id' => $teacherId, 'image_url' => $imageUrl]);

            return [
                'valid' => true,
                'msg' => 'Avatar updated successfully.',
                'image' => $imageUrl,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update teacher avatar', [
                'teacher_id' => $teacherId,
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
