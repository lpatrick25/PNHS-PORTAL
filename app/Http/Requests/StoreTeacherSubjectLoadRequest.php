<?php

namespace App\Http\Requests;

use App\Models\TeacherSubjectLoad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherSubjectLoadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('teacher_subject_loads')
                    ->where(function ($query) {
                        return $query->where('grade_level', $this->grade_level)
                            ->where('section', $this->section)
                            ->where('school_year_id', $this->school_year_id);
                    })
            ],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_level' => ['required', 'in:7,8,9,10,11,12'],
            'section' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages()
    {
        return [
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'subject_id.unique' => 'This subject is already assigned to the selected grade level and section for this school year.',
            'school_year_id.exists' => 'The selected school year does not exist.',
            'grade_level.in' => 'The grade level must be one of 7, 8, 9, 10, 11, or 12.',
            'section.max' => 'Section must not exceed 20 characters.',
        ];
    }
}
