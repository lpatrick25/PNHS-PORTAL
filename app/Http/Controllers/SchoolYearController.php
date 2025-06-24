<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use App\Models\SchoolYear;
use App\Services\SchoolYearService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SchoolYearController extends Controller
{
    protected $schoolYearService;

    public function __construct(SchoolYearService $schoolYearService)
    {
        $this->schoolYearService = $schoolYearService;
    }

    public function index()
    {
        return response()->json($this->schoolYearService->index());
    }

    public function store(StoreSchoolYearRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->schoolYearService->store($data));
    }

    public function update(UpdateSchoolYearRequest $request, $schoolYearId)
    {
        $data = $request->validated();
        return response()->json($this->schoolYearService->update($schoolYearId, $data));
    }

    public function show($schoolYearId)
    {
        try {
            $schoolYear = SchoolYear::findOrFail($schoolYearId);
            return response()->json($schoolYear);
        } catch (Exception $e) {
            Log::error('Failed to fetch school year', [
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'School year not found'], 404);
        }
    }

    public function destroy($schoolId)
    {
        return response()->json($this->schoolYearService->destroy($schoolId));
    }

    public function setCurrentSchoolYear($schoolYearId)
    {
        try {
            Log::info('Attempting to set current school year', ['school_year_id' => $schoolYearId]);

            $result = $this->schoolYearService->setCurrent($schoolYearId);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Failed to set current school year', [
                'school_year_id' => $schoolYearId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'valid' => false,
                'msg' => 'Failed to set current school year.',
            ], 500);
        }
    }
}
