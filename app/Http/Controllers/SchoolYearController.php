<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use App\Services\SchoolYearService;
use Illuminate\Http\Request;

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

    public function destroy($schoolYearId)
    {
        return response()->json($this->schoolYearService->destroy($schoolYearId));
    }
}
