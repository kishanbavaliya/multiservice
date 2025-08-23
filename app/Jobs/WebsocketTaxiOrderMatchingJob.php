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
use Carbon\Carbon;

class WebsocketTaxiOrderMatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use GoogleMapApiTrait;

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
            logger("Websocket Taxi order matching");
        }

        // logger("Order loaded ==> " . $this->order->code . "");
        //
        $order = $this->order;
        $order->refresh();
        //check if driver as been assinged to order now
        if (!empty($order->driver_id)) {
            if ($isNotProduction) {
                logger("Driver has been assigned. Now closing matching for order ==> {$order->code}");
            }
            return;
        }
        if (in_array($order->status, ["cancelled", "delivered", "failed", "scheduled"])) {
            if ($isNotProduction) {
                logger("Order Status updated. Now closing matching for order ==> {$order->status}");
            }
            return;
        }


        //to ensure unpaid online order is not sent to drivers
        if ($order->can_auto_assign_driver) {

            try {
                $pickupLocationLat = $order->taxi_order->pickup_latitude;
                $pickupLocationLng = $order->taxi_order->pickup_longitude;
                $dropoffLocationLat = $order->taxi_order->dropoff_latitude;
                $dropoffLocationLng = $order->taxi_order->dropoff_longitude;
                //
                $pickupLat = "" . $order->taxi_order->pickup_latitude . "," . $order->taxi_order->pickup_longitude;
                $dropoffLat = "" . $order->taxi_order->dropoff_latitude . "," . $order->taxi_order->dropoff_longitude;
                $driverSearchRadius = driverSearchRadius($order);
                $rejectedDriversCount = AutoAssignment::where('order_id', $order->id)->count();
                $maxDriverOrderNotificationAtOnce = (int) setting('maxDriverOrderNotificationAtOnce', 1) + $rejectedDriversCount;

                //
                $driverDocuments = (new DriverLocationService())->getNearbyTaxiDrivers(
                    $pickupLocationLat,
                    $pickupLocationLng,
                    $driverSearchRadius,
                    $maxDriverOrderNotificationAtOnce,
                    $order->taxi_order->vehicle_type_id,
                );
                if ($isNotProduction) {
                    logger("Drivers found for order =>" . $order->code . "", [$driverDocuments]);
                }
                //if no driver was found, create another delayed job
                if (empty($driverDocuments)) {
                    if ($isNotProduction) {
                        logger("No Driver found. Now rescheduling the order for another time");
                    }
                    //don't reschedule if the settings is not allowed
                    $delaySchedule = setting('delayResearchTaxiMatching');
                    $isAlowed = !empty($delaySchedule) && ((int)$delaySchedule) > 0;
                    if ($isAlowed) {
                        $nextCallDelay = (int) setting('delayResearchTaxiMatching', 30);
                        WebsocketTaxiOrderMatchingJob::dispatch($order)->delay($nextCallDelay);
                    }
                    return;
                }

                //rearrange drivers by the closet to the location
                $newDriverDocuments = collect($driverDocuments);
                $newDriverDocuments = $newDriverDocuments->sortBy('distance');
                $driverDocuments = $newDriverDocuments->toArray();
                //
                foreach ($driverDocuments as $driverData) {

                    //found closet driver
                    $driver = User::where('id', $driverData["driver_id"])->first();
                    if (empty($driver)) {
                        logger("Invalid user account");
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


                    try {


                        \DB::beginTransaction();

                        //assign order to him/her
                        $autoAssignment = new AutoAssignment();
                        $autoAssignment->order_id = $order->id;
                        $autoAssignment->driver_id = $driver->id;
                        $autoAssignment->save();

                        //add the new order to it
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

                        $driverDistanceToDropoff = $this->getDistance(
                            [
                                $pickupLocationLat,
                                $pickupLocationLng
                            ],
                            [
                                $dropoffLocationLat,
                                $dropoffLocationLng
                            ]
                        );


                        //pickup data
                        $pickup = [
                            'lat' => $pickupLocationLat,
                            'lng' => $pickupLocationLng,
                            'address' => $order->taxi_order->pickup_address,
                            "distance" => number_format($driverDistanceToPickup, 2),
                        ];


                        //dropoff data
                        $dropoffLocationLat = $order->taxi_order->dropoff_latitude;
                        $dropoffLocationLng = $order->taxi_order->dropoff_longitude;
                        $dropoff = [
                            'lat' => $dropoffLocationLat,
                            'lng' => $dropoffLocationLng,
                            'address' => $order->taxi_order->dropoff_address,
                            "distance" => number_format($driverDistanceToDropoff, 2),
                        ];



                        //get when the order can expire
                        $newTimestamp = $this->getExpireTimestamp($order);
                        //
                        $newOrderData = [
                            "dropoff" => json_encode($dropoff),
                            "pickup" => json_encode($pickup),
                            'amount' => (string)$order->total,
                            'total' => (string)$order->total,
                            'id' => (string)$order->id,
                            'range' => (string) $driverSearchRadius,
                            'status' => (string)$order->status,

                            'pickup_distance' => $driverDistanceToPickup,
                            'trip_distance' => $this->getRelativeDistance($pickupLat, $dropoffLat),
                            'code' => $order->code,
                            'vehicle_type_id' => $order->taxi_order->vehicle_type_id,
                            'earth_distance' => $this->getEarthDistance(
                                $order->taxi_order->pickup_latitude,
                                $order->taxi_order->pickup_longitude,
                            ),
                            'exipres_at' => $newTimestamp,
                            'exipres_at_timestamp' => Carbon::createFromTimestamp($newTimestamp)->toDateTimeString(),

                        ];
                        //send the event now
                        event(new WebsocketDriverNewOrderEvent($driver->id, $newOrderData));
                        if ($isNotProduction) {
                            logger("WebsocketDriverNewOrderEvent Event dispatched");
                        }


                        \DB::commit();
                    } catch (\Exception $ex) {
                        \DB::rollback();
                        logger("Skipping Taxi Order Matching", [$ex]);
                    }
                }
            } catch (\Exception $ex) {
                logger("Skipping Order", [$order->id]);
                logger("Order Error", [$ex]);
            }
        }

        //queue another check to resend order incase no driver accepted the order
        // logger("queue another check to resend order incase no driver accepted the order");
        $alertDuration = ((int) setting('alertDuration', 15)) + 10;
        $delayFor = now()->addSeconds($alertDuration);
        WebsocketTaxiOrderMatchingJob::dispatch($order)->delay($delayFor);
        if ($isNotProduction) {
            logger("Websocket Taxi order matching -- END");
        }
    }


    //
    public function getDistance($loc1, $loc2)
    {
        $geopointA = new GeoPoint($loc1[0], $loc1[1]);
        $geopointB = new GeoPoint($loc2[0], $loc2[1]);
        return $geopointA->distanceTo($geopointB, 'kilometers');
    }

    public function getExpireTimestamp($order)
    {
        $currentTimeStamp = Carbon::now()->timestamp;
        $nextTimestamp = $currentTimeStamp + (setting('alertDuration', 15) * 1000);
        return $nextTimestamp;
    }
}
