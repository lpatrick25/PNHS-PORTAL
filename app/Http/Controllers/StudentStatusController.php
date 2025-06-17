<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentStatusRequest;
use App\Models\Adviser;
use App\Services\StudentStatusService;
use Illuminate\Http\Request;

class StudentStatusController extends Controller
{
    protected $studentStatusService;

    public function __construct(StudentStatusService $studentStatusService)
    {
        $this->studentStatusService = $studentStatusService;
    }

    public function store(StoreStudentStatusRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->studentStatusService->store($data));
    }

    public function destroy($studentStatusId)
    {
        return response()->json($this->studentStatusService->destroy($studentStatusId));
    }

    public function getAdviserStudents($adviserId, $schoolYearId)
    {
        $students = $this->studentStatusService->getAdviserStudents($adviserId, $schoolYearId);
        return response()->json($students);
    }

    public function getNotEnrolled($schoolYearId)
    {
        $students = $this->studentStatusService->getNotEnrolled($schoolYearId);
        return response()->json($students);
    }
}
