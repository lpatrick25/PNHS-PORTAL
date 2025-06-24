<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\StudentStatus;
use App\Models\TeacherSubjectLoad;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherNavigationController extends Controller
{

    public function viewDashboard()
    {
        $teacher = auth()->user()->teacher;
        $currentSchoolYear = SchoolYear::where('current', true)->first();

        $dashboard = [
            'student_count' => 0,
            'subject_count' => 0,
            'advisory_section' => 'N/A',
            'advisory_grade_level' => 'N/A',
            'attendance_count' => 0,
            'class_record_count' => 0,
            'report_card_count' => 0,
        ];

        if ($teacher && $currentSchoolYear) {
            // Count students in advisory
            $advisory = $teacher->advisories()->where('school_year_id', $currentSchoolYear->id)->first();
            if ($advisory) {
                $dashboard['student_count'] = $advisory->students()->count();
                $dashboard['advisory_section'] = $advisory->section;
                $dashboard['advisory_grade_level'] = $advisory->grade_level;
            }

            // Count subjects handled
            $dashboard['subject_count'] = $teacher->subjectLoads()
                ->where('school_year_id', $currentSchoolYear->id)
                ->distinct('subject_id')
                ->count();

            // Placeholder for attendance, class records, and report cards
            // Replace with actual logic based on your models
            $dashboard['attendance_count'] = 0; // Example: Attendance::whereTeacherId($teacher->id)->count();
            $dashboard['class_record_count'] = 0; // Example: ClassRecord::whereTeacherId($teacher->id)->count();
            $dashboard['report_card_count'] = 0; // Example: ReportCard::whereTeacherId($teacher->id)->count();
        }

        return view('teacher.dashboard', compact('dashboard'));
    }

    public function viewAdvisory()
    {
        $schoolYears = SchoolYear::all();
        return view('teacher.advisory', compact('schoolYears'));
    }

    public function viewAdvisoryStudents(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $currentSchoolYear = SchoolYear::findOrFail($request->query('school_year_id'));

        if (!$teacher || !$currentSchoolYear) {
            return response()->json(['message' => 'Teacher or current school year not found'], 404);
        }

        $advisory = $teacher->advisories()->where('school_year_id', $currentSchoolYear->id)->first();
        if (!$advisory) {
            return response()->json(['message' => 'Advisory not found'], 404);
        }

        $students = $advisory->students()->get();

        $formattedStudents = $students->map(function ($student, $key) {
            return [
                'count' => $key + 1,
                'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($student->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                'rfid_no' => $student->rfid_no,
                'student_lrn' => $student->student_lrn,
                'student_name' => $student->full_name_with_extension,
                'contact' => $student->contact,
                'email' => $student->email,
                'status' => $student->currentStatus ? $student->currentStatus->status : 'N/A',
                'action' => '<button type="button" class="btn btn-md btn-primary" onclick="generateReportCard(' . $student->id . ')" title="Generate Report Card"><i class="fa fa-file"></i></button>',
            ];
        })->toArray();

        Log::info('Successfully fetched students', ['count' => count($formattedStudents)]);
        return response()->json($formattedStudents);
    }

    public function viewTeacherSubject()
    {
        $schoolYears = SchoolYear::all();
        return view('teacher.subjects', compact('schoolYears'));
    }

    public function viewTeacherSubjectLoad(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $schoolYearId = $request->query('school_year_id');
        $schoolYear = $schoolYearId ? SchoolYear::find($schoolYearId) : SchoolYear::where('current', true)->first();

        if (!$teacher || !$schoolYear) {
            return response()->json(['message' => 'Teacher or school year not found'], 404);
        }

        $subjectLoads = $teacher->subjectLoads()
            ->with(['subject', 'schoolYear'])
            ->where('school_year_id', $schoolYear->id)
            ->get();

        $formattedLoads = $subjectLoads->map(function ($load, $key) {
            return [
                'count' => $key + 1,
                'subject_code' => $load->subject->subject_code,
                'subject_name' => $load->subject->subject_name,
                'grade_level' => $load->grade_level,
                'section' => $load->section,
                'school_year' => $load->schoolYear->school_year,
                'action' => '<button type="button" class="btn btn-md btn-primary" onclick="view(' . $load->id . ')" title="View Students"><i class="fa fa-eye"></i></button>',
            ];
        })->toArray();

        Log::info('Successfully fetched subject loads', ['count' => count($formattedLoads), 'school_year_id' => $schoolYear->id]);
        return response()->json($formattedLoads);
    }

    public function viewSubjectStudents(Request $request)
    {
        $subjectLoadId = $request->query('subject_load_id');
        $subjectLoad = TeacherSubjectLoad::with(['schoolYear'])->find($subjectLoadId);

        if (!$subjectLoad) {
            return response()->json(['message' => 'Subject load not found'], 404);
        }

        $students = StudentStatus::where('school_year_id', $subjectLoad->school_year_id)
            ->where('grade_level', $subjectLoad->grade_level)
            ->where('section', $subjectLoad->section)
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter();

        $formattedStudents = $students->map(function ($student, $key) use ($subjectLoad) {
            return [
                'count' => $key + 1,
                'image' => '<img class="img img-fluid img-rounded" alt="User Avatar" src="' . ($student->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar.png')) . '" style="width: 50px;">',
                'student_lrn' => $student->student_lrn,
                'student_name' => $student->full_name_with_extension,
            ];
        })->toArray();

        Log::info('Successfully fetched students for subject load', ['count' => count($formattedStudents), 'subject_load_id' => $subjectLoadId]);
        return response()->json($formattedStudents);
    }

    public function viewAttendanceTeacher()
    {
        $schoolYears = SchoolYear::all();
        $currentSchoolYear = SchoolYear::where('current', true)->first();
        if (!$currentSchoolYear) {
            Log::warning('Current school year not found, redirecting to advisory');
            return redirect()->route('teacher.advisory');
        }
        $subjectLoads = auth()->user()->teacher->subjectLoads()
            ->where('school_year_id', $currentSchoolYear->id)
            ->with(['subject', 'schoolYear'])
            ->get();
        $teacherId = auth()->user()->teacher->id;
        return view('teacher.attendance', compact('schoolYears', 'currentSchoolYear', 'subjectLoads', 'teacherId'));
    }

    public function viewClassRecordTeacher()
    {
        try {
            $teacherId = auth()->user()->teacher->id;
            $schoolYears = SchoolYear::all();
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();
            return view('teacher.class-records', [
                'teacher_id' => $teacherId,
                'schoolYears' => $schoolYears,
                'currentSchoolYear' => $currentSchoolYear,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load class records view', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to load class records page.');
        }
    }

    public function viewReportCardTeacher()
    {
        $schoolYears = SchoolYear::all();
        return view('teacher.report-card', compact('schoolYears'));
    }

    public function viewProfile()
    {
        return view('teacher.profile');
    }
}
