<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\ItemCustomization;
use App\Models\MenuItem;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Http\Request;


class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::with([
            'offer',
            'menuItems.new_category',
            'menuItems.customizations.options'
        ])->get();

        return response()->json($restaurants);
    }
    
    public function storeRestaurant(Request $request)
    {   
        try {
            // ✅ Validation
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
                'banner_url' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
                'cuisine_type' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'delivery_fee' => 'nullable|numeric',
                'min_delivery_time' => 'nullable|integer',
                'max_delivery_time' => 'nullable|integer',
                'rating' => 'nullable|numeric|min:0|max:5',
                'offer_id' => 'nullable|integer|exists:offers,id',
            ]);
    
            $data = $request->except(['logo_url', 'banner_url']);
    
            // handle Logo Upload
            if ($request->hasFile('logo_url')) {
                $logoPath = $request->file('logo_url')->store('restaurants/logos', 'public');
                $data['logo_url'] = '/storage/' . $logoPath;
            }
    
            // handle Banner Upload
            if ($request->hasFile('banner_url')) {
                $bannerPath = $request->file('banner_url')->store('restaurants/banners', 'public');
                $data['banner_url'] = '/storage/' . $bannerPath;
            }
    
            // Create new restaurant
            $restaurant = \App\Models\Restaurant::create($data);
    
            return response()->json([
                'status' => true,
                'message' => 'Restaurant created successfully',
                'data' => $restaurant
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    
    public function getOffers()
    {
        try {
            $offers = Offer::where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->get();
    
            return response()->json([
                'status' => true,
                'message' => 'Offers fetched successfully',
                'data' => $offers
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    public function getTopTrendingRestaurants()
    {
        $restaurants = Restaurant::orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10) // limit to top 10
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Top trending restaurants fetched successfully',
            'data' => $restaurants
        ]);
    }

    
    public function getPopularBrands()
    {
        $brands = Restaurant::whereNotNull('offer_id')
            ->orderBy('rating', 'desc')
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Popular brands fetched successfully',
            'data' => $brands
        ]);
    }


    public function getBestSellers()
    {
        $restaurants = Restaurant::orderBy('rating', 'desc')
            ->orderBy('min_delivery_time', 'asc')
            ->take(10)
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Best sellers fetched successfully',
            'data' => $restaurants
        ]);
    }



}

