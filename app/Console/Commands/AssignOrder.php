<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use AnthonyMartin\GeoLocation\GeoPoint;
use App\Models\AutoAssignment;
use App\Services\AutoAssignmentService;
use App\Services\FirestoreRestService;
use App\Traits\FirebaseAuthTrait;
use App\Services\FirestoreCloudFunctionService;


class AssignOrder extends Command
{

    use FirebaseAuthTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find driver to assign order to';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //if communitcate via job is enabled
        $useFCMJob = (bool) setting('useFCMJob', "0");
        if ($useFCMJob) {
            return;
        }


        ///regular cron job matching
        $autoAsignmentStatus = setting('autoassignment_status', "ready");
        //get orders in ready state
	$orders = Order::currentStatus($autoAsignmentStatus)
    ->where(function ($query) {
        $query
            // branch 1: no vendor (taxi orders only)
            ->where(function ($q) {
                $q->whereDoesntHave('vendor')
                  ->whereNull('driver_id')
                  ->whereHas('taxi_order');
            })

            // OR branch 2: vendor exists with conditions
            ->orWhere(function ($q) {
                $q->whereHas('vendor', function ($query) {
                    $query->where('auto_assignment', 1)
                        ->whereHas('vendor_type', function ($query) {
                            $query->whereNotIn('slug', ["booking", "service"]);
                        });
                })
                ->doesntHave("auto_assignment")
                ->where(function ($query) {
                    $query->whereNotNull('delivery_address_id')
                          ->orWhereHas('stops');
                });
            });
    })
    ->limit(20)
    ->get();
         logger("orders", [$orders->pluck('id')]);
        //
        foreach ($orders as $order) {
            // logger("Order loaded ==> " . $order->code . "");
            //

            try {
                //get the pickup location
                if(!empty($order->taxi_order)) {
                    $pickupLocationLat = $order->taxi_order->pickup_latitude;
                    $pickupLocationLng = $order->taxi_order->pickup_longitude;
                } else {
                    $pickupLocationLat = $order->type != "parcel" ? $order->vendor->latitude : $order->pickup_location->latitude;
                    $pickupLocationLng = $order->type != "parcel" ? $order->vendor->longitude : $order->pickup_location->longitude;
                }
                $maxOnOrderForDriver = maxDriverOrderAtOnce($order);
                $driverSearchRadius = driverSearchRadius($order);
                $rejectedDriversCount = AutoAssignment::where('order_id', $order->id)->count();
                $maxDriverOrderNotificationAtOnce = ((int) maxDriverOrderNotificationAtOnce($order)) + ((int) $rejectedDriversCount);
                logger("1");

                ////fetch driver in different ways
                $fetchNearbyDriverSystem = setting('fetchNearbyDriverSystem', 0);
                if ($fetchNearbyDriverSystem == 0) {
                logger("2");

                    //find driver within that range
                    $vehicleTypeId = null;
                    if(!empty($order->taxi_order)) {
                        $vehicleTypeId = $order->taxi_order->vehicle_type_id;
                    }  
                    $firestoreRestService = new FirestoreRestService();
                    $driverDocuments = $firestoreRestService->whereWithinGeohash(
                        $pickupLocationLat,
                        $pickupLocationLng,
                        $driverSearchRadius,
                        $rejectedDriversCount,
                        $vehicleTypeId
                    );
                } else {
                logger("3");

                    // logger("data from ==> firebaseCloudFunctionService->nearbyDriver");
                    //find driver within that range
                    $firebaseCloudFunctionService = new FirestoreCloudFunctionService();
                    $driverDocuments = $firebaseCloudFunctionService->nearbyDriver(
                        $pickupLocationLat,
                        $pickupLocationLng,
                        $driverSearchRadius,
                        $limit = $maxDriverOrderNotificationAtOnce,
                    );
                }
                logger("4");

                //
                // logger("Drivers data", [$driverDocuments]);
                
                //
                foreach ($driverDocuments as $driverData) {
                logger("5");

                    //found closet driver
                    $driver = User::where('id', $driverData["id"])->first();
                    //prevent vendor driver from getting order vendor order
                    if (empty($driver) || ($driver->vendor_id != null && $driver->vendor_id != $order->vendor_id)) {
                        continue;
                    }
                logger("6");

                    //check the distance between this driver and pickup location
                    $tooFar = $this->isDriverFar(
                        $pickupLocationLat,
                        $pickupLocationLng,
                        $driverData["lat"],
                        $driverData["long"],
                        $order,
                    );
                    if ($tooFar) {
                        $autoAssignment = new AutoAssignment();
                        $autoAssignment->order_id = $order->id;
                        $autoAssignment->driver_id = $driver->id;
                        $autoAssignment->status = "rejected";
                        $autoAssignment->save();
                        continue;
                    }
                logger("7");

                    //check if he/she has a pending auto-assignment
                    $anyPendingAutoAssignment = AutoAssignment::where([
                        'driver_id' => $driver->id,
                        'status' => "pending",
                    ])->first();

                    if (!empty($anyPendingAutoAssignment)) {
                        // logger("there is pending auto assign");
                        continue;
                    }
                logger("8");

                    //check if he/she has a pending auto-assignment
                    $rejectedThisOrderAutoAssignment = AutoAssignment::where([
                        'driver_id' => $driver->id,
                        'order_id' => $order->id,
                        'status' => "rejected",
                    ])->first();

                    if (!empty($rejectedThisOrderAutoAssignment)) {
                        // logger("" . $driver->name . " => rejected this order => " . $order->code . "");
                        continue;
                    } else {
                        // logger("" . $driver->name . " => is being notified about this order => " . $order->code . "");
                    }
                logger("9");

                    // logger("Drivers data", [$driver->is_active, $driver->is_online, $maxOnOrderForDriver, $driver->assigned_orders]);

                    if ($driver->is_active && $driver->is_online && ((int)$maxOnOrderForDriver > $driver->assigned_orders)) {
                logger("10");

                        //assign order to him/her
                        $autoAssignment = new AutoAssignment();
                        $autoAssignment->order_id = $order->id;
                        $autoAssignment->driver_id = $driver->id;
                        $autoAssignment->save();

                        //add the new order to it
                        if(!empty($order->taxi_order)) {
                            $pickupLocationLat = $order->taxi_order->pickup_latitude;
                            $pickupLocationLng = $order->taxi_order->pickup_longitude;
                        } else {
                            $pickupLocationLat = $order->type != "parcel" ? $order->vendor->latitude : $order->pickup_location->latitude;
                            $pickupLocationLng = $order->type != "parcel" ? $order->vendor->longitude : $order->pickup_location->longitude;
                        }
                        $driverDistanceToPickup = $this->getDistance(
                            [
                                $pickupLocationLat,
                                $pickupLocationLng
                            ],
                            [
                                $driverData["lat"],
                                $driverData["long"],
                            ]
                        );
                logger("11");

                        if(!empty($order->taxi_order)) {
                            $pickup = [
                                'lat' => $pickupLocationLat,
                                'long' => $pickupLocationLng,
                                'address' => $order->taxi_order->pickup_address,
                                'city' => "",
                                'state' => "",
                                'country' => "",
                                "distance" => number_format($driverDistanceToPickup, 2),
                            ];
                            //dropoff data
                            
                            $dropoffLocationLat = $order->taxi_order->dropoff_latitude;
                            $dropoffLocationLng = $order->taxi_order->dropoff_longitude;
                        } else {
                            $pickup = [
                                'lat' => $pickupLocationLat,
                                'long' => $pickupLocationLng,
                                'address' => $order->type != "parcel" ? $order->vendor->address : $order->pickup_location->address,
                                'city' => $order->type != "parcel" ? "" : $order->pickup_location->city,
                                'state' => $order->type != "parcel" ? "" : $order->pickup_location->state ?? "",
                                'country' => $order->type != "parcel" ? "" : $order->pickup_location->country ?? "",
                                "distance" => number_format($driverDistanceToPickup, 2),
                            ];
                logger("12");


                            //dropoff data
                            $dropoffLocationLat = $order->type != "parcel" ? $order->delivery_address->latitude : $order->dropoff_location->latitude;
                            $dropoffLocationLng = $order->type != "parcel" ? $order->delivery_address->longitude : $order->dropoff_location->longitude;
                        }
                        $driverDistanceToDropoff = $this->getDistance(
                            [
                                $dropoffLocationLat,
                                $dropoffLocationLng
                            ],
                            [
                                $driverData["lat"],
                                $driverData["long"],
                            ]
                        );
                        if(!empty($order->taxi_order)) {
                        
                            $dropoff = [
                                'lat' => $dropoffLocationLat,
                                'long' => $dropoffLocationLng,
                                'address' => $order->taxi_order->dropoff_address,
                                'city' =>  "",
                                'state' =>  "",
                                'country' => "",
                                "distance" => number_format($driverDistanceToDropoff, 2),
                            ];
                        } else {

                            $dropoff = [
                                'lat' => $dropoffLocationLat,
                                'long' => $dropoffLocationLng,
                                'address' => $order->type != "parcel" ? $order->delivery_address->address : $order->dropoff_location->address,
                                'city' =>  $order->type != "parcel" ? "" : $order->dropoff_location->city,
                                'state' => $order->type != "parcel" ? "" : $order->pickup_location->state ?? "",
                                'country' => $order->type != "parcel" ? "" : $order->pickup_location->country ?? "",
                                "distance" => number_format($driverDistanceToDropoff, 2),
                            ];
                        }
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
                            'range' => $order->vendor ? (string)$order->vendor->delivery_range : "",
                            "notificationTime" => setting('alertDuration', 15),
                        ];



                        //send the new order to driver via push notification
                        $autoAssignmentSerivce = new AutoAssignmentService();
                        $autoAssignmentSerivce->saveNewOrderToFirebaseFirestore(
                            $driver,
                            $newOrderData,
                            $pickup["address"],
                            $driverDistanceToPickup
                        );
                        // $autoAssignmentSerivce->sendNewOrderNotification($driver,$newOrderData,$pickup["address"],$driverDistanceToPickup);
                    }
                }
            } catch (\Exception $ex) {
                logger("Skipping Order", [$order->id]);
                logger("Order Error AssignOrder ", [$ex->getMessage() ?? '']);
            }
        }
    }


    public function isDriverFar($lat1, $long1, $lat2, $long2, $order = null)
    {
        //check the distance between this driver and pickup location
        $geopointA = new GeoPoint($lat1, $long1);
        $geopointB = new GeoPoint($lat2, $long2);
        $driverToPickupDistance = $geopointA->distanceTo($geopointB, 'kilometers');
        $actualSearchRadius = driverSearchRadius($order);
        return $driverToPickupDistance > $actualSearchRadius;
    }

    //
    public function getDistance($loc1, $loc2)
    {
        $geopointA = new GeoPoint($loc1[0], $loc1[1]);
        $geopointB = new GeoPoint($loc2[0], $loc2[1]);
        return $geopointA->distanceTo($geopointB, 'kilometers');
    }
}
