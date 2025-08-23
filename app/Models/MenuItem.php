<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'restaurant_id',
        'new_categories_id',
        'name',
        'description',
        'image_url',
        'base_price',
        'is_customizable',
    ];

    public function new_category()
    {
        return $this->belongsTo(NewCategory::class, 'new_categories_id');
    }

    public function customizations()
    {
        return $this->hasMany(Customization::class);
    }
}
