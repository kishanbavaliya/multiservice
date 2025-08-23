<?php

namespace App\Upgrades;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class Upgrade75 extends BaseUpgrade
{

    public $versionName = "1.7.51";
    //Runs or migrations to be done on this version
    public function run()
    {
        DB::statement('ALTER TABLE `favourites` CHANGE `product_id` `product_id` BIGINT UNSIGNED NULL;');

        if (!Schema::hasColumn('favourites', 'vendor_id')) {
            Schema::table('favourites', function ($table) {
                $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('cascade')->after('product_id');
            });
        }

        //banner_delivery_zone
        //run a migration for vendor settings
        $tableExists = Schema::hasTable('banner_delivery_zone');
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2024_10_10_013214_create_banner_delivery_zone_pivot_table.php",
                '--force' => true,
            ]);
        }
    }
}