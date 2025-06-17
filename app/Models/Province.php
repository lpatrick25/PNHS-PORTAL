<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_code',
        'province_name',
        'region_code',
    ];

    protected $primaryKey = 'province_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'province_code' => 'string',
        'region_code' => 'string',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_code', 'region_code');
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class, 'province_code', 'province_code');
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'province_code', 'province_code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'province_code', 'province_code');
    }

    public function presentStudents()
    {
        return $this->hasMany(Student::class, 'present_province_code', 'province_code');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'province_code', 'province_code');
    }

    public function principals()
    {
        return $this->hasMany(Principal::class, 'province_code', 'province_code');
    }
}
