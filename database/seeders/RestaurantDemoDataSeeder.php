<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Models\RestaurantSubcategory;
use App\Models\RestaurantServingSize;
use App\Models\RestaurantModifier;
use App\Models\RestaurantModifierGroup;
use App\Models\RestaurantProduct;
use App\Models\RestaurantBanner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RestaurantDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            // Disable foreign key checks for MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('restaurant_modifier_group_modifier')->truncate(); // pivot table
        // Truncate all restaurant-related tables
        RestaurantBanner::truncate();
        RestaurantProduct::truncate();
        RestaurantModifier::truncate();
        RestaurantModifierGroup::truncate();
        RestaurantServingSize::truncate();
        RestaurantSubcategory::truncate();
        RestaurantCategory::truncate();
        Restaurant::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Old data cleared!');
        $this->command->info('Creating restaurant demo data...');

        // Create restaurants
        $restaurants = $this->createRestaurants();
        
        foreach ($restaurants as $restaurant) {
            $this->command->info("Creating data for restaurant: {$restaurant->name}");
            
            // Create categories for this restaurant
            $categories = $this->createCategories($restaurant);
            
            // Create subcategories for each category
            foreach ($categories as $category) {
                $this->createSubcategories($category);
            }
            
            // Create serving sizes
            $this->createServingSizes($restaurant);
            
            // Create modifier groups
            $modifierGroups = $this->createModifierGroups($restaurant);
            
            // Create modifiers for each group
            foreach ($modifierGroups as $group) {
                $this->createModifiers($group);
            }
            
            // Create products
            $this->createProducts($restaurant, collect($categories));
            
            // Create banners
            $this->createBanners($restaurant);
        }

        $this->command->info('Restaurant demo data created successfully!');
    }

    private function createRestaurants()
    {
        $restaurants = [
            [
                'name' => 'Pizza Palace',
                'description' => 'Authentic Italian pizza with fresh ingredients and traditional recipes',
                'cuisine_type' => 'Italian',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'latitude' => 40.7505,
                'longitude' => -73.9934,
                'phone' => '+1-555-0123',
                'email' => 'info@pizzapalace.com',
                'website' => 'https://pizzapalace.com',
                'delivery_fee' => 2.99,
                'minimum_order' => 15.00,
                'min_delivery_time' => 25,
                'max_delivery_time' => 45,
                'delivery_available' => true,
                'pickup_available' => true,
                'delivery_radius' => 5.0,
                'status' => 'active',
                'is_featured' => true,
                'is_verified' => true,
                'rating' => 4.5,
                'total_reviews' => 127,
            ],
            [
                'name' => 'Burger House',
                'description' => 'Gourmet burgers made with premium beef and fresh ingredients',
                'cuisine_type' => 'American',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '90210',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'phone' => '+1-555-0456',
                'email' => 'info@burgerhouse.com',
                'website' => 'https://burgerhouse.com',
                'delivery_fee' => 1.99,
                'minimum_order' => 12.00,
                'min_delivery_time' => 20,
                'max_delivery_time' => 35,
                'delivery_available' => true,
                'pickup_available' => true,
                'delivery_radius' => 4.0,
                'status' => 'active',
                'is_featured' => true,
                'is_verified' => true,
                'rating' => 4.3,
                'total_reviews' => 89,
            ],
            [
                'name' => 'Sushi Express',
                'description' => 'Fresh sushi and Japanese cuisine prepared by expert chefs',
                'cuisine_type' => 'Japanese',
                'address' => '789 Pine Street',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'postal_code' => '60601',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'phone' => '+1-555-0789',
                'email' => 'info@sushiexpress.com',
                'website' => 'https://sushiexpress.com',
                'delivery_fee' => 3.99,
                'minimum_order' => 20.00,
                'min_delivery_time' => 30,
                'max_delivery_time' => 50,
                'delivery_available' => true,
                'pickup_available' => true,
                'delivery_radius' => 6.0,
                'status' => 'active',
                'is_featured' => false,
                'is_verified' => true,
                'rating' => 4.7,
                'total_reviews' => 156,
            ],
        ];

        $createdRestaurants = [];
        foreach ($restaurants as $restaurantData) {
            $restaurant = Restaurant::create($restaurantData);
            $createdRestaurants[] = $restaurant;
        }

        return $createdRestaurants;
    }

    private function createCategories(Restaurant $restaurant)
    {
        $categories = [
            [
                'name' => 'Appetizers',
                'description' => 'Start your meal with our delicious appetizers',
                'image_url' => null,
                'icon_url' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Main Courses',
                'description' => 'Our signature main dishes',
                'image_url' => null,
                'icon_url' => null,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet endings to your meal',
                'image_url' => null,
                'icon_url' => null,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Refreshing drinks and beverages',
                'image_url' => null,
                'icon_url' => null,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $categoryData['restaurant_id'] = $restaurant->id;
            $category = RestaurantCategory::create($categoryData);
            $createdCategories[] = $category;
        }

        return $createdCategories;
    }

    private function createSubcategories(RestaurantCategory $category)
    {
        $subcategories = [];
        
        switch ($category->name) {
            case 'Appetizers':
                $subcategories = [
                    ['name' => 'Hot Appetizers', 'description' => 'Warm and crispy appetizers', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 1],
                    ['name' => 'Cold Appetizers', 'description' => 'Fresh and chilled appetizers', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 2],
                ];
                break;
            case 'Main Courses':
                $subcategories = [
                    ['name' => 'Pasta', 'description' => 'Italian pasta dishes', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 1],
                    ['name' => 'Pizza', 'description' => 'Traditional Italian pizzas', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 2],
                    ['name' => 'Burgers', 'description' => 'Gourmet burger selections', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 3],
                    ['name' => 'Sushi Rolls', 'description' => 'Fresh sushi creations', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 4],
                ];
                break;
            case 'Desserts':
                $subcategories = [
                    ['name' => 'Cakes', 'description' => 'Delicious cakes and pastries', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 1],
                    ['name' => 'Ice Cream', 'description' => 'Premium ice cream selections', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 2],
                ];
                break;
            case 'Beverages':
                $subcategories = [
                    ['name' => 'Soft Drinks', 'description' => 'Refreshing soft drinks', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 1],
                    ['name' => 'Hot Drinks', 'description' => 'Warm beverages', 'image_url' => null, 'icon_url' => null, 'is_active' => true, 'is_featured' => false, 'sort_order' => 2],
                ];
                break;
        }

        foreach ($subcategories as $subcategoryData) {
            $subcategoryData['restaurant_id'] = $category->restaurant_id;
            $subcategoryData['category_id'] = $category->id;
            RestaurantSubcategory::create($subcategoryData);
        }
    }

    private function createServingSizes(Restaurant $restaurant)
    {
        $sizes = [
            ['name' => 'Small', 'description' => 'Perfect for light eaters', 'status' => true],
            ['name' => 'Medium', 'description' => 'Standard serving size', 'status' => true],
            ['name' => 'Large', 'description' => 'Generous portion size', 'status' => true],
            ['name' => 'Family Size', 'description' => 'Feeds 4-6 people', 'status' => true],
        ];

        foreach ($sizes as $sizeData) {
            $sizeData['restaurant_id'] = $restaurant->id;
            RestaurantServingSize::create($sizeData);
        }
    }

    private function createModifierGroups(Restaurant $restaurant)
    {
        $groups = [
            [
                'name' => 'Pizza Toppings',
                'selection_type' => 'optional',
                'required_count' => null,
                'status' => true,
            ],
            [
                'name' => 'Burger Add-ons',
                'selection_type' => 'optional',
                'required_count' => null,
                'status' => true,
            ],
            [
                'name' => 'Sauce Selection',
                'selection_type' => 'optional',
                'required_count' => null,
                'status' => true,
            ],
            [
                'name' => 'Side Options',
                'selection_type' => 'optional',
                'required_count' => null,
                'status' => true,
            ],
        ];

        $createdGroups = [];
        foreach ($groups as $groupData) {
            $groupData['restaurant_id'] = $restaurant->id;
            $group = RestaurantModifierGroup::create($groupData);
            $createdGroups[] = $group;
        }

        return $createdGroups;
    }

    private function createModifiers(RestaurantModifierGroup $group)
    {
        $modifiers = [];
        
        switch ($group->name) {
            case 'Pizza Toppings':
                $modifiers = [
                    ['name' => 'Pepperoni', 'description' => 'Spicy pepperoni slices', 'status' => true],
                    ['name' => 'Mushrooms', 'description' => 'Fresh mushrooms', 'status' => true],
                    ['name' => 'Bell Peppers', 'description' => 'Colorful bell peppers', 'status' => true],
                    ['name' => 'Extra Cheese', 'description' => 'Additional mozzarella cheese', 'status' => true],
                ];
                break;
            case 'Burger Add-ons':
                $modifiers = [
                    ['name' => 'Bacon', 'description' => 'Crispy bacon strips', 'status' => true],
                    ['name' => 'Avocado', 'description' => 'Fresh avocado slices', 'status' => true],
                    ['name' => 'Onion Rings-1', 'description' => 'Crispy onion rings', 'status' => true],
                ];
                break;
            case 'Sauce Selection':
                $modifiers = [
                    ['name' => 'BBQ Sauce', 'description' => 'Sweet and tangy BBQ sauce', 'status' => true],
                    ['name' => 'Ranch Dressing', 'description' => 'Creamy ranch dressing', 'status' => true],
                    ['name' => 'Hot Sauce', 'description' => 'Spicy hot sauce', 'status' => true],
                ];
                break;
            case 'Side Options':
                $modifiers = [
                    ['name' => 'French Fries', 'description' => 'Crispy golden fries', 'status' => true],
                    ['name' => 'Onion Rings-2', 'description' => 'Breaded onion rings', 'status' => true],
                    ['name' => 'Coleslaw', 'description' => 'Fresh cabbage coleslaw', 'status' => true],
                ];
                break;
        }

        foreach ($modifiers as $modifierData) {
            $modifierData['restaurant_id'] = $group->restaurant_id;
            $modifier = RestaurantModifier::create($modifierData);
            
            // Attach modifier to the group with pivot data
            $group->modifiers()->attach($modifier->id, ['sort_order' => $modifierData['status'] ? 1 : 0]);
        }
    }

    private function createProducts(Restaurant $restaurant, $categories)
    {
        $products = [
            [
                'name' => 'Margherita Pizza',
                'description' => 'Classic pizza with tomato sauce, mozzarella, and basil',
                'category_id' => $categories->where('name', 'Main Courses')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Pizza')->first()->id ?? null,
                'price' => 14.99,
                'original_price' => 14.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'Tomato sauce, mozzarella cheese, fresh basil, olive oil',
                'allergens' => 'Dairy, Gluten',
                'preparation_time' => 20,
                'calories' => 285,
                'dietary_info' => 'Vegetarian',
                'is_available' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => true,
                'customization_options' => json_encode(['extra cheese', 'extra toppings']),
                'sort_order' => 1,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.5,
                'total_reviews' => 12,
            ],
            [
                'name' => 'Classic Cheeseburger',
                'description' => 'Juicy beef patty with cheese, lettuce, tomato, and special sauce',
                'category_id' => $categories->where('name', 'Main Courses')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Burgers')->first()->id ?? null,
                'price' => 12.99,
                'original_price' => 12.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'Beef patty, cheddar cheese, lettuce, tomato, onion, special sauce, bun',
                'allergens' => 'Dairy, Gluten, Beef',
                'preparation_time' => 15,
                'calories' => 650,
                'dietary_info' => 'Contains meat',
                'is_available' => true,
                'is_featured' => true,
                'is_popular' => true,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => true,
                'customization_options' => json_encode(['extra cheese', 'bacon', 'avocado']),
                'sort_order' => 2,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.3,
                'total_reviews' => 8,
            ],
            [
                'name' => 'California Roll',
                'description' => 'Fresh avocado, cucumber, and crab with rice and nori',
                'category_id' => $categories->where('name', 'Main Courses')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Sushi Rolls')->first()->id ?? null,
                'price' => 8.99,
                'original_price' => 8.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'Sushi rice, nori, avocado, cucumber, imitation crab, sesame seeds',
                'allergens' => 'Fish, Soy',
                'preparation_time' => 10,
                'calories' => 180,
                'dietary_info' => 'Contains seafood',
                'is_available' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => false,
                'customization_options' => null,
                'sort_order' => 3,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.7,
                'total_reviews' => 15,
            ],
            [
                'name' => 'Garlic Bread',
                'description' => 'Toasted bread with garlic butter and herbs',
                'category_id' => $categories->where('name', 'Appetizers')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Hot Appetizers')->first()->id ?? null,
                'price' => 4.99,
                'original_price' => 4.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'French bread, garlic butter, herbs, olive oil',
                'allergens' => 'Gluten, Dairy',
                'preparation_time' => 8,
                'calories' => 120,
                'dietary_info' => 'Vegetarian',
                'is_available' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => false,
                'customization_options' => null,
                'sort_order' => 4,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.2,
                'total_reviews' => 6,
            ],
            [
                'name' => 'Chocolate Lava Cake',
                'description' => 'Warm chocolate cake with molten center, served with vanilla ice cream',
                'category_id' => $categories->where('name', 'Desserts')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Cakes')->first()->id ?? null,
                'price' => 6.99,
                'original_price' => 6.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'Dark chocolate, butter, eggs, flour, sugar, vanilla ice cream',
                'allergens' => 'Dairy, Gluten, Eggs',
                'preparation_time' => 12,
                'calories' => 320,
                'dietary_info' => 'Vegetarian',
                'is_available' => true,
                'is_featured' => true,
                'is_popular' => false,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => false,
                'customization_options' => null,
                'sort_order' => 5,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.8,
                'total_reviews' => 9,
            ],
            [
                'name' => 'Fresh Lemonade',
                'description' => 'Homemade lemonade with fresh lemons and natural sweetener',
                'category_id' => $categories->where('name', 'Beverages')->first()->id,
                'subcategory_id' => $restaurant->subcategories()->where('name', 'Soft Drinks')->first()->id ?? null,
                'price' => 3.99,
                'original_price' => 3.99,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'ingredients' => 'Fresh lemons, water, natural sweetener, ice',
                'allergens' => 'None',
                'preparation_time' => 3,
                'calories' => 90,
                'dietary_info' => 'Vegan, Gluten-free',
                'is_available' => true,
                'is_featured' => false,
                'is_popular' => false,
                'is_recommended' => false,
                'stock_quantity' => null,
                'track_stock' => false,
                'allow_out_of_stock_orders' => true,
                'allow_customization' => false,
                'customization_options' => null,
                'sort_order' => 6,
                'view_count' => 0,
                'order_count' => 0,
                'rating' => 4.1,
                'total_reviews' => 4,
            ],
        ];

        foreach ($products as $productData) {
            $productData['restaurant_id'] = $restaurant->id;
            RestaurantProduct::create($productData);
        }
    }

    private function createBanners(Restaurant $restaurant)
    {
        $banners = [
            [
                'title' => 'Welcome to ' . $restaurant->name,
                'description' => 'Delicious food delivered to your door. Order now and get 20% off your first order!',
                'image_url' => "dummy.png",
                'banner_type' => 'homepage',
                'position' => 1,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'link_url' => null,
                'target_blank' => false,
                'sort_order' => 1,
                'click_count' => 0,
                'impression_count' => 0,
            ],
            [
                'title' => 'Special Offers',
                'description' => 'Check out our latest promotions and discounts',
                'image_url' => 'dummy.png',
                'banner_type' => 'offers',
                'position' => 2,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'link_url' => null,
                'target_blank' => false,
                'sort_order' => 2,
                'click_count' => 0,
                'impression_count' => 0,
            ],
            [
                'title' => 'New Menu Items',
                'description' => 'Try our latest culinary creations',
                'image_url' => 'dummy.png',
                'banner_type' => 'featured',
                'position' => 3,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'link_url' => null,
                'target_blank' => false,
                'sort_order' => 3,
                'click_count' => 0,
                'impression_count' => 0,
            ],
        ];

        foreach ($banners as $bannerData) {
            $bannerData['restaurant_id'] = $restaurant->id;
            RestaurantBanner::create($bannerData);
        }
    }
}
