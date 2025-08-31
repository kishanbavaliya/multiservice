<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUserRole;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CompleteRestaurantDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting complete restaurant demo setup...');

        // First, ensure we have the restaurant-manager role
        $this->ensureRestaurantManagerRole();

        // Run the demo data seeder
        $this->call(RestaurantDemoDataSeeder::class);

        // Create restaurant managers for each restaurant
        $this->createRestaurantManagers();

        $this->command->info('Complete restaurant demo setup finished!');
        $this->command->info('You can now log in with any of the restaurant manager accounts.');
    }

    private function ensureRestaurantManagerRole()
    {
        $restaurantManagerRole = Role::where('name', 'restaurant-manager')->first();
        
        if (!$restaurantManagerRole) {
            $this->command->error('Restaurant manager role not found. Please run PermissionsTableSeeder first.');
            $this->command->info('Running: php artisan db:seed --class=PermissionsTableSeeder');
            $this->call(PermissionsTableSeeder::class);
        }
    }

    private function createRestaurantManagers()
    {
        $this->command->info('Creating restaurant managers...');

        $restaurants = Restaurant::all();
        
        if ($restaurants->isEmpty()) {
            $this->command->warn('No restaurants found. Demo data seeder may have failed.');
            return;
        }

        $restaurantManagerRole = Role::where('name', 'restaurant-manager')->first();

        foreach ($restaurants as $restaurant) {
            $this->command->info("Creating manager for: {$restaurant->name}");

            // Create restaurant manager user
            $manager = User::create([
                'name' => $restaurant->name . ' Manager',
                'email' => 'manager.' . strtolower(str_replace(' ', '', $restaurant->name)) . '@demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign the restaurant-manager role
            $manager->assignRole($restaurantManagerRole);

            // Create the restaurant user role relationship
            RestaurantUserRole::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $manager->id,
                'role' => 'manager',
                'is_active' => true,
                'permissions' => json_encode([
                    'view-restaurant',
                    'view-restaurant-categories',
                    'view-restaurant-subcategories',
                    'view-restaurant-serving-sizes',
                    'view-restaurant-modifiers',
                    'view-restaurant-modifier-groups',
                    'view-restaurant-products',
                    'view-restaurant-banners',
                    'manage-assigned-restaurant',
                ]),
            ]);

            // Update the restaurant with the assigned manager
            $restaurant->update([
                'assigned_manager_id' => $manager->id,
            ]);

            $this->command->info("✓ Manager created: {$manager->email} (password: password)");
        }

        $this->command->newLine();
        $this->command->info('Restaurant Manager Login Credentials:');
        $this->command->info('====================================');
        
        foreach ($restaurants as $restaurant) {
            $manager = $restaurant->assignedManager;
            if ($manager) {
                $this->command->info("{$restaurant->name}: {$manager->email} / password");
            }
        }
    }
}
