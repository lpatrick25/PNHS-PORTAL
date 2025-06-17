<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubjectLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'school_year_id',
        'grade_level',
        'section',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'subject_load_id');
    }

    public function classRecords()
    {
        return $this->hasMany(ClassRecord::class, 'teacher_subject_load_id');
    }

    public function adviser()
    {
        return $this->hasOne(Adviser::class)
            ->where('grade_level', $this->grade_level)
            ->where('section', $this->section)
            ->where('school_year_id', $this->school_year_id);
    }
}
