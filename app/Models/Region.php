<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_code',
        'region_name',
    ];

    protected $primaryKey = 'region_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'region_code' => 'string',
    ];

    public function provinces()
    {
        return $this->hasMany(Province::class, 'region_code', 'region_code');
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class, 'region_code', 'region_code');
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class, 'region_code', 'region_code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'region_code', 'region_code');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'region_code', 'region_code');
    }

    public function principals()
    {
        return $this->hasMany(Principal::class, 'region_code', 'region_code');
    }
}
