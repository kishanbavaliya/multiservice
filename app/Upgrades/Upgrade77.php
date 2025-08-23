<?php

namespace App\Upgrades;

use App\Models\SmsGateway;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
//
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use GeoSot\EnvEditor\Facades\EnvEditor;

class Upgrade77 extends BaseUpgrade
{

    public $versionName = "1.7.70";
    //Runs or migrations to be done on this version
    public function run()
    {
        //migrate: product_timings table
        $tableExists = Schema::hasTable('driver_locations');
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_03_04_132226_create_driver_locations_table.php",
                '--force' => true,
            ]);
        }


        //
        if (!Schema::hasColumn('users', 'two_factor_secret')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2014_10_12_200000_add_two_factor_columns_to_users_table.php",
                '--force' => true,
            ]);
        }
        //
        if (!Schema::hasTable('brands')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_04_08_231742_create_brands_table.php",
                '--force' => true,
            ]);
        }

        //add brand_id to products
        if (!Schema::hasColumn('products', 'brand_id')) {
            Schema::table('products', function ($table) {
                $table->foreignId('brand_id')->nullable()->after('id')->constrained()->nullOnDelete()->after('approved');
            });
        }

        //seed ARKESEL sms gateway
        $smsGateway = SmsGateway::where('slug', "arkesel")->first();
        if (empty($smsGateway)) {
            \DB::table('sms_gateways')->insert(array(
                0 =>
                array(
                    'name' => 'Arkesel',
                    'slug' => 'arkesel',
                    'is_active' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ),
            ));
        }


        //missiong permissions
        //add permision to admin role
        $permissions = [
            "view-product-brands",
            "manage-product-brands",
            "track-drivers-location",
            "view-taxi-orders",
        ];
        foreach ($permissions as $permissionName) {
            $permission = Permission::findOrCreate($permissionName, 'web');
            //add permision to admin role
            $adminRole = Role::findOrCreate('admin');
            $adminRole->givePermissionTo($permission);
        }

        //websocket values
        $this->addReverbValues();
    }


    public function addReverbValues()
    {

        if (!EnvEditor::keyExists("REVERB_APP_ID")) {
            EnvEditor::addKey("REVERB_APP_ID", rand(111111, 999999));
            EnvEditor::addKey("REVERB_APP_KEY", \Str::random(12));
            EnvEditor::addKey("REVERB_APP_SECRET", \Str::random(18));
            EnvEditor::addKey("REVERB_SERVER_HOST", "127.0.0.1");
            EnvEditor::addKey("REVERB_SERVER_PORT", "6001");
            $appUrl = env("APP_URL");
            $appUrl = preg_replace('/^https?:\/\//', '', $appUrl);
            EnvEditor::addKey("REVERB_HOST", $appUrl);
            EnvEditor::addKey("REVERB_PORT", "6001");
            EnvEditor::addKey("REVERB_SCHEME", "wss");

            //
            EnvEditor::addKey("VITE_REVERB_APP_KEY", "\"\${REVERB_APP_KEY}\"");
            EnvEditor::addKey("VITE_REVERB_HOST", "\"\${REVERB_HOST}\"");
            EnvEditor::addKey("VITE_REVERB_PORT", "\"\${REVERB_PORT}\"");
            EnvEditor::addKey("VITE_REVERB_SCHEME", "\"\${REVERB_SCHEME}\"");
        }

        if (!EnvEditor::keyExists("BROADCAST_DRIVER")) {
            EnvEditor::addKey("BROADCAST_DRIVER", "reverb");
        } else {
            EnvEditor::editKey("BROADCAST_DRIVER", "reverb");
        }
    }
}