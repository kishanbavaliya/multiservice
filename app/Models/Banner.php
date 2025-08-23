<?php

namespace App\Models;

use App\Traits\GoogleMapApiTrait;

class Banner extends BaseModel
{

    use GoogleMapApiTrait;

    protected $appends = ['formatted_date', 'photo'];
    protected $with = ["category", "vendor", "product", 'vendor_type'];

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }
    public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    public function vendor_type()
    {
        return $this->hasOne('App\Models\VendorType', 'id', 'vendor_type_id');
    }

    public function delivery_zones()
    {
        return $this->belongsToMany('App\Models\DeliveryZone');
    }


    //
    public function scopeByDeliveryZone($query, $latitude, $longitude)
    {
        //no filter by location
        if (!fetchDataByLocation()) {
            return $query;
        }
        //filter by location
        $deliveryZonesIds = $this->getDeliveryZonesByLocation($latitude, $longitude);
        return $query->whereHas("delivery_zones", function ($query) use ($deliveryZonesIds) {
            $query->whereIn('delivery_zone_id', $deliveryZonesIds);
        })->orWhereDoesntHave("delivery_zones");
    }
}