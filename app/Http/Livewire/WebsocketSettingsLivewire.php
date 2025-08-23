<?php

namespace App\Http\Livewire;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class WebsocketSettingsLivewire extends BaseLivewireComponent
{

    public function render()
    {
        return view('livewire.settings.websocket');
    }



    public function regenerateKeys()
    {
        // Generate new values
        $newAppId = rand(100000, 999999);
        $newAppKey = Str::random(20);
        $newAppSecret = Str::random(20);

        //
        setEnv("REVERB_APP_ID", $newAppId);
        setEnv("REVERB_APP_KEY", $newAppKey);
        setEnv("REVERB_APP_SECRET", $newAppSecret);

        // Clear config cache so Laravel picks up the new values
        Artisan::call('config:clear');
        try {
            Artisan::call('queue:restart');
        } catch (\Exception $ex) {
            logger("Error restart service: Queue", [$ex]);
        }
        try {
            Artisan::call('reverb:restart');
        } catch (\Exception $ex) {
            logger("Error restart service: Reverb", [$ex]);
        }
        //
        $this->emit("reloadPage");
    }
}