<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'discount_type', 'discount_value', 'restaurant_id', 'start_at', 'end_at', 'active'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'active' => 'boolean'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}

