<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdviserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'grade_level' => ['required', 'in:7,8,9,10,11,12'],
            'section' => ['required', 'string', 'max:20', 'unique:advisers,section'],
        ];
    }

    public function messages()
    {
        return [
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'grade_level.in' => 'The grade level must be one of 7, 8, 9, 10, 11, or 12.',
            'section.unique' => 'The section is already taken.',
        ];
    }
}
