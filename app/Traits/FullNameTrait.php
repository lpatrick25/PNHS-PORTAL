<?php

namespace App\Traits;

trait FullNameTrait
{
    public function getFullNameAttribute()
    {
        $name = trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
        return $name === '' ? null : $name;
    }

    public function getFullNameWithExtensionAttribute()
    {
        $name = trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
        $extension = trim($this->extension_name);
        $name = $name === '' ? null : $name;
        $extension = $extension === '' ? '' : " {$extension}";
        return $name . $extension;
    }
}
