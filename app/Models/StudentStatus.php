<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'adviser_id',
        'school_year_id',
        'grade_level',
        'section',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function adviser()
    {
        return $this->belongsTo(Adviser::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
