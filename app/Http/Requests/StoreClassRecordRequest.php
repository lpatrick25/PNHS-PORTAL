<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'records_name' => ['required', 'string', 'max:50'],
            'student_id' => ['required', 'exists:students,id'],
            'teacher_subject_load_id' => ['required', 'exists:teacher_subject_loads,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'total_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'student_score' => ['required', 'integer', 'min:0', 'max:100'],
            'records_type' => ['required', Rule::in(['Written Works', 'Performance Tasks', 'Quarterly Assessment'])],
            'quarter' => ['required', Rule::in(['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'])],
        ];
    }
}
