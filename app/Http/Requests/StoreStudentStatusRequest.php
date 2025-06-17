<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'adviser_id' => ['required', 'exists:advisers,id'],
            'grade_level' => ['required', 'in:7,8,9,10,11,12'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'section' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:ENROLLED,DROPPED'],
        ];
    }

    public function messages()
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'adviser_id.exists' => 'The selected adviser does not exist.',
            'grade_level.in' => 'The grade level must be one of 7, 8, 9, 10, 11, or 12.',
            'school_year_id.exists' => 'The selected school year does not exist.',
            'status.in' => 'The status must be either ENROLLED or DROPPED.',
        ];
    }
}
