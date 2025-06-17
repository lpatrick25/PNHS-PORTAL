<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_code',
        'municipality_name',
        'region_code',
        'province_code',
        'zip_code',
    ];

    protected $primaryKey = 'municipality_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'municipality_code' => 'string',
        'region_code' => 'string',
        'province_code' => 'string',
        'zip_code' => 'string',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_code', 'region_code');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'province_code');
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'municipality_code', 'municipality_code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'municipality_code', 'municipality_code');
    }

    public function presentStudents()
    {
        return $this->hasMany(Student::class, 'present_municipality_code', 'municipality_code');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'municipality_code', 'municipality_code');
    }

    public function principals()
    {
        return $this->hasMany(Principal::class, 'municipality_code', 'municipality_code');
    }
}
