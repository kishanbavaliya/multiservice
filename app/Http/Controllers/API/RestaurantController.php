<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                    $query->where('is_active', true);
                },
                'subcategories' => function ($query) {
                    $query->where('is_active', true);
                },
                'products' => function ($query) {
                    $query->where('is_available', true)
                          ->where('is_active', true);
                },
                'servingSizes' => function ($query) {
                    $query->where('status', true);
                },
                'modifierGroups' => function ($query) {
                    $query->where('status', true);
                },
                'banners' => function ($query) {
                    $query->where('is_active', true);
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
                'message' => 'Restaurant not found'
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
}
