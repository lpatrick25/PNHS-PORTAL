<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FullNameTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use HasFactory, FullNameTrait, InteractsWithMedia;

    protected $fillable = [
        'student_lrn',
        'user_id',
        'rfid_no',
        'first_name',
        'middle_name',
        'last_name',
        'extension_name',
        'province_code',
        'municipality_code',
        'barangay_code',
        'zip_code',
        'religion',
        'birthday',
        'sex',
        'disability',
        'email',
        'parent_contact',
        'contact',
        'present_province_code',
        'present_municipality_code',
        'present_barangay_code',
        'present_zip_code',
        'mother_first_name',
        'mother_middle_name',
        'mother_last_name',
        'mother_address',
        'father_first_name',
        'father_middle_name',
        'father_last_name',
        'father_suffix',
        'father_address',
        'guardian',
        'guardian_address',
    ];

    protected $casts = [
        'birthday' => 'date',
        'sex' => 'string',
    ];

    protected $appends = ['full_name', 'full_name_with_extension'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_code', 'province_code');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_code', 'municipality_code');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_code', 'barangay_code');
    }

    public function presentProvince()
    {
        return $this->belongsTo(Province::class, 'present_province_code', 'province_code');
    }

    public function presentMunicipality()
    {
        return $this->belongsTo(Municipality::class, 'present_municipality_code', 'municipality_code');
    }

    public function presentBarangay()
    {
        return $this->belongsTo(Barangay::class, 'present_barangay_code', 'barangay_code');
    }

    public function studentStatuses()
    {
        return $this->hasMany(StudentStatus::class);
    }

    public function currentStatus()
    {
        return $this->hasOne(StudentStatus::class)->latest();
    }

    public function adviser()
    {
        return $this->hasOneThrough(
            Adviser::class,
            StudentStatus::class,
            'student_id', // Foreign key on StudentStatus
            'id',         // Local key on Adviser
            'id',         // Local key on Student
            'adviser_id'  // Foreign key on StudentStatus
        )->latest('student_statuses.created_at');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function classRecords()
    {
        return $this->hasMany(ClassRecord::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(50)
                    ->height(50)
                    ->optimize()
                    ->performOnCollections('avatar');
            });
    }
}
