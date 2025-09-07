<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\RestaurantProduct;

class RestaurantController extends Controller
{
    /**
     * Get all restaurants with pagination, filtering, and sorting
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Restaurant::with(['categories', 'products']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by featured status
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Filter by cuisine type
            if ($request->has('cuisine_type') && !empty($request->cuisine_type)) {
                $query->where('cuisine_type', 'like', "%{$request->cuisine_type}%");
            }

            // Filter by city
            if ($request->has('city') && !empty($request->city)) {
                $query->where('city', 'like', "%{$request->city}%");
            }

            // Filter by delivery availability
            if ($request->has('delivery_available')) {
                $query->where('delivery_available', $request->boolean('delivery_available'));
            }

            // Filter by pickup availability
            if ($request->has('pickup_available')) {
                $query->where('pickup_available', $request->boolean('pickup_available'));
            }

            // Filter by minimum rating
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }

            // Filter by maximum delivery time
            if ($request->has('max_delivery_time')) {
                $query->where('max_delivery_time', '<=', $request->max_delivery_time);
            }

            // Search by name, description, or cuisine type
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('cuisine_type', 'like', "%{$search}%");
                });
            }

            // Sort results
            $sortBy = $request->get('sort_by', 'rating');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            // Validate sort fields
            $allowedSortFields = ['name', 'rating', 'created_at', 'delivery_fee', 'min_delivery_time'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'rating';
            }
            
            $query->orderBy($sortBy, $sortDirection);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $restaurants = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $restaurants,
                'message' => 'Restaurants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve restaurants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top trending restaurants based on rating, reviews, and featured status
     */
    public function getTopTrendingRestaurants(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $city = $request->get('city');
            $cuisine_type = $request->get('cuisine_type');

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true)
                ->select([
                    'id', 'name', 'description', 'cuisine_type', 'city', 'state',
                    'delivery_fee', 'min_delivery_time', 'max_delivery_time',
                    'delivery_available', 'pickup_available', 'rating', 'total_reviews',
                    'logo_url', 'banner_url', 'is_featured'
                ])
                ->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }]);

            // Filter by city if provided
            if ($city) {
                $query->where('city', 'like', "%{$city}%");
            }

            // Filter by cuisine type if provided
            if ($cuisine_type) {
                $query->where('cuisine_type', 'like', "%{$cuisine_type}%");
            }

            // Calculate trending score based on rating, reviews, and featured status
            $restaurants = $query->get()
                ->map(function ($restaurant) {
                    // Trending score = (rating * 15) + (total_reviews * 0.1) + (featured bonus)
                    $featuredBonus = $restaurant->is_featured ? 10 : 0;
                    $trendingScore = ($restaurant->rating * 15) + 
                                   ($restaurant->total_reviews * 0.1) + 
                                   $featuredBonus;
                    $restaurant->trending_score = round($trendingScore, 2);
                    return $restaurant;
                })
                ->sortByDesc('trending_score')
                ->take($limit)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $restaurants,
                'message' => 'Top trending restaurants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trending restaurants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get best sellers restaurants based on rating and reviews
     */
    public function getBestSellersRestaurants(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $city = $request->get('city');
            $cuisine_type = $request->get('cuisine_type');
            $timeframe = $request->get('timeframe', 'all'); // all, week, month

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true)
                ->where('rating', '>', 0)
                ->select([
                    'id', 'name', 'description', 'cuisine_type', 'city', 'state',
                    'delivery_fee', 'min_delivery_time', 'max_delivery_time',
                    'delivery_available', 'pickup_available', 'rating', 'total_reviews',
                    'logo_url', 'banner_url', 'is_featured'
                ])
                ->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }]);

            // Filter by city if provided
            if ($city) {
                $query->where('city', 'like', "%{$city}%");
            }

            // Filter by cuisine type if provided
            if ($cuisine_type) {
                $query->where('cuisine_type', 'like', "%{$cuisine_type}%");
            }

            // Apply timeframe filter if specified
            if ($timeframe !== 'all') {
                $date = now();
                switch ($timeframe) {
                    case 'week':
                        $date = $date->subWeek();
                        break;
                    case 'month':
                        $date = $date->subMonth();
                        break;
                }
                $query->where('updated_at', '>=', $date);
            }

            // Get restaurants and calculate best seller score
            $restaurants = $query->get()
                ->map(function ($restaurant) {
                    // Best seller score = (rating * 0.7) + (total_reviews * 0.3)
                    $bestSellerScore = ($restaurant->rating * 0.7) + ($restaurant->total_reviews * 0.3);
                    $restaurant->best_seller_score = round($bestSellerScore, 2);
                    return $restaurant;
                })
                ->sortByDesc('best_seller_score')
                ->take($limit)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $restaurants,
                'message' => 'Best sellers restaurants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve best sellers restaurants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get nearby restaurants based on coordinates and radius
     */
    public function getNearbyRestaurants(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0.1|max:50', // in kilometers
                'limit' => 'nullable|integer|min:1|max:100',
                'cuisine_type' => 'nullable|string',
                'delivery_available' => 'nullable|boolean',
                'min_rating' => 'nullable|numeric|min:0|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->get('radius', 5); // Default 5km radius
            $limit = $request->get('limit', 20);

            // Calculate distance using Haversine formula
            $distanceFormula = "
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance
            ";

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true)
                ->select([
                    'id', 'name', 'description', 'cuisine_type', 'address', 'city', 'state',
                    'latitude', 'longitude', 'delivery_fee', 'min_delivery_time', 'max_delivery_time',
                    'delivery_available', 'pickup_available', 'rating', 'total_reviews',
                    'logo_url', 'banner_url', 'is_featured'
                ])
                ->selectRaw($distanceFormula, [$latitude, $longitude, $latitude])
                ->having('distance', '<=', $radius)
                ->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }]);

            // Filter by cuisine type if provided
            if ($request->has('cuisine_type')) {
                $query->where('cuisine_type', 'like', "%{$request->cuisine_type}%");
            }

            // Filter by delivery availability if provided
            if ($request->has('delivery_available')) {
                $query->where('delivery_available', $request->boolean('delivery_available'));
            }

            // Filter by minimum rating if provided
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }

            // Order by distance and rating
            $restaurants = $query->orderBy('distance', 'asc')
                ->orderBy('rating', 'desc')
                ->limit($limit)
                ->get();

            // Add additional distance information
            $restaurants->each(function ($restaurant) {
                $restaurant->distance_km = round($restaurant->distance, 2);
                $restaurant->distance_miles = round($restaurant->distance * 0.621371, 2);
                unset($restaurant->distance);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'restaurants' => $restaurants,
                    'search_location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'radius_km' => $radius
                    ],
                    'total_found' => $restaurants->count()
                ],
                'message' => 'Nearby restaurants retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve nearby restaurants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurant by ID with detailed information
     */
    public function show($id): JsonResponse
    {
        try {
            $restaurant = Restaurant::with([
            'categories' => function ($query) {
                $query->where('is_active', true)
                      ->with([
                          'subcategories' => function ($subQuery) {
                              $subQuery->where('is_active', true)
                                       ->with([
                                           'products' => function ($productQuery) {
                                               $productQuery->where('is_available', true);
                                           }
                                       ]);
                          },
                          'products' => function ($productQuery) {
                              $productQuery->where('is_available', true);
                          }
                      ]);
            },
                'servingSizes' => function ($query) {
                    $query->where('status', true);
                },
                'modifierGroups' => function ($query) {
                    $query->where('status', true);
                },
                'banners' => function ($query) {
                    $query->where('is_active', true);
                },
                'offer' => function ($query) {
                    $query->where('active', 1);
                }
            ])->findOrFail($id);

            // Note: view_count column doesn't exist in current schema
            // Consider adding this column for analytics if needed

            return response()->json([
                'success' => true,
                'data' => $restaurant,
                'message' => 'Restaurant retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
                'data' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Search restaurants with advanced filters
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'city' => 'nullable|string',
                'cuisine_type' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive,suspended',
                'delivery_available' => 'nullable|boolean',
                'min_rating' => 'nullable|numeric|min:0|max:5',
                'max_delivery_time' => 'nullable|integer|min:1',
                'price_range' => 'nullable|string|in:low,medium,high',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true);

            // Search by name, description, cuisine type, or city
            $searchQuery = $request->query;
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('cuisine_type', 'like', "%{$searchQuery}%")
                  ->orWhere('city', 'like', "%{$searchQuery}%");
            });

            // Apply filters
            if ($request->has('city')) {
                $query->where('city', 'like', "%{$request->city}%");
            }

            if ($request->has('cuisine_type')) {
                $query->where('cuisine_type', 'like', "%{$request->cuisine_type}%");
            }

            if ($request->has('delivery_available')) {
                $query->where('delivery_available', $request->boolean('delivery_available'));
            }

            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }

            if ($request->has('max_delivery_time')) {
                $query->where('max_delivery_time', '<=', $request->max_delivery_time);
            }

            // Price range filter
            if ($request->has('price_range')) {
                switch ($request->price_range) {
                    case 'low':
                        $query->where('delivery_fee', '<=', 2.99);
                        break;
                    case 'medium':
                        $query->whereBetween('delivery_fee', [3.00, 5.99]);
                        break;
                    case 'high':
                        $query->where('delivery_fee', '>=', 6.00);
                        break;
                }
            }

            $limit = $request->get('limit', 20);
            $restaurants = $query->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }])
                ->orderBy('rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'restaurants' => $restaurants,
                    'total_found' => $restaurants->count(),
                    'search_query' => $request->query
                ],
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurant statistics and analytics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_restaurants' => Restaurant::count(),
                'active_restaurants' => Restaurant::where('status', 'active')->count(),
                'verified_restaurants' => Restaurant::where('is_verified', true)->count(),
                'featured_restaurants' => Restaurant::where('is_featured', true)->count(),
                'delivery_available' => Restaurant::where('delivery_available', true)->count(),
                'pickup_available' => Restaurant::where('pickup_available', true)->count(),
                'top_cuisine_types' => Restaurant::select('cuisine_type', DB::raw('count(*) as count'))
                    ->groupBy('cuisine_type')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'top_cities' => Restaurant::select('city', DB::raw('count(*) as count'))
                    ->groupBy('city')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'rating_distribution' => [
                    '5_star' => Restaurant::where('rating', '>=', 4.5)->count(),
                    '4_star' => Restaurant::whereBetween('rating', [4.0, 4.49])->count(),
                    '3_star' => Restaurant::whereBetween('rating', [3.0, 3.99])->count(),
                    '2_star' => Restaurant::whereBetween('rating', [2.0, 2.99])->count(),
                    '1_star' => Restaurant::whereBetween('rating', [1.0, 1.99])->count(),
                    'unrated' => Restaurant::where('rating', 0)->count()
                ],
                'recent_additions' => Restaurant::latest()->take(5)->get(['id', 'name', 'city', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Restaurant statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve restaurant statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurants by cuisine type
     */
    public function getByCuisineType($cuisineType, Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $city = $request->get('city');
            $minRating = $request->get('min_rating');

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true)
                ->where('cuisine_type', 'like', "%{$cuisineType}%")
                ->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }]);

            if ($city) {
                $query->where('city', 'like', "%{$city}%");
            }

            if ($minRating) {
                $query->where('rating', '>=', $minRating);
            }

            $restaurants = $query->orderBy('rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'cuisine_type' => $cuisineType,
                    'restaurants' => $restaurants,
                    'total_found' => $restaurants->count()
                ],
                'message' => "Restaurants with cuisine type '{$cuisineType}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve restaurants by cuisine type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurants by city
     */
    public function getByCity($city, Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $cuisineType = $request->get('cuisine_type');
            $minRating = $request->get('min_rating');
            $sortBy = $request->get('sort_by', 'rating');

            $query = Restaurant::where('status', 'active')
                ->where('is_verified', true)
                ->where('city', 'like', "%{$city}%")
                ->with(['categories' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                }]);

            if ($cuisineType) {
                $query->where('cuisine_type', 'like', "%{$cuisineType}%");
            }

            if ($minRating) {
                $query->where('rating', '>=', $minRating);
            }

            // Apply sorting
            switch ($sortBy) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'delivery_fee':
                    $query->orderBy('delivery_fee', 'asc');
                    break;
                case 'min_delivery_time':
                    $query->orderBy('min_delivery_time', 'asc');
                    break;
                case 'total_reviews':
                    $query->orderBy('total_reviews', 'desc');
                    break;
                default:
                    $query->orderBy('rating', 'desc');
            }

            $restaurants = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'city' => $city,
                    'restaurants' => $restaurants,
                    'total_found' => $restaurants->count(),
                    'sort_by' => $sortBy
                ],
                'message' => "Restaurants in '{$city}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve restaurants by city',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Search restaurants with advanced filters
     */
    public function allSearch(Request $request): JsonResponse
    {
        try {
            // ========== 0. Validation ==========
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'city' => 'nullable|string',
                'cuisine_type' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive,suspended',
                'delivery_available' => 'nullable|boolean',
                'min_rating' => 'nullable|numeric|min:0|max:5',
                'max_delivery_time' => 'nullable|integer|min:1',
                'price_range' => 'nullable|string|in:low,medium,high',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ========== 1. Get request parameters ==========
            $searchQuery = $request['query']; // required, safe to use array syntax
            $limit = $request->has('limit') ? $request['limit'] : 20;

            $city = $request->has('city') ? $request['city'] : null;
            $cuisine_type = $request->has('cuisine_type') ? $request['cuisine_type'] : null;
            $delivery_available = $request->has('delivery_available') ? $request->boolean('delivery_available') : null;
            $min_rating = $request->has('min_rating') ? $request['min_rating'] : null;
            $max_delivery_time = $request->has('max_delivery_time') ? $request['max_delivery_time'] : null;
            $price_range = $request->has('price_range') ? $request['price_range'] : null;
            // dd($searchQuery);
            // ========== 2. Search Restaurants ==========
            $restaurantQuery = Restaurant::where('status', 'active')
                ->where('is_verified', 1)
                ->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('description', 'like', "%{$searchQuery}%")
                    ->orWhere('cuisine_type', 'like', "%{$searchQuery}%")
                    ->orWhere('city', 'like', "%{$searchQuery}%");
                });
     // dd($restaurantQuery->get());
            // Apply filters if present
            if ($city) {
                $restaurantQuery->where('city', 'like', "%{$city}%");
            }

            if ($cuisine_type) {
                $restaurantQuery->where('cuisine_type', 'like', "%{$cuisine_type}%");
            }

            if (!is_null($delivery_available)) {
                $restaurantQuery->where('delivery_available', $delivery_available);
            }

            if (!is_null($min_rating)) {
                $restaurantQuery->where('rating', '>=', $min_rating);
            }

            if (!is_null($max_delivery_time)) {
                $restaurantQuery->where('max_delivery_time', '<=', $max_delivery_time);
            }

            if ($price_range) {
                switch ($price_range) {
                    case 'low':
                        $restaurantQuery->where('delivery_fee', '<=', 2.99);
                        break;
                    case 'medium':
                        $restaurantQuery->whereBetween('delivery_fee', [3.00, 5.99]);
                        break;
                    case 'high':
                        $restaurantQuery->where('delivery_fee', '>=', 6.00);
                        break;
                }
            }

            $restaurants = $restaurantQuery
                ->with(['categories' => function ($q) {
                    $q->where('is_active', true)->limit(3);
                }])
                ->orderBy('rating', 'desc')
                ->orderBy('total_reviews', 'desc')
                ->limit($limit)
                ->get();

            // ========== 3. Search Dishes ==========
            $dishes = RestaurantProduct::where('is_available', true)
                ->where('name', 'like', "%{$searchQuery}%")
                ->with(['restaurant' => function ($q) {
                    $q->select('id', 'name', 'rating', 'max_delivery_time');
                }])
                ->limit($limit)
                ->get();

            // ========== 4. Response ==========
            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => [
                    'restaurants' => $restaurants,
                    'dishes' => $dishes,
                    'total_restaurants' => $restaurants->count(),
                    'total_dishes' => $dishes->count(),
                    'search_query' => $searchQuery,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'delivery_available' => 'nullable|boolean',
                'free_delivery' => 'nullable|boolean',
                'min_discount' => 'nullable|integer|min:0|max:100',
                'offers' => 'nullable|boolean',
                'top_rated' => 'nullable|boolean',
                'price_level' => 'nullable|array',
                'price_level.*' => 'integer',
                'cuisine' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Restaurant::with('offers')->where('status', 'active')->where('is_verified', true);

            // Filter: delivery_available
            if ($request->has('delivery_available')) {
                $query->where('delivery_available', $request->boolean('delivery_available'));
            }

            // Filter: free_delivery
            if ($request->has('free_delivery')) {
                $query->where('delivery_fee', 0);
            }

            // Filter: offers
            if ($request->has('offers')) {
                $query->whereHas('offers', function ($q) {
                    // $q->where('is_active', true);
                });
            }

            // Filter: min_discount
            // if ($request->has('min_discount')) {
            //     $query->where('discount_percentage', '>=', $request->min_discount);
            // }
            if ($request->has('min_discount')) {
                $query->whereHas('offers', function ($q) use ($request) {
                    // $q->where('is_active', true)
                    $q->where('discount_value', '>=', $request->min_discount);
                });
            }

            // Filter: top_rated
            if ($request->has('top_rated') && $request->boolean('top_rated')) {
                $query->where('rating', '>=', 4.5);
            }

            // Filter: price_level (discount percentage)
            if ($request->has('price_level')) {
                $levels = is_array($request->price_level)
                    ? $request->price_level
                    : [$request->price_level];

                $query->whereHas('offers', function ($q) use ($levels) {
                    // $q->where('is_active', true)
                    $q->whereIn('discount_value', $levels);
                });
            }

            // Filter: cuisine
            if ($request->has('cuisine')) {
                $query->where('cuisine_type', 'like', "%{$request->cuisine}%");
            }

            $limit = $request->get('limit', 20);
            switch ($request->get('sort_by')) {
                case 'top_rated':
                    $query->orderBy('rating', 'desc');
                    break;

                case 'delivery_time':
                    $query->orderBy('max_delivery_time', 'asc'); // or 'delivery_time'
                    break;

                case 'cost_low_to_high':
                    $query->with(['offers' => function ($q) {
                        $q->orderBy('discount_value', 'asc');
                    }]);
                    break;

                case 'cost_high_to_low':
                    $query->with(['offers' => function ($q) {
                        $q->orderBy('discount_value', 'desc');
                    }]);
                    break;
                case 'most_popular':
                    $query->orderBy('rating', 'desc'); // Assuming you track popularity
                    break;

                case 'recommended':
                default:
                    $query->orderBy('rating', 'desc'); // Fallback or logic for "recommended"
                    break;
            }

            $restaurants = $query
                ->orderBy('rating', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'restaurants' => $restaurants,
                    'total' => $restaurants->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Filter failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPopularBrands()
    {
        $brands = Restaurant::orderBy('rating', 'desc')
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Popular brands fetched successfully',
            'data' => $brands
        ]);
    }

    /**
     * Create a new restaurant
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|string',
                'banner_url' => 'nullable|string',
                'cuisine_type' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'country' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'website' => 'nullable|url',
                'opening_hours' => 'nullable|array',
                'delivery_fee' => 'nullable|numeric',
                'minimum_order' => 'nullable|numeric',
                'min_delivery_time' => 'nullable|integer',
                'max_delivery_time' => 'nullable|integer',
                'delivery_available' => 'nullable|boolean',
                'pickup_available' => 'nullable|boolean',
                'delivery_radius' => 'nullable|numeric',
                'status' => 'nullable|in:active,inactive,suspended',
                'is_featured' => 'nullable|boolean',
                'is_verified' => 'nullable|boolean',
                'assigned_admin_id' => 'nullable|exists:users,id',
                'assigned_manager_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only((new Restaurant())->getFillable());

            // If a manager creates a restaurant, assign it to them
            if (Auth::user() && Auth::user()->hasRole('manager')) {
                $data['assigned_manager_id'] = Auth::id();
            }

            $restaurant = Restaurant::create($data);

            return response()->json([
                'success' => true,
                'data' => $restaurant,
                'message' => 'Restaurant created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create restaurant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing restaurant
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $restaurant = Restaurant::findOrFail($id);

            // Permission: only super_admin or users assigned to the restaurant can update
            if (!(Auth::user() && Auth::user()->hasRole('super_admin')) && !($restaurant->hasUser(Auth::id()))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|string',
                'banner_url' => 'nullable|string',
                'cuisine_type' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'country' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'website' => 'nullable|url',
                'opening_hours' => 'nullable|array',
                'delivery_fee' => 'nullable|numeric',
                'minimum_order' => 'nullable|numeric',
                'min_delivery_time' => 'nullable|integer',
                'max_delivery_time' => 'nullable|integer',
                'delivery_available' => 'nullable|boolean',
                'pickup_available' => 'nullable|boolean',
                'delivery_radius' => 'nullable|numeric',
                'status' => 'nullable|in:active,inactive,suspended',
                'is_featured' => 'nullable|boolean',
                'is_verified' => 'nullable|boolean',
                'assigned_admin_id' => 'nullable|exists:users,id',
                'assigned_manager_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only((new Restaurant())->getFillable());
            $restaurant->update($data);

            return response()->json([
                'success' => true,
                'data' => $restaurant,
                'message' => 'Restaurant updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update restaurant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a restaurant
     */
    public function destroy($id): JsonResponse
    {
        try {
            $restaurant = Restaurant::findOrFail($id);

            // Permission: only super_admin or users assigned to the restaurant can delete
            if (!(Auth::user() && Auth::user()->hasRole('super_admin')) && !($restaurant->hasUser(Auth::id()))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $restaurant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Restaurant deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete restaurant',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
