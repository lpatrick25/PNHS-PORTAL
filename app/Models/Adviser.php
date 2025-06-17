<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adviser extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'grade_level',
        'section',
        'school_year_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentStatus::class,
            'adviser_id', // Foreign key on StudentStatus
            'id',         // Local key on Student
            'id',         // Local key on Adviser
            'student_id'  // Foreign key on StudentStatus
        )->where('student_statuses.school_year_id', $this->school_year_id)
            ->where('student_statuses.grade_level', $this->grade_level)
            ->where('student_statuses.section', $this->section);
    }

    public function studentStatuses()
    {
        return $this->hasMany(StudentStatus::class);
    }
}
