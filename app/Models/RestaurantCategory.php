<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class RestaurantCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id', 'name', 'description', 'image_url', 'icon_url',
        'sort_order', 'is_active', 'is_featured'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'image_url_formatted',
        'icon_url_formatted',
        'product_count'
    ];

    // Accessors
    public function getImageUrlFormattedAttribute()
    {
        return $this->image_url ? URL::to($this->image_url) : null;
    }

    public function getIconUrlFormattedAttribute()
    {
        return $this->icon_url ? URL::to($this->icon_url) : null;
    }

    public function getProductCountAttribute()
    {
        return $this->products()->count();
    }

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function subcategories()
    {
        return $this->hasMany(RestaurantSubcategory::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(RestaurantProduct::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
