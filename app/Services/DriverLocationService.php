<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\DriverLocation;

class DriverLocationService
{
    protected function baseDriverQuery(float $latitude, float $longitude, float $radiusKm)
    {
        return DriverLocation::select(
            'driver_locations.*',
            DB::raw("(
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(driver_locations.lat)) *
                        cos(radians(driver_locations.lng) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(driver_locations.lat))
                    )
                ) AS distance")
        )
            ->addBinding([$latitude, $longitude, $latitude], 'select')
            ->with('driver')
            ->whereHas('driver', function ($query) {
                $query->where('is_online', true)
                    ->whereDoesntHave('currently_assigned_orders');
            })
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    public function getNearbyAvailableDrivers(float $latitude, float $longitude, float $radiusKm, int $limit = 10)
    {
        return $this->baseDriverQuery($latitude, $longitude, $radiusKm)
            ->limit($limit)
            ->get();
    }

    public function getNearbyTaxiDrivers(float $latitude, float $longitude, float $radiusKm, int $limit = 10, ?int $vehicleTypeId = null)
    {
        return $this->baseDriverQuery($latitude, $longitude, $radiusKm)
            ->whereHas('driver', function ($query) use ($vehicleTypeId) {
                $query->where(function ($q) use ($vehicleTypeId) {
                    // Case 1: driver_type.is_taxi = true
                    $q->whereHas('driver_type', function ($q2) {
                        $q2->where('is_taxi', true);
                    });

                    // Case 2: no driver_type but has vehicle (classic taxi detection)
                    $q->orWhere(function ($q2) use ($vehicleTypeId) {
                        $q2->whereDoesntHave('driver_type')
                            ->whereHas('vehicle');

                        if ($vehicleTypeId !== null) {
                            $q2->whereHas('vehicle', function ($q3) use ($vehicleTypeId) {
                                $q3->where('vehicle_type_id', $vehicleTypeId);
                            });
                        }
                    });

                    // Case 3: driver_type.is_taxi = true AND matches vehicle_type
                    if ($vehicleTypeId !== null) {
                        $q->whereHas('vehicle', function ($q3) use ($vehicleTypeId) {
                            $q3->where('vehicle_type_id', $vehicleTypeId);
                        });
                    }
                });
            })
            ->limit($limit)
            ->get();
    }


    public function getNearbyRegularDrivers(float $latitude, float $longitude, float $radiusKm, int $limit = 10)
    {
        return $this->baseDriverQuery($latitude, $longitude, $radiusKm)
            ->whereHas('driver', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('driver_type', function ($q2) {
                        $q2->where('is_taxi', false);
                    })->orWhere(function ($q2) {
                        $q2->whereDoesntHave('driver_type')
                            ->whereDoesntHave('vehicle');
                    });
                });
            })
            ->limit($limit)
            ->get();
    }
}