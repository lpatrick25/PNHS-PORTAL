<?php

namespace App\Services;

use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SchoolYearService
{
    public function index()
    {
        try {
            Log::info('Fetching all school years for index');
            $schoolYears = SchoolYear::all();

            $formattedSchoolYears = $schoolYears->map(function ($schoolYear, $key) {
                $actions = '';
                $actions .= '<button type="button" class="btn btn-md btn-primary" title="Update" onclick="view(' . $schoolYear->id . ')"><i class="fa fa-edit"></i></button>';
                $actions .= '<button type="button" class="btn btn-md btn-success ml-1" title="Set Current School Year" onclick="setCurrent(' . $schoolYear->id . ')"><i class="fa fa-check"></i></button>';

                return [
                    'count' => $key + 1,
                    'id' => $schoolYear->id,
                    'school_year' => $schoolYear->school_year,
                    'start_date' => $schoolYear->start_date->format('Y-m-d'),
                    'end_date' => $schoolYear->end_date->format('Y-m-d'),
                    'current' => $schoolYear->current ? 'Yes' : 'No',
                    'action' => $actions,
                ];
            })->toArray();

            Log::info('Successfully fetched school years', ['count' => count($formattedSchoolYears)]);
            return $formattedSchoolYears;
        } catch (Exception $e) {
            Log::error('Failed to fetch school years', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function store(array $data)
    {
        try {
            Log::info('Attempting to store new school year', ['school_year' => $data['school_year']]);

            $schoolYear = DB::transaction(function () use ($data) {
                if ($data['current']) {
                    SchoolYear::where('current', true)->update(['current' => false]);
                }
                return SchoolYear::create($data);
            });

            Log::info('School year stored successfully', ['school_year_id' => $schoolYear->id]);

            return [
                'valid' => true,
                'msg' => 'School year added successfully.',
                'school_year' => $schoolYear,
            ];
        } catch (Exception $e) {
            Log::error('Failed to store school year', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to add school year. Please try again later.',
            ];
        }
    }

    public function update($schoolYearId, array $data)
    {
        try {
            Log::info('Attempting to update school year', ['school_year_id' => $schoolYearId]);

            $schoolYear = DB::transaction(function () use ($schoolYearId, $data) {
                if ($data['current']) {
                    SchoolYear::where('current', true)->update(['current' => false]);
                }
                $schoolYear = SchoolYear::findOrFail($schoolYearId);
                $schoolYear->update($data);
                return $schoolYear;
            });

            Log::info('School year updated successfully', ['school_year_id' => $schoolYearId]);

            return [
                'valid' => true,
                'msg' => 'School year updated successfully.',
                'school_year' => $schoolYear,
            ];
        } catch (Exception $e) {
            Log::error('Failed to update school year', [
                'school_year_id' => $schoolYearId,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to update school year. Please try again later.',
            ];
        }
    }

    public function destroy($schoolYearId)
    {
        try {
            Log::info('Attempting to delete school year', ['school_year_id' => $schoolYearId]);

            DB::transaction(function () use ($schoolYearId) {
                $schoolYear = SchoolYear::findOrFail($schoolYearId);
                if ($schoolYear->current) {
                    throw new Exception('Cannot delete the current school year.');
                }
                $schoolYear->delete();
            });

            Log::info('School year deleted successfully', ['school_year_id' => $schoolYearId]);

            return [
                'valid' => true,
                'msg' => 'School year deleted successfully.',
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete school year', [
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to delete school year: ' . $e->getMessage(),
            ];
        }
    }

    public function setCurrent($schoolYearId)
    {
        try {
            Log::info('Setting current school year', ['school_year_id' => $schoolYearId]);

            $schoolYear = DB::transaction(function () use ($schoolYearId) {
                // Reset current status for all school years
                SchoolYear::where('current', true)->update(['current' => false]);
                // Set the selected school year as current
                $schoolYear = SchoolYear::findOrFail($schoolYearId);
                $schoolYear->update(['current' => true]);
                return $schoolYear;
            });

            Log::info('Successfully set current school year', ['school_year_id' => $schoolYearId]);

            return [
                'valid' => true,
                'msg' => 'School year set as current successfully.',
                'school_year' => $schoolYear,
            ];
        } catch (Exception $e) {
            Log::error('Failed to set current school year', [
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'msg' => 'Failed to set current school year. Please try again later.',
            ];
        }
    }
}
