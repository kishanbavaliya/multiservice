<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantServingSize;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RestaurantServingSizeController extends Controller
{
    /**
     * Display a listing of serving sizes with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RestaurantServingSize::with(['restaurant']);

            // Apply filters
            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('type')) {
                if ($request->type === 'global') {
                    $query->whereNull('restaurant_id');
                } elseif ($request->type === 'restaurant') {
                    $query->whereNotNull('restaurant_id');
                }
            }

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $servingSizes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $servingSizes,
                'message' => 'Serving sizes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving serving sizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created serving size
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'nullable|exists:restaurants,id',
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'status' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for unique name per restaurant (or global)
            $existingServingSize = RestaurantServingSize::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->first();

            if ($existingServingSize) {
                return response()->json([
                    'success' => false,
                    'message' => 'A serving size with this name already exists for this restaurant'
                ], 422);
            }

            $servingSize = RestaurantServingSize::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $servingSize->load('restaurant'),
                'message' => 'Serving size created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating serving size: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified serving size
     */
    public function show($id): JsonResponse
    {
        try {
            $servingSize = RestaurantServingSize::with(['restaurant'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $servingSize,
                'message' => 'Serving size retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Serving size not found'
            ], 404);
        }
    }

    /**
     * Update the specified serving size
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $servingSize = RestaurantServingSize::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'nullable|exists:restaurants,id',
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'status' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for unique name per restaurant (or global), excluding current record
            $existingServingSize = RestaurantServingSize::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->where('id', '!=', $id)
                ->first();

            if ($existingServingSize) {
                return response()->json([
                    'success' => false,
                    'message' => 'A serving size with this name already exists for this restaurant'
                ], 422);
            }

            $servingSize->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $servingSize->load('restaurant'),
                'message' => 'Serving size updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating serving size: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified serving size
     */
    public function destroy($id): JsonResponse
    {
        try {
            $servingSize = RestaurantServingSize::findOrFail($id);

            if (!$servingSize->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete serving size. It is being used by products.'
                ], 422);
            }

            $servingSize->delete();

            return response()->json([
                'success' => true,
                'message' => 'Serving size deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting serving size: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get serving sizes by restaurant
     */
    public function getByRestaurant($restaurantId): JsonResponse
    {
        try {
            $servingSizes = RestaurantServingSize::byRestaurant($restaurantId)
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $servingSizes,
                'message' => 'Restaurant serving sizes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving restaurant serving sizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get global serving sizes
     */
    public function getGlobal(): JsonResponse
    {
        try {
            $servingSizes = RestaurantServingSize::global()
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $servingSizes,
                'message' => 'Global serving sizes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving global serving sizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available serving sizes for a restaurant (including global)
     */
    public function getAvailableForRestaurant($restaurantId): JsonResponse
    {
        try {
            $globalServingSizes = RestaurantServingSize::global()->active()->ordered()->get();
            $restaurantServingSizes = RestaurantServingSize::byRestaurant($restaurantId)->active()->ordered()->get();

            $allServingSizes = $globalServingSizes->merge($restaurantServingSizes);

            return response()->json([
                'success' => true,
                'data' => $allServingSizes,
                'message' => 'Available serving sizes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available serving sizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle serving size status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $servingSize = RestaurantServingSize::findOrFail($id);
            $servingSize->update(['status' => !$servingSize->status]);

            return response()->json([
                'success' => true,
                'data' => $servingSize->load('restaurant'),
                'message' => 'Serving size status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating serving size status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search serving sizes
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'restaurant_id' => 'nullable|exists:restaurants,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = RestaurantServingSize::with(['restaurant']);

            if ($request->restaurant_id) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            $servingSizes = $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->query . '%')
                  ->orWhere('description', 'like', '%' . $request->query . '%');
            })
            ->active()
            ->ordered()
            ->limit(10)
            ->get();

            return response()->json([
                'success' => true,
                'data' => $servingSizes,
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching serving sizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get serving size statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => RestaurantServingSize::count(),
                'active' => RestaurantServingSize::active()->count(),
                'inactive' => RestaurantServingSize::where('status', false)->count(),
                'global' => RestaurantServingSize::global()->count(),
                'restaurant_specific' => RestaurantServingSize::whereNotNull('restaurant_id')->count(),
                'unused' => RestaurantServingSize::whereDoesntHave('products')->count(),
                'in_use' => RestaurantServingSize::whereHas('products')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}

