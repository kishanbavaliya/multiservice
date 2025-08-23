<?php

namespace App\Http\Livewire;

use App\Models\NavMenu;

class MarketPlaceLivewire extends BaseLivewireComponent
{


    public function render()
    {
        return view('livewire.extensions.marketplace', [
            "menu" => NavMenu::get() ?? []
        ]);
    }
}