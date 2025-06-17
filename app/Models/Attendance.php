<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'school_year_id',
        'attendance_date',
        'status',
        'remarks',
        'subject_load_id',
    ];

    protected $dates = ['attendance_date'];

    protected $casts = [
        'attendance_date' => 'date',
        'status' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function teacherSubjectLoad()
    {
        return $this->belongsTo(TeacherSubjectLoad::class, 'subject_load_id');
    }
}
