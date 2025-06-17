<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['present', 'absent', 'late'])],
            'remarks' => ['nullable', 'string', 'max:255'],
            'subject_load_id' => [
                'required',
                'exists:teacher_subject_loads,id',
                Rule::unique('attendances')
                    ->where(function ($query) {
                        return $query->where('student_id', $this->student_id)
                            ->where('attendance_date', $this->attendance_date);
                    }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'school_year_id.exists' => 'The selected school year does not exist.',
            'attendance_date.date' => 'The attendance date must be a valid date.',
            'status.in' => 'The status must be one of present, absent, or late.',
            'remarks.max' => 'Remarks must not exceed 255 characters.',
            'subject_load_id.exists' => 'The selected subject load does not exist.',
            'subject_load_id.unique' => 'Attendance for this student on this date and subject has already been recorded.',
        ];
    }
}
