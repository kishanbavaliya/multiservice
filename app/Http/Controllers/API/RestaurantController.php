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
                'name'              => 'required|string|max:255',
                'description'       => 'nullable|string',
                'logo_url'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
                'image_url'         => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
                'cuisine_type'      => 'nullable|string|max:255',
                'address'           => 'nullable|string',
                'latitude'          => 'nullable|numeric',
                'longitude'         => 'nullable|numeric',
                'delivery_fee'      => 'nullable|numeric',
                'min_delivery_time' => 'nullable|integer',
                'max_delivery_time' => 'nullable|integer',
                'rating'            => 'nullable|numeric|min:0|max:5',
                'offer_id'          => 'nullable|integer|exists:offers,id',
            ]);

            $data = $request->except(['logo_url', 'image_url']);

            // ✅ Handle Logo Upload (public/restaurants/logos)
            if ($request->hasFile('logo_url')) {
                $file     = $request->file('logo_url');
                $filename = time() . '_logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('restaurants/logos'), $filename);
                $data['logo_url'] = 'restaurants/logos/' . $filename;
            }

            // ✅ Handle Banner Upload (public/restaurants/banners)
            if ($request->hasFile('image_url')) {
                $file     = $request->file('image_url');
                $filename = time() . '_banner_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('restaurants/banners'), $filename);
                $data['image_url'] = 'restaurants/banners/' . $filename;
            }

            // ✅ Create new restaurant
            $restaurant = \App\Models\Restaurant::create($data);

            return response()->json([
                'status'  => true,
                'message' => 'Restaurant created successfully',
                'data'    => $restaurant
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }

    public function getRestaurantDetails($id)
    {
        try {
            // For now using static data
            $restaurant = [
                'id' => $id,
                'name' => 'Pizza Palace',
                'description' => 'Best wood-fired pizzas in town 🍕🔥',
                'address' => 'Deira (5.8 km)',
                'rating' => 4.4,
                'ratings_count' => '999+',
                'delivery_time' => '35 - 45 mins',
                'price_for_one' => '$$$',
                'delivery_fee' => 'AED 9.90',
                'offer' => [
                    'title' => '30% off',
                    'discount_value' => 30,
                    'discount_type' => 'percentage'
                ],
                'logo_url' => url('/restaurants/logos/1756493337_logo_68b1f619d43dd.png'),
                'image_url' => url('/restaurants/banners/1756493337_banner_68b1f619d4498.jpg'),

                // Menu categories
                'categories' => [
                    [
                        'id' => 1,
                        'name' => 'Bestsellers',
                        'items_count' => 4,
                        'items' => [
                            [
                                'id' => 101,
                                'name' => 'Margherita Pizza',
                                'description' => 'Classic cheese and tomato pizza with fresh basil.',
                                'price' => 17.50,
                                'original_price' => 25.00,
                                'discount' => '30%',
                                'image_url' => url('/menu/download5.jpg')
                            ],
                            [
                                'id' => 102,
                                'name' => 'Pepperoni Pizza',
                                'description' => 'Loaded with spicy pepperoni and mozzarella cheese.',
                                'price' => 1.50,
                                'original_price' => null,
                                'discount' => null,
                                'image_url' => url('/menu/download6.jpg')
                            ],
                        ]
                    ],
                    [
                        'id' => 2,
                        'name' => 'Veg Pizzas',
                        'items_count' => 23,
                        'items' => [
                            [
                                'id' => 201,
                                'name' => 'Farmhouse Pizza',
                                'description' => 'Loaded with fresh veggies – onion, capsicum, tomato, and olives.',
                                'price' => 12.73,
                                'original_price' => 18.13,
                                'discount' => '30%',
                                'image_url' => url('/menu/download7.jpg')
                            ],
                            [
                                'id' => 202,
                                'name' => 'Paneer Tikka Pizza',
                                'description' => 'Spicy paneer tikka with capsicum, onion, and cheese.',
                                'price' => 15.87,
                                'original_price' => 22.67,
                                'discount' => '30%',
                                'image_url' => url('/menu/download8.jpg')
                            ],
                        ]
                    ]
                ],

                // Example of customisation options (like Chicken Shawarma)
                'customizable_item' => [
                    'id' => 301,
                    'name' => 'Build Your Own Pizza',
                    'description' => 'Create your perfect pizza by choosing toppings.',
                    'base_price' => 15.00,
                    'image_url' => url('/menu/download9.jpg'),
                    'customizations' => [
                        'title' => 'Choose Your Toppings',
                        'max_options' => 5,
                        'options' => [
                            ['id' => 1, 'name' => 'Extra Cheese', 'price' => 2.00],
                            ['id' => 2, 'name' => 'Mushrooms', 'price' => 1.50],
                            ['id' => 3, 'name' => 'Pepperoni', 'price' => 2.50],
                            ['id' => 4, 'name' => 'Olives', 'price' => 1.25],
                            ['id' => 5, 'name' => 'Grilled Chicken', 'price' => 3.00],
                        ]
                    ]
                ]
            ];

            return response()->json([
                'status' => true,
                'message' => 'Restaurant details fetched successfully',
                'data' => $restaurant
            ], 200);

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

            // Add full URL for banner_image
            $offers->transform(function ($offer) {
                if ($offer->banner_image) {
                    $offer->banner_image = url($offer->banner_image);
                }
                return $offer;
            });

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


    public function createOffer(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'title'          => 'required|string|max:255',
                'description'    => 'nullable|string',
                'discount_type'  => 'required|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0',
                'max_discount'   => 'nullable|numeric|min:0',
                'banner_image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after_or_equal:start_date',
            ]);

            // Handle image upload if exists
            $imagePath = null;
            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('offers'), $filename);
                $imagePath = 'offers/' . $filename; // store relative path
            }

            // Create offer
            $offer = Offer::create([
                'title'          => $validated['title'],
                'description'    => $validated['description'] ?? null,
                'discount_type'  => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'max_discount'   => $validated['max_discount'] ?? null,
                'banner_image'   => $imagePath,
                'start_date'     => Carbon::parse($validated['start_date']),
                'end_date'       => Carbon::parse($validated['end_date']),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Offer created successfully',
                'data'    => $offer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data'    => []
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

