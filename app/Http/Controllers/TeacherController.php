<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function index()
    {
        return response()->json($this->teacherService->index());
    }

    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();
        $response = $this->teacherService->store($data);
        return response()->json($response);
    }

    public function update(UpdateTeacherRequest $request, $teacherId)
    {
        $data = $request->except('_token');
        $response = $this->teacherService->update($teacherId, $data);
        return response()->json($response);
    }

    public function updateAvatar(Request $request, $teacherId)
    {
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $response = $this->teacherService->updateAvatar($teacherId, $file);
            return response()->json($response);
        }
        return response()->json(['valid' => false, 'msg' => 'No file uploaded.']);
    }
}
