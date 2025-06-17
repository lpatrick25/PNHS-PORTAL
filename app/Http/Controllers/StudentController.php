<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index()
    {
        return response()->json($this->studentService->index());
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $response = $this->studentService->store($data);

        return response()->json($response);
    }

    public function update(UpdateStudentRequest $request, $studentLRN)
    {
        $data = $request->except('_token');
        $response = $this->studentService->update($studentLRN, $data);
        return response()->json($response);
    }

    public function updateAvatar(Request $request, $studentLRN)
    {
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $response = $this->studentService->updateAvatar($studentLRN, $file);
            return response()->json($response);
        }
        return response()->json(['valid' => false, 'msg' => 'No file uploaded.']);
    }
}
