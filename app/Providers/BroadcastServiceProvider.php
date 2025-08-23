<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if (!\GeoSot\EnvEditor\Facades\EnvEditor::keyExists("REVERB_APP_ID")) {
            (new \App\Upgrades\Upgrade77())->addReverbValues();
        }

        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        require base_path('routes/channels.php');
    }
}