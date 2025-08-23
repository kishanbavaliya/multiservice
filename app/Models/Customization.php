<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    protected $table = 'customizations';

    protected $fillable = [
        'menu_item_id',
        'name',
    ];

    public function options()
    {
        return $this->hasMany(CustomizationOption::class, 'customization_id', 'id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id', 'id');
    }

}
