<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends BaseModel
{
    protected $fillable = [
        'user_id', 'vendor_id', 'restaurant_id', 'sub_total', 'total', 'offer_id'
    ];

    protected $casts = [
        'sub_total' => 'double',
        'total' => 'double',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
