<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class APIController extends Controller
{
    /**
     * Retrieve student information by RFID number.
     *
     * @param Request $request
     * @param string $rfid_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentByRFID(Request $request, $rfid_no)
    {
        try {
            // Validate RFID input
            $validator = Validator::make(['rfid_no' => $rfid_no], [
                'rfid_no' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid RFID number.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Fetch student with related data
            $student = Student::with([
                'barangay',
                'municipality',
                'province',
                'presentBarangay',
                'presentMunicipality',
                'presentProvince',
            ])->where('rfid_no', $rfid_no)->first();

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Student not found.',
                ], 404);
            }

            $student->image = $student->getFirstMediaUrl('avatar', 'thumb') ?: url('dist/img/avatar.png');

            // Prepare response data
            // $response = [
            //     'student_lrn' => $student->student_lrn,
            //     'rfid_no' => $student->rfid_no,
            //     'full_name' => $student->full_name_with_extension,
            //     'first_name' => $student->first_name,
            //     'middle_name' => $student->middle_name,
            //     'last_name' => $student->last_name,
            //     'extension_name' => $student->extension_name,
            //     'email' => $student->email,
            //     'contact' => $student->contact,
            //     'birthday' => $student->birthday ? $student->birthday->format('F j, Y') : null,
            //     'sex' => $student->sex,
            //     'religion' => $student->religion,
            //     'disability' => $student->disability ?? 'None',
            //     'parent_contact' => $student->parent_contact,
            //     'mother_name' => trim("{$student->mother_first_name} {$student->mother_middle_name} {$student->mother_last_name}"),
            //     'father_name' => trim("{$student->father_first_name} {$student->father_middle_name} {$student->father_last_name} {$student->father_suffix}"),
            //     'guardian' => $student->guardian,
            //     'avatar' => $student->getFirstMediaUrl('avatar', 'thumb') ?: asset('images/default-avatar.png'),
            //     'address' => [
            //         'barangay' => $student->barangay ? $student->barangay->barangay_name : null,
            //         'municipality' => $student->municipality ? $student->municipality->municipality_name : null,
            //         'province' => $student->province ? $student->province->province_name : null,
            //         'zip_code' => $student->zip_code,
            //     ],
            //     'present_address' => [
            //         'barangay' => $student->presentBarangay ? $student->presentBarangay->barangay_name : null,
            //         'municipality' => $student->presentMunicipality ? $student->presentMunicipality->municipality_name : null,
            //         'province' => $student->presentProvince ? $student->presentProvince->province_name : null,
            //         'zip_code' => $student->present_zip_code,
            //     ],
            // ];

            return response()->json($student);
        } catch (\Exception $e) {
            Log::error('Error retrieving student by RFID: ' . $e->getMessage(), [
                'rfid_no' => $rfid_no,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to retrieve student information.',
            ], 500);
        }
    }
}
