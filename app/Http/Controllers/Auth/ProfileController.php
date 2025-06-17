<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function viewProfile()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please log in to view your profile.');
            }

            $role = $user->role;
            $data = null;
            $view = 'index';

            switch ($role) {
                case \App\Models\User::ROLE_STUDENT:
                    $student = $user->student;
                    if (!$student) {
                        abort(404, 'Student profile not found.');
                    }
                    $data = $this->getProfileData($student);
                    $view = 'student.profiles';
                    break;

                case \App\Models\User::ROLE_TEACHER:
                    $teacher = $user->teacher;
                    if (!$teacher) {
                        abort(404, 'Teacher profile not found.');
                    }
                    $data = $this->getProfileData($teacher);
                    $view = 'teacher.profiles';
                    break;

                case \App\Models\User::ROLE_PRINCIPAL:
                    $principal = $user->principal;
                    if (!$principal) {
                        abort(404, 'Principal profile not found.');
                    }
                    $data = $this->getProfileData($principal);
                    $view = 'principal.profiles';
                    break;

                default:
                    return redirect()->route('home')->with('error', 'Invalid user role.');
            }

            return view($view, compact('data'));
        } catch (\Exception $e) {
            Log::error('Error fetching user profile: ' . $e->getMessage());
            abort(500, 'Failed to retrieve profile. Please try again later.');
        }
    }

    private function getProfileData($model)
    {
        $barangay = $model->barangay;
        $municipality = $model->municipality;
        $province = $model->province;
        $region = $model->region;

        return [
            'teacher_id' => $model->id,
            'full_name' => $model->full_name_with_extension,
            'first_name' => $model->first_name,
            'middle_name' => $model->middle_name,
            'last_name' => $model->last_name,
            'extension_name' => $model->extension_name,
            'email' => $model->email,
            'contact' => $model->contact,
            'birthday' => $model->birthday ? $model->birthday->format('F j, Y') : null,
            'religion' => $model->religion,
            'civil_status' => $model->civil_status ?? null, // Not applicable for Student
            'barangay_name' => $barangay ? $barangay->barangay_name : null,
            'municipality_name' => $municipality ? $municipality->municipality_name : null,
            'province_name' => $province ? $province->province_name : null,
            'region_name' => $region ? $region->region_name : null,
            'zip_code' => $model->zip_code,
            // Student-specific fields
            'student_lrn' => $model instanceof \App\Models\Student ? $model->student_lrn : null,
            'sex' => $model instanceof \App\Models\Student ? $model->sex : null,
            'disability' => $model instanceof \App\Models\Student ? $model->disability : null,
            'parent_contact' => $model instanceof \App\Models\Student ? $model->parent_contact : null,
            'present_barangay_name' => $model instanceof \App\Models\Student && $model->presentBarangay ? $model->presentBarangay->barangay_name : null,
            'present_municipality_name' => $model instanceof \App\Models\Student && $model->presentMunicipality ? $model->presentMunicipality->municipality_name : null,
            'present_province_name' => $model instanceof \App\Models\Student && $model->presentProvince ? $model->presentProvince->province_name : null,
            'present_zip_code' => $model instanceof \App\Models\Student ? $model->present_zip_code : null,
        ];
    }
}
