<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUserRole;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RestaurantManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the restaurant-manager role
        $restaurantManagerRole = Role::where('name', 'restaurant-manager')->first();
        
        if (!$restaurantManagerRole) {
            $this->command->error('Restaurant manager role not found. Please run PermissionsTableSeeder first.');
            return;
        }

        // Get all restaurants
        $restaurants = Restaurant::all();
        
        if ($restaurants->isEmpty()) {
            $this->command->error('No restaurants found. Please create restaurants first.');
            return;
        }

        foreach ($restaurants as $restaurant) {
            // Create a restaurant manager user
            $manager = User::create([
                'name' => $restaurant->name . ' Manager',
                'email' => 'manager.' . strtolower(str_replace(' ', '', $restaurant->name)) . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign the restaurant-manager role
            $manager->assignRole($restaurantManagerRole);

            // Create the restaurant user role relationship
            // RestaurantUserRole::create([
            //     'restaurant_id' => $restaurant->id,
            //     'user_id' => $manager->id,
            //     'role' => 'manager',
            //     'is_active' => true,
            //     'permissions' => json_encode([
            //         'view-restaurant',
            //         'view-restaurant-categories',
            //         'view-restaurant-subcategories',
            //         'view-restaurant-serving-sizes',
            //         'view-restaurant-modifiers',
            //         'view-restaurant-modifier-groups',
            //         'view-restaurant-products',
            //         'view-restaurant-banners',
            //         'manage-assigned-restaurant',
            //     ]),
            // ]);

            // Update the restaurant with the assigned manager
            $restaurant->update([
                'assigned_manager_id' => $manager->id,
            ]);

            $this->command->info("Created restaurant manager for {$restaurant->name}: {$manager->email}");
        }
    }
}
