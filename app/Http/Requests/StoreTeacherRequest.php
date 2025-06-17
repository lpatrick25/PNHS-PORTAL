<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', 'string', 'max:255'],
            'province_code' => ['required', 'exists:provinces,province_code'],
            'municipality_code' => ['required', 'exists:municipalities,municipality_code'],
            'barangay_code' => ['required', 'exists:barangays,barangay_code'],
            'zip_code' => ['required', 'string', 'max:10'],
            'religion' => ['required', 'string', 'max:50'],
            'birthday' => ['required', 'date', function ($attribute, $value, $fail) {
                $age = now()->diffInYears($value);
                if ($age < 21) {
                    $fail('Teacher must be at least 21 years old.');
                }
            }],
            'sex' => ['required', 'in:Male,Female'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Divorced'],
            'email' => ['required', 'email', 'max:50', 'unique:teachers,email'],
            'contact' => ['required', 'regex:/^639\d{9}$/'],
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'The email is already taken.',
            'birthday.date' => 'The birthday must be a valid date.',
            'birthday.*' => 'Teacher must be at least 21 years old.',
            'contact.regex' => 'The contact must be a valid 11-digit Philippine mobile number starting with 639.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'contact' => $this->cleanPhoneNumber($this->contact),
        ]);
    }

    protected function cleanPhoneNumber($phone)
    {
        if ($phone) {
            return preg_replace('/[^0-9]/', '', $phone);
        }
        return $phone;
    }
}
