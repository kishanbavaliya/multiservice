<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Events\WebsocketDriverNewOrderEvent;
use App\Models\AutoAssignment;
use App\Models\Order;
use App\Models\User;
use App\Services\DriverLocationService;
use App\Traits\GoogleMapApiTrait;
use App\Traits\OrderJobTrait;


class WebsocketRegularOrderMatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use GoogleMapApiTrait, OrderJobTrait;

    public Order $order;
    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $isNotProduction = env('APP_ENV') != 'production';
        if ($isNotProduction) {
            logger("Websocket Regular order matching");
        }

        // logger("Order loaded ==> " . $this->order->code . "");
        //
        $order = $this->order;
        $order->refresh();
        //check if driver as been assinged to order now
        if (!$this->canCalledMatchingJob($order)) {
            if ($isNotProduction) {
                logger("Driver has been assigned. Now closing matching for order ==> {$order->code}");
            }
            return;
        }


        //
        try {
            //get the pickup location
            $pickupLocationLat = $order->type != "parcel" ? $order->vendor->latitude : $order->pickup_location->latitude;
            $pickupLocationLng = $order->type != "parcel" ? $order->vendor->longitude : $order->pickup_location->longitude;
            $maxOnOrderForDriver = maxDriverOrderAtOnce($order);
            $driverSearchRadius = driverSearchRadius($order);
            $rejectedDriversCount = AutoAssignment::where('order_id', $order->id)->count();
            $maxDriverOrderNotificationAtOnce = ((int) maxDriverOrderNotificationAtOnce($order)) + ((int) $rejectedDriversCount);

            //Fetch: Drivers
            $driverDocuments = (new DriverLocationService())->getNearbyRegularDrivers(
                $pickupLocationLat,
                $pickupLocationLng,
                $driverSearchRadius,
                $maxDriverOrderNotificationAtOnce,
            );

            //
            if ($isNotProduction) {
                logger("Drivers data", [$driverDocuments]);
            }

            //
            foreach ($driverDocuments as $driverData) {

                $driver = User::where('id', $driverData["driver_id"])->first();
                //prevent vendor driver from getting order vendor order
                if (empty($driver) || ($driver->vendor_id != null && $driver->vendor_id != $order->vendor_id)) {
                    continue;
                }

                //check if he/she has a pending auto-assignment
                $anyPendingAutoAssignment = AutoAssignment::where([
                    'driver_id' => $driver->id,
                    'status' => "pending",
                ])->first();

                if (!empty($anyPendingAutoAssignment)) {
                    if ($isNotProduction) {
                        logger("there is pending auto assign");
                    }
                    continue;
                }

                //check if he/she has a pending auto-assignment
                $rejectedThisOrderAutoAssignment = AutoAssignment::where([
                    'driver_id' => $driver->id,
                    'order_id' => $order->id,
                    'status' => "rejected",
                ])->first();

                if (!empty($rejectedThisOrderAutoAssignment)) {
                    if ($isNotProduction) {
                        logger("" . $driver->name . " => rejected this order => " . $order->code . "");
                    }
                    continue;
                } else {
                    if ($isNotProduction) {
                        logger("" . $driver->name . " => is being notified about this order => " . $order->code . "");
                    }
                }
                if ($isNotProduction) {
                    logger("Drivers data", [$driver->is_active, $driver->is_online, $maxOnOrderForDriver, $driver->assigned_orders]);
                }

                if ($driver->is_active && $driver->is_online && ((int)$maxOnOrderForDriver > $driver->assigned_orders)) {

                    //assign order to him/her
                    $autoAssignment = new AutoAssignment();
                    $autoAssignment->order_id = $order->id;
                    $autoAssignment->driver_id = $driver->id;
                    $autoAssignment->save();

                    //add the new order to it
                    $pickupLocationLat = $order->type != "parcel" ? $order->vendor->latitude : $order->pickup_location->latitude;
                    $pickupLocationLng = $order->type != "parcel" ? $order->vendor->longitude : $order->pickup_location->longitude;
                    $driverDistanceToPickup = $this->getDistance(
                        [
                            $pickupLocationLat,
                            $pickupLocationLng
                        ],
                        [
                            $driverData["lat"],
                            $driverData["lng"],
                        ]
                    );
                    $pickup = [
                        'lat' => $pickupLocationLat,
                        'long' => $pickupLocationLng,
                        'address' => $order->type != "parcel" ? $order->vendor->address : $order->pickup_location->address,
                        'city' => $order->type != "parcel" ? "" : $order->pickup_location->city,
                        'state' => $order->type != "parcel" ? "" : $order->pickup_location->state ?? "",
                        'country' => $order->type != "parcel" ? "" : $order->pickup_location->country ?? "",
                        "distance" => number_format($driverDistanceToPickup, 2),
                    ];


                    //dropoff data
                    $dropoffLocationLat = $order->type != "parcel" ? $order->delivery_address->latitude : $order->dropoff_location->latitude;
                    $dropoffLocationLng = $order->type != "parcel" ? $order->delivery_address->longitude : $order->dropoff_location->longitude;
                    $driverDistanceToDropoff = $this->getDistance(
                        [
                            $dropoffLocationLat,
                            $dropoffLocationLng
                        ],
                        [
                            $driverData["lat"],
                            $driverData["lng"],
                        ]
                    );

                    $dropoff = [
                        'lat' => $dropoffLocationLat,
                        'long' => $dropoffLocationLng,
                        'address' => $order->type != "parcel" ? $order->delivery_address->address : $order->dropoff_location->address,
                        'city' =>  $order->type != "parcel" ? "" : $order->dropoff_location->city,
                        'state' => $order->type != "parcel" ? "" : $order->pickup_location->state ?? "",
                        'country' => $order->type != "parcel" ? "" : $order->pickup_location->country ?? "",
                        "distance" => number_format($driverDistanceToDropoff, 2),
                    ];
                    //
                    $newOrderData = [
                        "pickup" => json_encode($pickup),
                        "dropoff" => json_encode($dropoff),
                        "pickup_distance"   => number_format($driverDistanceToPickup, 2),
                        'amount' => (string)$order->delivery_fee,
                        'total' => (string)$order->total,
                        'vendor_id' => (string)$order->vendor_id,
                        'is_parcel' => (string)($order->type == "parcel"),
                        'package_type' =>  (string)($order->package_type->name ?? ""),
                        'id' => (string)$order->id,
                        'range' => (string)$order->vendor->delivery_range,
                        "notificationTime" => setting('alertDuration', 15),
                    ];
                    //send the event now
                    event(new WebsocketDriverNewOrderEvent($driver->id, $newOrderData));
                    if ($isNotProduction) {
                        logger("WebsocketDriverNewOrderEvent Event dispatched");
                    }
                }
            }
        } catch (\Exception $ex) {
            logger("Error Match Order", [$order->id]);
            logger("Matching Order Job Error", [$ex]);
        }


        //queue another check to resend order incase no driver accepted the order
        // logger("queue another check to resend order incase no driver accepted the order");
        $alertDuration = ((int) setting('alertDuration', 15)) + 10;
        $delayFor = now()->addSeconds($alertDuration);
        WebsocketRegularOrderMatchingJob::dispatch($order)->delay($delayFor);
    }


    //
    public function getDistance($loc1, $loc2)
    {
        $geopointA = new GeoPoint($loc1[0], $loc1[1]);
        $geopointB = new GeoPoint($loc2[0], $loc2[1]);
        return $geopointA->distanceTo($geopointB, 'kilometers');
    }
}