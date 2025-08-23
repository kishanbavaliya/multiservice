<?php

namespace App\Traits;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\DeliveryZonePoint;

trait GeoBoundaryCheckTrait
{
    use GoogleMapApiTrait;

    public function inAnyCityBoundary($lat, $lng, $stateId = null): ?City
    {
        $cLatLng = [
            'lat' => $lat,
            'lng' => $lng
        ];
        $foundCity = null;
        //get all Cities but in chunks of 500
        City::where('is_active', 1)->when($stateId, function ($query) use ($stateId) {
            return $query->where('state_id', $stateId);
        })
            ->whereNotNull('boundaries')
            ->chunk(50, function ($cities) use ($cLatLng, &$foundCity) {
                foreach ($cities as $city) {
                    $isMultiPolygon = $this->isMultiPolygon($city->boundaries);
                    if ($isMultiPolygon) {
                        $polygonPoints = $this->getMultiPolygon($city->boundaries);
                        foreach ($polygonPoints as $polygonPoint) {
                            if ($this->insideBound($cLatLng, $polygonPoint)) {
                                $foundCity = $city;
                                break;
                            }
                        }
                    } else {
                        $points = $this->formatBoundaries($city->boundaries);
                        if ($this->insideBound($cLatLng, $points)) {
                            $foundCity = $city;
                            break;
                        }
                    }

                    if ($foundCity != null) {
                        break;
                    }
                }
            });
        return $foundCity;
    }
    public function inAnyStateBoundary($lat, $lng, $countryId = null): ?State
    {

        $cLatLng = [
            'lat' => $lat,
            'lng' => $lng
        ];
        $foundState = null;
        //get all States but in chunks of 100
        State::where('is_active', 1)->when($countryId, function ($query) use ($countryId) {
            return $query->where('country_id', $countryId);
        })
            ->whereNotNull('boundaries')
            ->chunk(50, function ($states) use ($cLatLng, &$foundState) {
                foreach ($states as $state) {
                    $isMultiPolygon = $this->isMultiPolygon($state->boundaries);
                    if ($isMultiPolygon) {
                        $polygonPoints = $this->getMultiPolygon($state->boundaries);
                        foreach ($polygonPoints as $polygonPoint) {
                            if ($this->insideBound($cLatLng, $polygonPoint)) {
                                $foundState = $state;
                                break;
                            }
                        }
                    } else {
                        $points = $this->formatBoundaries($state->boundaries);
                        if ($this->insideBound($cLatLng, $points)) {
                            $foundState = $state;
                            break;
                        }
                    }

                    if ($foundState != null) {
                        break;
                    }
                }
            });
        return $foundState;
    }
    public function inAnyCountryBoundary($lat, $lng): ?Country
    {
        $cLatLng = [
            'lat' => $lat,
            'lng' => $lng
        ];
        $foundCountry = null;
        //get all countries but in chunks of 50
        Country::where('is_active', 1)->whereNotNull('boundaries')
            ->chunk(50, function ($countries) use ($cLatLng, &$foundCountry) {
                foreach ($countries as $country) {
                    $isMultiPolygon = $this->isMultiPolygon($country->boundaries);
                    if ($isMultiPolygon) {
                        $polygonPoints = $this->getMultiPolygon($country->boundaries);
                        foreach ($polygonPoints as $polygonPoint) {
                            if ($this->insideBound($cLatLng, $polygonPoint)) {
                                $foundCountry = $country;
                                break 2; // Break out of both loops
                            }
                        }
                    } else {
                        $points = $this->formatBoundaries($country->boundaries);
                        if ($this->insideBound($cLatLng, $points)) {
                            //how to return the country to the calling function
                            $foundCountry = $country;
                            break;
                        }
                    }

                    //
                    if ($foundCountry != null) {
                        break;
                    }
                }
            });
        return $foundCountry;
    }


    //misc
    public function formatBoundaries($boundaries)
    {
        $boundaries = json_decode($boundaries);
        if (!is_array($boundaries[0])) {
            return collect([]);
        }

        $boundaries = collect($boundaries)->map(function ($boundary) {
            $point = new DeliveryZonePoint();
            $point->lat = $boundary->lat ?? $boundary[0];
            $point->lng = $boundary->lng ?? $boundary[1];
            return $point;
        });
        return $boundaries;
    }

    public function isMultiPolygon($boundaries)
    {
        $boundaries = json_decode($boundaries);
        //check if the first element is an array
        return is_array($boundaries[0]);
    }

    public function getMultiPolygon($boundaries)
    {
        $boundaries = json_decode($boundaries);
        $multiPolygon = [];
        foreach ($boundaries as $boundary) {
            $boundary = json_encode($boundary);
            $multiPolygon[] = $this->formatBoundaries($boundary);
        }
        return $multiPolygon;
    }
}