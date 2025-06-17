<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'student_lrn' => ['required', 'string', 'min:12', 'max:12', 'unique:students,student_lrn'],
            'rfid_no' => ['required', 'string', 'unique:students,rfid_no'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', 'string', 'max:255'],
            'province_code' => ['required', 'exists:provinces,province_code'],
            'municipality_code' => ['required', 'exists:municipalities,municipality_code'],
            'barangay_code' => ['required', 'exists:barangays,barangay_code'],
            'zip_code' => ['required', 'numeric', 'digits:4'],
            'religion' => ['required', 'string', 'max:50'],
            'birthday' => ['required', 'date', function ($attribute, $value, $fail) {
                $age = now()->diffInYears($value);
                if ($age < 11) {
                    $fail('Student must be at least 11 years old.');
                }
            }],
            'sex' => ['required', 'in:Male,Female'],
            'disability' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:50', 'unique:students,email'],
            'parent_contact' => ['required', 'regex:/^639\d{9}$/'],
            'contact' => ['required', 'regex:/^639\d{9}$/'],
            'present_province_code' => ['required', 'exists:provinces,province_code'],
            'present_municipality_code' => ['required', 'exists:municipalities,municipality_code'],
            'present_barangay_code' => ['required', 'exists:barangays,barangay_code'],
            'present_zip_code' => ['required', 'numeric', 'digits:4'],
            'mother_first_name' => ['required', 'string', 'max:255'],
            'mother_middle_name' => ['nullable', 'string', 'max:255'],
            'mother_last_name' => ['required', 'string', 'max:255'],
            'mother_address' => ['required', 'string', 'max:255'],
            'father_first_name' => ['required', 'string', 'max:255'],
            'father_middle_name' => ['nullable', 'string', 'max:255'],
            'father_last_name' => ['required', 'string', 'max:255'],
            'father_suffix' => ['nullable', 'string', 'max:255'],
            'father_address' => ['required', 'string', 'max:255'],
            'guardian' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'student_lrn.unique' => 'The Student LRN is already taken.',
            'rfid_no.unique' => 'The RFID No is already taken.',
            'email.unique' => 'The email is already taken.',
            'birthday.date' => 'The birthday must be a valid date.',
            'birthday.*' => 'Student must be at least 11 years old.',
            'parent_contact.regex' => 'The parent contact must be a valid 11-digit Philippine mobile number starting with 639.',
            'contact.regex' => 'The student contact must be a valid 11-digit Philippine mobile number starting with 639.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'parent_contact' => $this->cleanPhoneNumber($this->parent_contact),
            'contact' => $this->cleanPhoneNumber($this->contact),
        ]);
    }

    protected function cleanPhoneNumber($phone)
    {
        if ($phone) {
            return preg_replace('/[^0-9]/', '', $phone); // Extract only digits
        }
        return $phone;
    }
}
