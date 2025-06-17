<?php

namespace App\Http\Controllers\Navigation;

use App\Http\Controllers\Controller;
use App\Models\Adviser;
use App\Models\Principal;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class PrincipalNavigationController extends Controller
{

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

        return view('principal.dashboard', compact('dashboard'));
    }

    public function viewStudents()
    {
        // Logic to retrieve students
        return view('principal.students');
    }

    public function viewTeachers()
    {
        // Logic to retrieve teachers
        return view('principal.teachers');
    }
}
