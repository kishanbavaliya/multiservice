<?php

namespace App\Upgrades;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class Upgrade76 extends BaseUpgrade
{

    public $versionName = "1.7.60";
    //Runs or migrations to be done on this version
    public function run()
    {
        //
    }
}