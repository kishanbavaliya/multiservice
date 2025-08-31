<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if restaurants already exist
        if (Restaurant::count() > 0) {
            $this->command->info('Restaurants already exist. Skipping seeder.');
            return;
        }

        $restaurants = [
            [
                'name' => 'Pizza Palace',
                'description' => 'Delicious Italian pizzas and pasta',
                'cuisine_type' => 'Italian',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'phone' => '+1-555-0123',
                'email' => 'info@pizzapalace.com',
                'status' => 'active',
                'is_featured' => true,
                'is_verified' => true,
                'delivery_fee' => 5.00,
                'minimum_order' => 15.00,
                'min_delivery_time' => 30,
                'max_delivery_time' => 60,
            ],
            [
                'name' => 'Burger House',
                'description' => 'Juicy burgers and fries',
                'cuisine_type' => 'American',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '90210',
                'phone' => '+1-555-0456',
                'email' => 'info@burgerhouse.com',
                'status' => 'active',
                'is_featured' => false,
                'is_verified' => true,
                'delivery_fee' => 3.50,
                'minimum_order' => 12.00,
                'min_delivery_time' => 25,
                'max_delivery_time' => 45,
            ],
            [
                'name' => 'Sushi Express',
                'description' => 'Fresh sushi and Japanese cuisine',
                'cuisine_type' => 'Japanese',
                'address' => '789 Pine Street',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'postal_code' => '60601',
                'phone' => '+1-555-0789',
                'email' => 'info@sushiexpress.com',
                'status' => 'active',
                'is_featured' => true,
                'is_verified' => true,
                'delivery_fee' => 6.00,
                'minimum_order' => 20.00,
                'min_delivery_time' => 35,
                'max_delivery_time' => 55,
            ],
            [
                'name' => 'Taco Fiesta',
                'description' => 'Authentic Mexican tacos and burritos',
                'cuisine_type' => 'Mexican',
                'address' => '321 Elm Street',
                'city' => 'Miami',
                'state' => 'FL',
                'country' => 'USA',
                'postal_code' => '33101',
                'phone' => '+1-555-0321',
                'email' => 'info@tacofiesta.com',
                'status' => 'active',
                'is_featured' => false,
                'is_verified' => false,
                'delivery_fee' => 4.50,
                'minimum_order' => 10.00,
                'min_delivery_time' => 20,
                'max_delivery_time' => 40,
            ],
            [
                'name' => 'Curry Corner',
                'description' => 'Spicy Indian curries and naan bread',
                'cuisine_type' => 'Indian',
                'address' => '654 Maple Drive',
                'city' => 'Seattle',
                'state' => 'WA',
                'country' => 'USA',
                'postal_code' => '98101',
                'phone' => '+1-555-0654',
                'email' => 'info@currycorner.com',
                'status' => 'active',
                'is_featured' => false,
                'is_verified' => true,
                'delivery_fee' => 5.50,
                'minimum_order' => 18.00,
                'min_delivery_time' => 30,
                'max_delivery_time' => 50,
            ],
        ];

        foreach ($restaurants as $restaurantData) {
            Restaurant::create($restaurantData);
        }

        $this->command->info('Created ' . count($restaurants) . ' restaurants successfully!');
    }
}
