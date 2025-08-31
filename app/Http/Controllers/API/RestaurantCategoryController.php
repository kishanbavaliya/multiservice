<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RestaurantCategoryController extends Controller
{
    /**
     * Get all restaurant categories with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RestaurantCategory::with(['restaurant', 'subcategories']);

            // Filter by restaurant ID if provided
            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filter by featured status
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            // Search by name or description
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort results
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created restaurant category
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'required|exists:restaurants,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image_url' => 'nullable|url|max:500',
                'icon_url' => 'nullable|url|max:500',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = RestaurantCategory::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $category->load('restaurant'),
                'message' => 'Category created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified restaurant category
     */
    public function show($id): JsonResponse
    {
        try {
            $category = RestaurantCategory::with(['restaurant', 'subcategories'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Category retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }

    /**
     * Update the specified restaurant category
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $category = RestaurantCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'image_url' => 'nullable|url|max:500',
                'icon_url' => 'nullable|url|max:500',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $category->load('restaurant'),
                'message' => 'Category updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified restaurant category
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = RestaurantCategory::findOrFail($id);

            // Check if category has subcategories
            if ($category->subcategories()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing subcategories'
                ], 422);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing products'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories by restaurant ID
     */
    public function getByRestaurant($restaurantId): JsonResponse
    {
        try {
            $categories = RestaurantCategory::where('restaurant_id', $restaurantId)
                ->where('is_active', true)
                ->with(['subcategories' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('sort_order', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Restaurant categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve restaurant categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category active status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $category = RestaurantCategory::findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'is_active' => $category->is_active,
                    'updated_at' => $category->updated_at
                ],
                'message' => 'Category status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category featured status
     */
    public function toggleFeatured($id): JsonResponse
    {
        try {
            $category = RestaurantCategory::findOrFail($id);
            $category->is_featured = !$category->is_featured;
            $category->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'is_featured' => $category->is_featured,
                    'updated_at' => $category->updated_at
                ],
                'message' => 'Category featured status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category featured status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search categories
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'restaurant_id' => 'nullable|exists:restaurants,id',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = RestaurantCategory::with(['restaurant', 'subcategories']);

            // Search by name or description
            $searchQuery = $request->query;
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });

            // Filter by restaurant ID if provided
            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $categories = $query->limit(20)->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
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
     * Get category statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_categories' => RestaurantCategory::count(),
                'active_categories' => RestaurantCategory::where('is_active', true)->count(),
                'featured_categories' => RestaurantCategory::where('is_featured', true)->count(),
                'categories_with_subcategories' => RestaurantCategory::has('subcategories')->count(),
                'categories_with_products' => RestaurantCategory::has('products')->count(),
                'recent_categories' => RestaurantCategory::latest()->take(5)->get(['id', 'name', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Category statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
