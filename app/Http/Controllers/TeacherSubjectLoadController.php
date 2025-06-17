<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherSubjectLoadRequest;
use App\Http\Requests\UpdateTeacherSubjectLoadRequest;
use App\Models\TeacherSubjectLoad;
use App\Services\TeacherSubjectLoadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherSubjectLoadController extends Controller
{
    protected $teacherSubjectLoadService;

    public function __construct(TeacherSubjectLoadService $teacherSubjectLoadService)
    {
        $this->teacherSubjectLoadService = $teacherSubjectLoadService;
    }

    public function index(Request $request)
    {
        return response()->json($this->teacherSubjectLoadService->index($request));
    }

    public function store(StoreTeacherSubjectLoadRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->teacherSubjectLoadService->store($data));
    }

    public function update(UpdateTeacherSubjectLoadRequest $request, $loadId)
    {
        $data = $request->validated();
        return response()->json($this->teacherSubjectLoadService->update($loadId, $data));
    }

    public function destroy($loadId)
    {
        return response()->json($this->teacherSubjectLoadService->destroy($loadId));
    }

    public function show($loadId)
    {
        try {
            Log::info('Fetching teacher subject load for edit', [
                'load_id' => $loadId,
            ]);
            return response()->json($this->teacherSubjectLoadService->show($loadId));
        } catch (Exception $e) {
            Log::error('Failed to fetch teacher subject load', [
                'load_id' => $loadId ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Teacher subject load not found'], 404);
        }
    }
}
