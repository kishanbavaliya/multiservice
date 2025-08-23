<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemCustomization extends Model
{
    protected $table = 'order_item_customizations';

    protected $fillable = [
        'order_item_id', 'customization_option_id', 'extra_price'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function option()
    {
        return $this->belongsTo(CustomizationOption::class, 'customization_option_id');
    }
}
