<?php

namespace App\Models;


class VehicleType extends NoDeleteBaseModel
{
    protected $appends = ['formatted_date', 'photo', 'icon', 'icon_base64'];

    public static function boot()
    {
        parent::boot();

        //while creating/inserting item into db
        static::creating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
        static::updating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
    }

    public function getIconAttribute()
    {
        return $this->getFirstMediaUrl('icon');
    }
    public function getIconBase64Attribute()
    {
        $filePath = $this->getFirstMediaPath('icon');
        if ($filePath == null) {
            return;
        }
        // Read the file contents
        $fileContents = file_get_contents($filePath);
        // Encode the file contents to base64
        $base64 = base64_encode($fileContents);
        return $base64;
    }
}