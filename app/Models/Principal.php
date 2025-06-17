<?php

namespace App\Models;

use App\Traits\FullNameTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Principal extends Model implements HasMedia
{
    use HasFactory, FullNameTrait, InteractsWithMedia;

    protected $fillable = [
        'user_id',
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
        'civil_status',
        'email',
        'contact',
    ];

    protected $casts = [
        'birthday' => 'date',
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
