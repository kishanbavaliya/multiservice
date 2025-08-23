<?php

namespace App\Models;


class BannerDeliveryZone extends NoDeleteBaseModel
{

    public $table = "banner_delivery_zone";
    public $timestamps = false;

    protected $fillable = [
        'banner_id',
        'delivery_zone_id',
    ];

    protected $with = [
        'delivery_zone'
    ];

    public function delivery_zone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}