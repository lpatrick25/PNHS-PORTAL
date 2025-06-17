<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use App\Models\Adviser;
use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\Principal;
use App\Models\Province;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherSubjectLoadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminNavigationController extends Controller
{
    protected $teacherSubjectLoadService;

    public function __construct(TeacherSubjectLoadService $teacherSubjectLoadService)
    {
        $this->teacherSubjectLoadService = $teacherSubjectLoadService;
    }

    public function viewDashboard()
    {
        // Logic to retrieve dashboard data
        $dashboard = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'principals' => Principal::count(),
            'advisers' => Adviser::count(),
            'users' => User::count(), // Assuming you have a User model for admin users
        ];

        return view('admin.dashboard', compact('dashboard'));
    }

    public function viewStudents()
    {
        // Logic to retrieve students
        return view('admin.students');
    }

    public function addStudent()
    {
        // Logic to show the form for adding a new student
        return view('admin.add_student');
    }

    public function updateStudent($studentLRN)
    {
        $provinces = Province::all();
        $municipalities = Municipality::all();
        $barangays = Barangay::all();
        $student = Student::where('student_lrn', $studentLRN)->firstOrFail();
        // Logic to retrieve student data for updating
        return view('admin.update_student', compact('student', 'provinces', 'municipalities', 'barangays'));
    }

    public function viewTeachers()
    {
        return view('admin.teachers');
    }

    public function addTeacher()
    {
        return view('admin.add_teacher');
    }

    public function updateTeacher($teacherId)
    {
        $provinces = Province::all();
        $municipalities = Municipality::all();
        $barangays = Barangay::all();
        $teacher = Teacher::findOrFail($teacherId);
        return view('admin.update_teacher', compact('teacher', 'provinces', 'municipalities', 'barangays'));
    }

    public function viewPrincipals()
    {
        return view('admin.principals');
    }

    public function addPrincipal()
    {
        return view('admin.add_principal');
    }

    public function updatePrincipal($principalId)
    {
        $provinces = Province::all();
        $municipalities = Municipality::all();
        $barangays = Barangay::all();
        $principal = Principal::findOrFail($principalId);
        return view('admin.update_principal', compact('principal', 'provinces', 'municipalities', 'barangays'));
    }

    public function viewAdvisers()
    {
        // Logic to retrieve advisers
        return view('admin.advisers');
    }

    public function addAdviserStudent(Adviser $adviser)
    {
        // Get all school years for the form
        $schoolYears = SchoolYear::all();

        // Get the current school year
        $currentSchoolYear = SchoolYear::where('current', true)->first();

        // Get all advisers with their teacher details
        $adviser = Adviser::with('teacher')->first();

        return view('admin.add_adviser_student', compact('adviser', 'schoolYears', 'currentSchoolYear'));
    }

    public function viewSubjects()
    {
        // Logic to retrieve subjects
        return view('admin.subjects');
    }

    public function viewTeacherSubjects()
    {
        // Logic to retrieve teacher subjects
        return view('admin.teacher_subjects');
    }

    public function getTeacherList()
    {
        try {
            return response()->json($this->teacherSubjectLoadService->getTeacherList());
        } catch (Exception $e) {
            Log::error('Failed to fetch teacher list in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['valid' => false, 'msg' => 'Failed to fetch teacher list'], 500);
        }
    }

    public function viewTeacherSubjectLoad($teacherId)
    {
        // Logic to retrieve teacher subject load
        try {
            $currentSchoolYear = SchoolYear::where('current', true)->firstOrFail();
            $teachers = Teacher::all();
            $subjects = Subject::all();
            return view('admin.teacher_subject_load', compact('teacherId', 'currentSchoolYear', 'teachers', 'subjects'));
        } catch (Exception $e) {
            Log::error('Failed to load teacher subject loads view', [
                'teacher_id' => $teacherId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to load teacher subject loads');
        }
    }

    public function getSectionsByGradeLevel(Request $request)
    {
        $gradeLevel = $request->query('grade_level');
        if (!in_array($gradeLevel, [7, 8, 9, 10, 11, 12])) {
            return response()->json([], 400);
        }
        return response()->json($this->teacherSubjectLoadService->getSectionsByGradeLevel($gradeLevel));
    }
}
