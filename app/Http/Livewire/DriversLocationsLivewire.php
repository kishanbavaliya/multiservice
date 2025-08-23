<?php

namespace App\Http\Livewire;

use App\Models\DriverLocation;

class DriversLocationsLivewire extends BaseLivewireComponent
{

    //
    public $model = DriverLocation::class;

    public function render()
    {
        return view('livewire.drivers_locations');
    }
}