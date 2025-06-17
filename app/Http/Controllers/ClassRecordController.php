<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRecordRequest;
use App\Http\Requests\UpdateClassRecordRequest;
use App\Services\ClassRecordService;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ClassRecordController extends Controller
{
    protected $classRecordService;

    public function __construct(ClassRecordService $classRecordService)
    {
        $this->classRecordService = $classRecordService;
    }

    public function viewClassRecordTeacher()
    {
        try {
            $schoolYears = SchoolYear::all();
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();
            $teacher_id = auth()->user()->teacher->id;
            return view('teacher.class-records.index', compact('schoolYears', 'currentSchoolYear', 'teacher_id'));
        } catch (Exception $e) {
            Log::error('Failed to load class records view', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load class records page');
        }
    }

    public function viewClassRecord($subjectLoadId)
    {
        try {
            $subjectLoad = \App\Models\TeacherSubjectLoad::with(['subject', 'teacher', 'schoolYear'])
                ->findOrFail($subjectLoadId);
            return view('teacher.view-class-records', compact('subjectLoad'));
        } catch (Exception $e) {
            Log::error('Failed to load class record details', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load class record details');
        }
    }

    public function index(Request $request)
    {
        try {
            $teacherId = $request->query('teacher_id');
            $schoolYearId = $request->query('school_year_id');
            $subjectLoadId = $request->query('subject_load_id');
            return response()->json($this->classRecordService->index($teacherId, $schoolYearId, $subjectLoadId));
        } catch (Exception $e) {
            Log::error('Failed to fetch class records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch class records'], 500);
        }
    }

    public function bySubjectLoad(Request $request)
    {
        try {
            $subjectLoadId = $request->query('subject_load_id');
            $quarter = $request->query('quarter', '1st Quarter');
            return response()->json($this->classRecordService->bySubjectLoad($subjectLoadId, $quarter));
        } catch (Exception $e) {
            Log::error('Failed to fetch class records by subject load', [
                'subject_load_id' => $subjectLoadId,
                'quarter' => $request->quarter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch class records'], 500);
        }
    }

    public function store(StoreClassRecordRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->classRecordService->store($data);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to store class record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to create class record'], 500);
        }
    }

    public function update(UpdateClassRecordRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $response = $this->classRecordService->update($id, $data);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to update class record', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to update class record'], 500);
        }
    }

    public function updateScore(Request $request)
    {
        try {
            $request->validate([
                'pk' => ['required', 'exists:class_records,id'],
                'value' => ['required', 'integer', 'min:0', 'max:100'],
            ]);

            $response = $this->classRecordService->updateScore($request->pk, $request->value);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to update score', [
                'pk' => $request->pk,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to update score'], 500);
        }
    }

    public function updateTotalScore(Request $request)
    {
        try {
            $request->validate([
                'records_name' => ['required', 'string', 'max:50'],
                'quarter' => ['required', 'in:1st Quarter,2nd Quarter,3rd Quarter,4th Quarter'],
                'subject_load_id' => ['required', 'exists:teacher_subject_loads,id'],
                'value' => ['required', 'integer', 'min:0', 'max:100'],
            ]);

            $response = $this->classRecordService->updateTotalScore(
                $request->records_name,
                $request->quarter,
                $request->subject_load_id,
                $request->value
            );
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to update total score', [
                'records_name' => $request->records_name,
                'quarter' => $request->quarter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to update total score'], 500);
        }
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'subject_load_id' => ['required', 'exists:teacher_subject_loads,id'],
                'quarter' => ['required', 'in:1st Quarter,2nd Quarter,3rd Quarter,4th Quarter'],
            ]);

            $response = $this->classRecordService->generate($request->subject_load_id, $request->quarter);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to generate class records', [
                'subject_load_id' => $request->subject_load_id,
                'quarter' => $request->quarter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to generate class records'], 500);
        }
    }

    public function export($subjectLoadId)
    {
        try {
            $response = $this->classRecordService->export($subjectLoadId);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to export class records', [
                'subject_load_id' => $subjectLoadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to export class records'], 500);
        }
    }

    public function downloadExcel($subjectLoadId, $fileName)
    {
        $filePath = public_path("classrecords/{$subjectLoadId}/{$fileName}");

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $fileName);
    }
}
