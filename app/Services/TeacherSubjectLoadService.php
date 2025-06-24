<?php

namespace App\Services;

use App\Models\Adviser;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\TeacherSubjectLoad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request as HttpRequest;

class TeacherSubjectLoadService
{
    public function index(HttpRequest $request)
    {
        try {
            Log::info('Fetching all teacher subject loads for index 123');
            $loads = TeacherSubjectLoad::with(['teacher', 'subject', 'schoolYear'])->where('teacher_id', $request->input('teacher_id'))->where('school_year_id', $request->input('school_year_id'))->get();

            $formattedLoads = $loads->map(function ($load, $key) {
                $actions = '';
                $actions .= '<button type="button" class="btn btn-md btn-primary" title="Update" onclick="editLoad(' . $load->id . ')"><i class="fa fa-edit"></i></button>';
                // $actions .= '<button type="button" class="btn btn-md btn-danger ml-1" title="Delete" onclick="deleteLoad(' . $load->id . ')"><i class="fa fa-trash"></i></button>';

                return [
                    'count' => $key + 1,
                    'teacher_name' => $load->teacher->full_name_with_extension,
                    'subject_name' => $load->subject->subject_name,
                    'school_year' => $load->schoolYear->school_year,
                    'grade_level' => 'Grade ' . $load->grade_level,
                    'section' => $load->section,
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched teacher subject loads', ['count' => count($formattedLoads)]);
            return $formattedLoads;
        } catch (Exception $e) {
            Log::error('Failed to fetch teacher subject loads', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new teacher subject load', ['teacher_id' => $data['teacher_id']]);

            $load = DB::transaction(function () use ($data) {
                return TeacherSubjectLoad::create($data);
            });

            Log::info('Teacher subject load stored successfully', ['load_id' => $load->id]);

            return [
                'valid' => true,
                'msg' => 'Teacher subject load added successfully.',
                'load' => $load->load(['teacher', 'subject', 'schoolYear']),
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'teacher_subject_loads_unique')) {
                Log::error('Duplicate subject assignment detected', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
                return [
                    'valid' => false,
                    'msg' => 'This subject is already assigned to the selected grade level and section for this school year.',
                ];
            }
            Log::error('Failed to store teacher subject load', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add teacher subject load. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to store teacher subject load', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add teacher subject load. Please try again later.',
            ];
        }
    }

    public function update($loadId, array $data)
    {
        try {
            Log::info('Attempting to update teacher subject load', ['load_id' => $loadId]);

            $load = DB::transaction(function () use ($loadId, $data) {
                $load = TeacherSubjectLoad::findOrFail($loadId);
                $load->update($data);
                return $load;
            });

            Log::info('Teacher subject load updated successfully', ['load_id' => $loadId]);

            return [
                'valid' => true,
                'msg' => 'Teacher subject load updated successfully.',
                'load' => $load->load(['teacher', 'subject', 'schoolYear']),
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'teacher_subject_loads_unique')) {
                Log::error('Duplicate subject assignment detected', [
                    'load_id' => $loadId,
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
                return [
                    'valid' => false,
                    'msg' => 'This subject is already assigned to the selected grade level and section for this school year.',
                ];
            }
            Log::error('Failed to update teacher subject load', [
                'load_id' => $loadId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update teacher subject load. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to update teacher subject load', [
                'load_id' => $loadId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update teacher subject load. Please try again later.',
            ];
        }
    }

    public function destroy($loadId)
    {
        try {
            Log::info('Attempting to delete teacher subject load', ['load_id' => $loadId]);

            DB::transaction(function () use ($loadId) {
                $load = TeacherSubjectLoad::findOrFail($loadId);
                $load->delete();
            });

            Log::info('Teacher subject load deleted successfully', ['load_id' => $loadId]);

            return [
                'valid' => true,
                'msg' => 'Teacher subject load deleted successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete teacher subject load', [
                'load_id' => $loadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to delete teacher subject load: ' . $e->getMessage(),
            ];
        }
    }

    public function show($loadId)
    {
        try {
            Log::info('Fetching teacher subject load for edit', ['load_id' => $loadId]);
            $load = TeacherSubjectLoad::with(['teacher', 'subject', 'schoolYear'])->findOrFail($loadId);
            return $load;
        } catch (Exception $e) {
            Log::error('Failed to fetch teacher subject load', [
                'load_id' => $loadId ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function getTeacherList()
    {
        try {
            Log::info('Fetching all teachers for list');
            $teachers = Teacher::with('user')->get();

            $formattedTeachers = $teachers->map(function ($teacher, $key) {
                $actions = '<a href="' . route('admin.viewTeacherSubjectLoad', ['teacherId' => $teacher->id]) . '" type="button" class="btn btn-md btn-primary" title="View Teacher Loads"><i class="fa fa-eye"></i></a>';

                return [
                    'count' => $key + 1,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($teacher->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'teacher_name' => $teacher->full_name_with_extension ?? 'N/A',
                    'contact' => $teacher->contact ?? 'N/A',
                    'email' => $teacher->email ?? 'N/A',
                    'role' => $teacher->user->role ?? 'Teacher',
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched teachers', ['count' => count($formattedTeachers)]);
            return $formattedTeachers;
        } catch (Exception $e) {
            Log::error('Failed to fetch teachers for list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to fetch teacher list: ' . $e->getMessage(),
            ];
        }
    }

    public function getSectionsByGradeLevel($gradeLevel)
    {
        try {
            Log::info('Fetching sections for grade level', ['grade_level' => $gradeLevel]);
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();
            $sections = Adviser::where('grade_level', $gradeLevel)
                ->where('school_year_id', $currentSchoolYear->id)
                ->pluck('section')
                ->unique()
                ->values();

            Log::info('Successfully fetched sections', ['grade_level' => $gradeLevel, 'count' => count($sections)]);
            return $sections;
        } catch (Exception $e) {
            Log::error('Failed to fetch sections for grade level', [
                'grade_level' => $gradeLevel,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}
