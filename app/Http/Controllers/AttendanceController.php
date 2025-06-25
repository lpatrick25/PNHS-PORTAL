<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Models\SchoolYear;
use App\Models\TeacherSubjectLoad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function viewAttendanceTeacher(Request $request)
    {
        try {
            $schoolYears = SchoolYear::all();
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();
            $teacher_id = auth()->user()->teacher->id;
            return view('admin.attendances', compact('schoolYears', 'currentSchoolYear', 'teacher_id'));
        } catch (Exception $e) {
            Log::error('Failed to load attendance view', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load attendance page');
        }
    }

    public function index(Request $request)
    {
        try {
            $teacherId = $request->query('teacher_id');
            $schoolYearId = $request->query('school_year_id');
            $subjectLoadId = $request->query('subject_load_id');
            return response()->json($this->attendanceService->index($teacherId, $schoolYearId, $subjectLoadId));
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance records in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch attendance records'], 500);
        }
    }

    public function bySubjectLoad($subject_load_id)
    {
        try {
            return response()->json($this->attendanceService->bySubjectLoad($subject_load_id));
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance by subject load', [
                'subject_load_id' => $subject_load_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch attendance records'], 500);
        }
    }

    public function byDate($subject_load_id, $attendance_date)
    {
        try {
            return response()->json($this->attendanceService->byDate($subject_load_id, $attendance_date));
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance by date', [
                'subject_load_id' => $subject_load_id,
                'attendance_date' => $attendance_date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch attendance records'], 500);
        }
    }

    public function generate(Request $request)
    {
        try {
            if ($request->attendance_date) {
                $request->merge([
                    'attendance_date' => date('Y-m-d')
                ]);
            }

            $request->validate([
                'subject_load_id' => ['required', 'exists:teacher_subject_loads,id'],
                'attendance_date' => ['required', 'date'],
            ]);
            $response = $this->attendanceService->generate($request->subject_load_id, $request->attendance_date);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to generate attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to generate attendance'], 500);
        }
    }

    public function store(StoreAttendanceRequest $request)
    {
        try {
            $data = $request->validated();
            $response = $this->attendanceService->store($data);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to store attendance in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to record attendance'], 500);
        }
    }

    public function show(Attendance $attendance)
    {
        try {
            return response()->json($attendance->load(['student', 'schoolYear', 'subjectLoad']));
        } catch (Exception $e) {
            Log::error('Failed to fetch attendance record', [
                'attendance_id' => $attendance->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Attendance record not found'], 404);
        }
    }

    public function update(UpdateAttendanceRequest $request, $attendanceId)
    {
        try {
            $data = $request->validated();
            $response = $this->attendanceService->update($attendanceId, $data);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to update attendance in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to update attendance'], 500);
        }
    }

    public function destroy($attendanceId)
    {
        try {
            $response = $this->attendanceService->destroy($attendanceId);
            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to delete attendance in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to delete attendance'], 500);
        }
    }

    public function processRfid(Request $request)
    {
        try {
            $request->validate([
                'rfid_no' => ['required', 'string'],
                'subject_load_id' => ['required', 'exists:teacher_subject_loads,id'],
                'attendance_date' => ['required', 'date'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ]);

            $response = $this->attendanceService->processRfidAttendance($request->only([
                'rfid_no',
                'subject_load_id',
                'attendance_date',
                'remarks',
            ]));

            return response()->json($response, $response['valid'] ? 200 : 422);
        } catch (Exception $e) {
            Log::error('Failed to process RFID attendance in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to process RFID attendance'], 500);
        }
    }
}
