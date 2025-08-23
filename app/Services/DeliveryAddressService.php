<?php

namespace App\Services;

use App\Models\DeliveryAddress;

class DeliveryAddressService
{


    static public function saveOrUpdate(DeliveryAddress $deliveryAddress): DeliveryAddress
    {
        $oldDeliveryAddress = DeliveryAddress::where([
            ['user_id', $deliveryAddress->user_id],
            ['address', $deliveryAddress->address],
            ['latitude', $deliveryAddress->latitude],
            ['longitude', $deliveryAddress->longitude],
        ])->first();
        if ($oldDeliveryAddress != null) {
            return $oldDeliveryAddress;
        }
        //savings
        try {
            $deliveryAddress->save();
            $deliveryAddress->refresh();
        } catch (\Exception $ex) {
            logger("error saving the delivery address", [$ex]);
        }
        return $deliveryAddress;
    }
}
