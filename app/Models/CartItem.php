<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends BaseModel
{
    protected $fillable = [
        'cart_id', 'quantity', 'price',
        'restaurant_id', 'restaurant_product_id', 'restaurant_category_id', 'restaurant_subcategory_id'
    ];

    protected $casts = [
        'quantity' => 'int',
        'price' => 'double',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function product()
    {
    return $this->belongsTo(RestaurantProduct::class, 'restaurant_product_id', 'id');
    }

    public function modifiers()
    {
        return $this->hasMany(CartModifier::class, 'cart_item_id', 'id');
    }
}
