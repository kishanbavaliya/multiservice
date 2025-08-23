<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class FoodAppController extends Controller
{
    /**
     * 📱 Screen 1: Get all categories
     * GET /api/categories
     */
    public function getCategories()
    {
        try {
            $categories = NewCategory::select('id', 'name','item_count')->get();
    
            if ($categories->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No categories found.',
                    'data'    => []
                ], 404);
            }
    
            return response()->json([
                'status'  => true,
                'message' => 'Categories fetched successfully.',
                'data'    => $categories
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }


/**
 * 📱 Screen 2: Get restaurants by category
 * GET /api/categories/{id}/restaurants
 */
public function getRestaurantsByCategory($id)
{
    try {
        $restaurants = Restaurant::whereHas('menuItems', function ($q) use ($id) {
            $q->where('new_categories_id', $id);
        })
        ->with('offer')
        ->get();

        if ($restaurants->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No restaurants found for this category.',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Restaurants fetched successfully.',
            'data'    => $restaurants
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error fetching restaurants: ' . $e->getMessage(),
            'data'    => []
        ], 500);
    }
}

/**
 * 📱 Screen 3: Get menu for a restaurant
 * GET /api/restaurants/{id}/menu
 */
public function getRestaurantMenu($id)
{
    try {
        $menu = MenuItem::where('restaurant_id', $id)
            ->with([
                'new_category',
                'customizations.options'
            ])
            ->get();

        if ($menu->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No menu items found for this restaurant.',
                'data'    => []
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Menu fetched successfully.',
            'data'    => $menu
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error fetching menu: ' . $e->getMessage(),
            'data'    => []
        ], 500);
    }
}

/**
 * 📱 Screen 4: Get single product details
 * GET /api/menu-items/{id}
 */
public function getMenuItemDetails($id)
{
    try {
        $item = MenuItem::with([
            'new_category',
            'customizations.options'
        ])->find($id);

        if (!$item) {
            return response()->json([
                'status'  => false,
                'message' => 'Menu item not found.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Menu item details fetched successfully.',
            'data'    => $item
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Error fetching menu item: ' . $e->getMessage(),
            'data'    => null
        ], 500);
    }
}

}
