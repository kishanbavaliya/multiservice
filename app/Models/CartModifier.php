<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartModifier extends BaseModel
{
    use HasFactory;

    protected $table = 'cart_modifiers';

    protected $fillable = [
    'cart_item_id', 'modifier_id', 'modifier_group_id', 'name', 'price', 'quantity', 'restaurant_id'
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'int',
    ];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class, 'cart_item_id', 'id');
    }

    public function modifier()
    {
        return $this->belongsTo(RestaurantModifier::class, 'modifier_id', 'id');
    }

    public function modifierGroup()
    {
        return $this->belongsTo(RestaurantModifierGroup::class, 'modifier_group_id', 'id');
    }
}
