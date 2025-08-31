<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantModifier;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RestaurantModifierController extends Controller
{
    /**
     * Display a listing of modifiers with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RestaurantModifier::with(['restaurant']);

            // Apply filters
            if ($request->has('restaurant_id') && $request->restaurant_id) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
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
            $modifiers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $modifiers,
                'message' => 'Modifiers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifiers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created modifier
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'restaurant_id' => 'required|exists:restaurants,id',
                'status' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for unique name within the same restaurant
            $existingModifier = RestaurantModifier::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->first();

            if ($existingModifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'A modifier with this name already exists for this restaurant'
                ], 422);
            }

            $modifier = RestaurantModifier::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $modifier->load('restaurant'),
                'message' => 'Modifier created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating modifier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified modifier
     */
    public function show($id): JsonResponse
    {
        try {
            $modifier = RestaurantModifier::with(['restaurant'])->find($id);

            if (!$modifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $modifier,
                'message' => 'Modifier retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified modifier
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $modifier = RestaurantModifier::find($id);

            if (!$modifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'restaurant_id' => 'required|exists:restaurants,id',
                'status' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for unique name within the same restaurant (excluding current modifier)
            $existingModifier = RestaurantModifier::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->where('id', '!=', $id)
                ->first();

            if ($existingModifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'A modifier with this name already exists for this restaurant'
                ], 422);
            }

            $modifier->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $modifier->load('restaurant'),
                'message' => 'Modifier updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating modifier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified modifier
     */
    public function destroy($id): JsonResponse
    {
        try {
            $modifier = RestaurantModifier::find($id);

            if (!$modifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier not found'
                ], 404);
            }

            if (!$modifier->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete modifier. It is being used by products.'
                ], 422);
            }

            $modifier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modifier deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting modifier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modifiers by restaurant
     */
    public function getByRestaurant($restaurantId): JsonResponse
    {
        try {
            $modifiers = RestaurantModifier::where('restaurant_id', $restaurantId)
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $modifiers,
                'message' => 'Restaurant modifiers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving restaurant modifiers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle modifier status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $modifier = RestaurantModifier::find($id);

            if (!$modifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier not found'
                ], 404);
            }

            $modifier->update(['status' => !$modifier->status]);

            return response()->json([
                'success' => true,
                'data' => $modifier->load('restaurant'),
                'message' => 'Modifier status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating modifier status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search modifiers
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = RestaurantModifier::with(['restaurant']);

            if ($request->has('q') && $request->q) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('description', 'like', '%' . $request->q . '%');
                });
            }

            if ($request->has('restaurant_id') && $request->restaurant_id) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            $modifiers = $query->active()->ordered()->get();

            return response()->json([
                'success' => true,
                'data' => $modifiers,
                'message' => 'Search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching modifiers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modifier statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => RestaurantModifier::count(),
                'active' => RestaurantModifier::where('status', true)->count(),
                'inactive' => RestaurantModifier::where('status', false)->count(),
                'by_restaurant' => RestaurantModifier::selectRaw('restaurant_id, COUNT(*) as count')
                    ->groupBy('restaurant_id')
                    ->with('restaurant:id,name')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Modifier statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
