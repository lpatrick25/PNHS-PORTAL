<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_code',
        'barangay_name',
        'region_code',
        'province_code',
        'municipality_code',
    ];

    protected $primaryKey = 'barangay_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'barangay_code' => 'string',
        'region_code' => 'string',
        'province_code' => 'string',
        'municipality_code' => 'string',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_code', 'region_code');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'province_code');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_code', 'municipality_code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'barangay_code', 'barangay_code');
    }

    public function presentStudents()
    {
        return $this->hasMany(Student::class, 'present_barangay_code', 'barangay_code');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'barangay_code', 'barangay_code');
    }

    public function principals()
    {
        return $this->hasMany(Principal::class, 'barangay_code', 'barangay_code');
    }
}
