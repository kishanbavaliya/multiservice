<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories'; // your actual table name

    protected $fillable = [
        'name',
        'icon_url',
        'item_count'
    ];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'new_category_id', 'id');
    }
}
