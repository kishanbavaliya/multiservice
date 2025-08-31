<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Restaurant extends Model
{
    protected $table = 'restaurants';

    protected $fillable = [
        'name', 'description', 'logo_url', 'image_url',
        'cuisine_type', 'address', 'latitude', 'longitude',
        'delivery_fee', 'min_delivery_time', 'max_delivery_time',
        'rating', 'offer_id'
    ];
    
    // Accessor for logo_url
    public function getLogoUrlAttribute($value)
    {
        return $value ? URL::to($value) : null;
    }

    // Accessor for image_url
    public function getImageUrlAttribute($value)
    {
        return $value ? URL::to($value) : null;
    }
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'restaurant_id', 'id');
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }

}
