<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year',
        'start_date',
        'end_date',
        'current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'current' => 'boolean',
    ];

    public function advisers()
    {
        return $this->hasMany(Adviser::class);
    }

    public function studentStatuses()
    {
        return $this->hasMany(StudentStatus::class);
    }

    public function teacherSubjectLoads()
    {
        return $this->hasMany(TeacherSubjectLoad::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function classRecords()
    {
        return $this->hasMany(ClassRecord::class);
    }

}
