<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class FoodCategory extends Model
{
    protected $fillable = ['name', 'image'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? URL::to($this->image) : null;
    }
}

