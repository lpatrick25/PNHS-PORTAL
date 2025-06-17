<?php

namespace App\Services;

use App\Models\Adviser;
use App\Models\SchoolYear;
use App\Models\StudentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AdviserService
{
    public function index()
    {
        try {
            Log::info('Fetching all advisers for index');

            // Get the current school year
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();

            // Fetch advisers with their teacher and school year relationships
            $advisers = Adviser::with(['teacher', 'schoolYear'])->get();

            // Prepare formatted advisers with student count
            $formattedAdvisers = $advisers->map(function ($adviser, $key) use ($currentSchoolYear) {
                // Count enrolled students for this adviser in the current school year
                $studentCount = StudentStatus::where('adviser_id', $adviser->id)
                    ->where('school_year_id', $currentSchoolYear->id)
                    ->where('status', 'ENROLLED')
                    ->count();

                $actions = '';
                $actions .= '<a href="' . route('admin.addAdviserStudent', ['adviser' => $adviser->id]) . '" type="button" class="btn btn-md btn-primary" title="Add Student"><i class="fa fa-user-plus"></i></a>';

                return [
                    'count' => $key + 1,
                    'adviser_id' => $adviser->id,
                    'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($adviser->teacher->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                    'adviser_name' => $adviser->teacher->full_name_with_extension,
                    'grade_level' => 'Grade ' . $adviser->grade_level,
                    'section' => $adviser->section,
                    'school_year' => $adviser->schoolYear->school_year,
                    'student_count' => '<span class="badge badge-primary">' . $studentCount . ' Student' . ($studentCount !== 1 ? 's' : '') . '</span>',
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched advisers', ['count' => count($formattedAdvisers)]);
            return $formattedAdvisers;
        } catch (Exception $e) {
            Log::error('Failed to fetch advisers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new adviser', ['section' => $data['section']]);

            $adviser = DB::transaction(function () use ($data) {
                $schoolYear = DB::table('school_years')->where('current', true)->first();
                return Adviser::create(array_merge($data, ['school_year_id' => $schoolYear->id]));
            });

            Log::info('Adviser stored successfully', ['adviser_id' => $adviser->id]);

            return [
                'valid' => true,
                'msg' => 'Adviser added successfully.',
                'adviser' => $adviser->load(['teacher', 'schoolYear']),
            ];
        } catch (Exception $e) {
            Log::error('Failed to store adviser', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add adviser. Please try again later.',
            ];
        }
    }

    public function update($adviserId, array $data)
    {
        try {
            Log::info('Attempting to update adviser', ['adviser_id' => $adviserId]);

            $adviser = DB::transaction(function () use ($adviserId, $data) {
                $adviser = Adviser::findOrFail($adviserId);
                $adviser->update($data);
                return $adviser;
            });

            Log::info('Adviser updated successfully', ['adviser_id' => $adviserId]);

            return [
                'valid' => true,
                'msg' => 'Adviser updated successfully.',
                'adviser' => $adviser->load(['teacher', 'schoolYear']),
            ];
        } catch (Exception $e) {
            Log::error('Failed to update adviser', [
                'adviser_id' => $adviserId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update adviser. Please try again later.',
            ];
        }
    }

    public function destroy($adviserId)
    {
        try {
            Log::info('Attempting to delete adviser', ['adviser_id' => $adviserId]);

            DB::transaction(function () use ($adviserId) {
                $adviser = Adviser::findOrFail($adviserId);
                $adviser->delete();
            });

            Log::info('Adviser deleted successfully', ['adviser_id' => $adviserId]);

            return [
                'valid' => true,
                'msg' => 'Adviser deleted successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete adviser', [
                'adviser_id' => $adviserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to delete adviser. Please try again later.',
            ];
        }
    }
}
