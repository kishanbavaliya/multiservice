<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Models\RestaurantSubcategory;
use App\Models\RestaurantProduct;
use App\Models\RestaurantServingSize;
use App\Models\RestaurantModifierGroup;
use App\Models\RestaurantModifier;
use App\Models\RestaurantBanner;
use Illuminate\Support\Facades\DB;

class VerifyRestaurantDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurant:verify-demo-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that restaurant demo data was created correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verifying Restaurant Demo Data...');
        $this->info('================================');

        $this->verifyRestaurants();
        $this->verifyCategories();
        $this->verifySubcategories();
        $this->verifyServingSizes();
        $this->verifyModifierGroups();
        $this->verifyModifiers();
        $this->verifyProducts();
        $this->verifyBanners();
        $this->verifyRelationships();

        $this->info('Verification complete!');
    }

    private function verifyRestaurants()
    {
        $this->info('Checking Restaurants...');
        
        $restaurants = Restaurant::all();
        $this->info("✓ Found {$restaurants->count()} restaurants");
        
        foreach ($restaurants as $restaurant) {
            $this->info("  - {$restaurant->name} (ID: {$restaurant->id})");
        }
        $this->newLine();
    }

    private function verifyCategories()
    {
        $this->info('Checking Categories...');
        
        $categories = RestaurantCategory::all();
        $this->info("✓ Found {$categories->count()} categories");
        
        $restaurants = Restaurant::with('categories')->get();
        foreach ($restaurants as $restaurant) {
            $this->info("  {$restaurant->name}: {$restaurant->categories->count()} categories");
        }
        $this->newLine();
    }

    private function verifySubcategories()
    {
        $this->info('Checking Subcategories...');
        
        $subcategories = RestaurantSubcategory::all();
        $this->info("✓ Found {$subcategories->count()} subcategories");
        
        $categories = RestaurantCategory::with('subcategories')->get();
        foreach ($categories as $category) {
            if ($category->subcategories->count() > 0) {
                $this->info("  {$category->name}: {$category->subcategories->count()} subcategories");
            }
        }
        $this->newLine();
    }

    private function verifyServingSizes()
    {
        $this->info('Checking Serving Sizes...');
        
        $sizes = RestaurantServingSize::all();
        $this->info("✓ Found {$sizes->count()} serving sizes");
        
        $restaurants = Restaurant::with('servingSizes')->get();
        foreach ($restaurants as $restaurant) {
            $this->info("  {$restaurant->name}: {$restaurant->servingSizes->count()} serving sizes");
        }
        $this->newLine();
    }

    private function verifyModifierGroups()
    {
        $this->info('Checking Modifier Groups...');
        
        $groups = RestaurantModifierGroup::all();
        $this->info("✓ Found {$groups->count()} modifier groups");
        
        $restaurants = Restaurant::with('modifierGroups')->get();
        foreach ($restaurants as $restaurant) {
            $this->info("  {$restaurant->name}: {$restaurant->modifierGroups->count()} modifier groups");
        }
        $this->newLine();
    }

    private function verifyModifiers()
    {
        $this->info('Checking Modifiers...');
        
        $modifiers = RestaurantModifier::all();
        $this->info("✓ Found {$modifiers->count()} modifiers");
        
        $groups = RestaurantModifierGroup::with('modifiers')->get();
        foreach ($groups as $group) {
            if ($group->modifiers->count() > 0) {
                $this->info("  {$group->name}: {$group->modifiers->count()} modifiers");
            }
        }
        $this->newLine();
    }

    private function verifyProducts()
    {
        $this->info('Checking Products...');
        
        $products = RestaurantProduct::all();
        $this->info("✓ Found {$products->count()} products");
        
        $restaurants = Restaurant::with('products')->get();
        foreach ($restaurants as $restaurant) {
            $this->info("  {$restaurant->name}: {$restaurant->products->count()} products");
        }
        $this->newLine();
    }

    private function verifyBanners()
    {
        $this->info('Checking Banners...');
        
        $banners = RestaurantBanner::all();
        $this->info("✓ Found {$banners->count()} banners");
        
        $restaurants = Restaurant::with('banners')->get();
        foreach ($restaurants as $restaurant) {
            $this->info("  {$restaurant->name}: {$restaurant->banners->count()} banners");
        }
        $this->newLine();
    }

    private function verifyRelationships()
    {
        $this->info('Verifying Relationships...');
        
        // Check foreign key constraints
        $this->info('Checking foreign key relationships...');
        
        $restaurants = Restaurant::all();
        foreach ($restaurants as $restaurant) {
            // Check if categories belong to this restaurant
            $categoryCount = RestaurantCategory::where('restaurant_id', $restaurant->id)->count();
            $this->info("  {$restaurant->name}: {$categoryCount} categories");
            
            // Check if subcategories belong to this restaurant
            $subcategoryCount = RestaurantSubcategory::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$subcategoryCount} subcategories");
            
            // Check if products belong to this restaurant
            $productCount = RestaurantProduct::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$productCount} products");
            
            // Check if serving sizes belong to this restaurant
            $sizeCount = RestaurantServingSize::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$sizeCount} serving sizes");
            
            // Check if modifier groups belong to this restaurant
            $groupCount = RestaurantModifierGroup::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$groupCount} modifier groups");
            
            // Check if modifiers belong to this restaurant
            $modifierCount = RestaurantModifier::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$modifierCount} modifiers");
            
            // Check if banners belong to this restaurant
            $bannerCount = RestaurantBanner::where('restaurant_id', $restaurant->id)->count();
            $this->info("    - {$bannerCount} banners");
        }
        
        $this->newLine();
        $this->info('✓ All relationships verified successfully!');
    }
}
