<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'records_name' => ['sometimes', 'string', 'max:50'],
            'student_id' => ['sometimes', 'exists:students,id'],
            'teacher_subject_load_id' => ['sometimes', 'exists:teacher_subject_loads,id'],
            'school_year_id' => ['sometimes', 'exists:school_years,id'],
            'total_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'student_score' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'records_type' => ['sometimes', Rule::in(['Written Works', 'Performance Tasks', 'Quarterly Assessment'])],
            'quarter' => ['sometimes', Rule::in(['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'])],
        ];
    }
}
