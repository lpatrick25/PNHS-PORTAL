<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolYearRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'school_year' => ['required', 'string', 'max:10', Rule::unique('school_years', 'school_year')->ignore($this->school_year_id), 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => ['required', 'date', 'before:end_date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'current' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'school_year.unique' => 'This school year already exists.',
            'school_year.regex' => 'School year must be in the format YYYY-YYYY (e.g., 2024-2025).',
            'start_date.before' => 'Start date must be before the end date.',
            'end_date.after' => 'End date must be after the start date.',
        ];
    }
}
