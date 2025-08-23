<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCustomization extends Model
{
    protected $table = 'item_customizations';

    protected $fillable = [
        'menu_item_id', 'name', 'selection_type', 'required'
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function options()
    {
        return $this->hasMany(CustomizationOption::class, 'customization_id');
    }
}
