<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject_code' => ['required', 'string', 'max:255', 'unique:subjects,subject_code'],
            'subject_name' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages()
    {
        return [
            'subject_code.unique' => 'This subject code already exists.',
            'subject_code.max' => 'Subject code must not exceed 255 characters.',
            'subject_name.max' => 'Subject name must not exceed 50 characters.',
        ];
    }
}
