<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizationOption extends Model
{
    protected $table = 'customization_options';

    protected $fillable = [
        'customization_id', 'name', 'price'
    ];

    public function customization()
    {
        return $this->belongsTo(Customization::class, 'customization_id', 'id');
    }

}
