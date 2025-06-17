<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubjectController extends Controller
{
    protected $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    public function index()
    {
        return response()->json($this->subjectService->index());
    }

    public function store(StoreSubjectRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->subjectService->store($data));
    }

    public function show(Subject $subject)
    {
        try {
            return response()->json($subject);
        } catch (Exception $e) {
            Log::error('Failed to fetch subject for edit', [
                'subject_id' => $subject->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Subject not found'], 404);
        }
    }

    public function update(UpdateSubjectRequest $request, $subjectId)
    {
        $data = $request->validated();
        return response()->json($this->subjectService->update($subjectId, $data));
    }

    public function destroy($subjectId)
    {
        return response()->json($this->subjectService->destroy($subjectId));
    }
}
