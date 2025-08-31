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

class ViewRestaurantDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurant:view-demo-data {--restaurant-id= : Show data for specific restaurant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View all restaurant demo data in a formatted table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $restaurantId = $this->option('restaurant-id');
        
        if ($restaurantId) {
            $restaurant = Restaurant::find($restaurantId);
            if (!$restaurant) {
                $this->error("Restaurant with ID {$restaurantId} not found.");
                return 1;
            }
            $this->showRestaurantDetails($restaurant);
        } else {
            $this->showAllRestaurants();
        }

        return 0;
    }

    private function showAllRestaurants()
    {
        $this->info('Restaurant Demo Data Overview');
        $this->info('=============================');

        $restaurants = Restaurant::all();
        
        if ($restaurants->isEmpty()) {
            $this->warn('No restaurants found. Please run the demo seeder first.');
            return;
        }

        $tableData = [];
        foreach ($restaurants as $restaurant) {
            $tableData[] = [
                'ID' => $restaurant->id,
                'Name' => $restaurant->name,
                'Cuisine' => $restaurant->cuisine_type,
                'Categories' => $restaurant->categories()->count(),
                'Products' => $restaurant->products()->count(),
                'Serving Sizes' => $restaurant->servingSizes()->count(),
                'Modifier Groups' => $restaurant->modifierGroups()->count(),
                'Banners' => $restaurant->banners()->count(),
                'Status' => $restaurant->status,
                'Rating' => $restaurant->rating,
            ];
        }

        $this->table([
            'ID', 'Name', 'Cuisine', 'Categories', 'Products', 'Serving Sizes', 'Modifier Groups', 'Banners', 'Status', 'Rating'
        ], $tableData);

        $this->newLine();
        $this->info('Use --restaurant-id=X to see detailed information for a specific restaurant.');
    }

    private function showRestaurantDetails(Restaurant $restaurant)
    {
        $this->info("Detailed Information for: {$restaurant->name}");
        $this->info(str_repeat('=', strlen($restaurant->name) + 30));

        // Basic restaurant info
        $this->info('Restaurant Details:');
        $this->info("- Address: {$restaurant->address}, {$restaurant->city}, {$restaurant->state}");
        $this->info("- Phone: {$restaurant->phone}");
        $this->info("- Email: {$restaurant->email}");
        $this->info("- Delivery Fee: \${$restaurant->delivery_fee}");
        $this->info("- Minimum Order: \${$restaurant->minimum_order}");
        $this->info("- Rating: {$restaurant->rating}/5 ({$restaurant->total_reviews} reviews)");
        $this->newLine();

        // Categories
        $this->info('Categories:');
        $categories = $restaurant->categories()->with('subcategories')->get();
        foreach ($categories as $category) {
            $this->info("  • {$category->name}");
            foreach ($category->subcategories as $subcategory) {
                $this->info("    - {$subcategory->name}");
            }
        }
        $this->newLine();

        // Products
        $this->info('Products:');
        $products = $restaurant->products()->with('category')->get();
        foreach ($products as $product) {
            $categoryName = $product->category ? $product->category->name : 'N/A';
            $this->info("  • {$product->name} (\${$product->price}) - {$categoryName}");
        }
        $this->newLine();

        // Serving Sizes
        $this->info('Serving Sizes:');
        $sizes = $restaurant->servingSizes()->get();
        foreach ($sizes as $size) {
            $status = $size->status ? 'Active' : 'Inactive';
            $this->info("  • {$size->name}: {$status}");
        }
        $this->newLine();

        // Modifier Groups
        $this->info('Modifier Groups:');
        $groups = $restaurant->modifierGroups()->with('modifiers')->get();
        foreach ($groups as $group) {
            $selectionType = ucfirst($group->selection_type);
            $this->info("  • {$group->name} ({$selectionType})");
            foreach ($group->modifiers as $modifier) {
                $status = $modifier->status ? 'Active' : 'Inactive';
                $this->info("    - {$modifier->name}: {$status}");
            }
        }
        $this->newLine();

        // Banners
        $this->info('Banners:');
        $banners = $restaurant->banners()->get();
        foreach ($banners as $banner) {
            $positionLabel = $this->getPositionLabel($banner->position);
            $this->info("  • {$banner->title} ({$banner->banner_type}) - {$positionLabel}");
        }
    }

    private function getPositionLabel($position)
    {
        $positions = [
            1 => 'Top',
            2 => 'Middle',
            3 => 'Bottom',
            4 => 'Left Sidebar',
            5 => 'Right Sidebar',
            6 => 'Full Width',
        ];
        
        return $positions[$position] ?? "Position {$position}";
    }
}
