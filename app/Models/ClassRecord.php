<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_subject_load_id',
        'school_year_id',
        'records_name',
        'records_type',
        'total_score',
        'student_score',
        'quarter',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'student_score' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacherSubjectLoad()
    {
        return $this->belongsTo(TeacherSubjectLoad::class, 'teacher_subject_load_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
