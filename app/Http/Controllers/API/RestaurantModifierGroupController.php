<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantModifierGroup;
use App\Models\RestaurantModifier;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RestaurantModifierGroupController extends Controller
{
    /**
     * Display a listing of modifier groups with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RestaurantModifierGroup::with(['restaurant', 'modifiers']);

            // Apply filters
            if ($request->has('restaurant_id') && $request->restaurant_id) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('selection_type') && $request->selection_type) {
                $query->where('selection_type', $request->selection_type);
            }

            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $modifierGroups = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $modifierGroups,
                'message' => 'Modifier groups retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier groups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created modifier group
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'selection_type' => 'required|in:required,optional',
                'required_count' => 'nullable|integer|min:1',
                'restaurant_id' => 'required|exists:restaurants,id',
                'status' => 'boolean',
                'modifier_ids' => 'array',
                'modifier_ids.*' => 'exists:restaurant_modifiers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate required_count when selection_type is required
            if ($request->selection_type === 'required' && !$request->required_count) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required count is mandatory when selection type is required'
                ], 422);
            }

            // Check for unique name within the same restaurant
            $existingGroup = RestaurantModifierGroup::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->first();

            if ($existingGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'A modifier group with this name already exists for this restaurant'
                ], 422);
            }

            $data = [
                'name' => $request->name,
                'selection_type' => $request->selection_type,
                'required_count' => $request->selection_type === 'required' ? $request->required_count : null,
                'restaurant_id' => $request->restaurant_id,
                'status' => $request->status ?? true,
            ];

            $modifierGroup = RestaurantModifierGroup::create($data);

            // Sync modifiers if provided
            if ($request->has('modifier_ids') && is_array($request->modifier_ids)) {
                $modifierGroup->syncModifiers($request->modifier_ids);
            }

            return response()->json([
                'success' => true,
                'data' => $modifierGroup->load(['restaurant', 'modifiers']),
                'message' => 'Modifier group created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating modifier group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified modifier group
     */
    public function show($id): JsonResponse
    {
        try {
            $modifierGroup = RestaurantModifierGroup::with(['restaurant', 'modifiers'])->find($id);

            if (!$modifierGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier group not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $modifierGroup,
                'message' => 'Modifier group retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified modifier group
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $modifierGroup = RestaurantModifierGroup::find($id);

            if (!$modifierGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier group not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'selection_type' => 'required|in:required,optional',
                'required_count' => 'nullable|integer|min:1',
                'restaurant_id' => 'required|exists:restaurants,id',
                'status' => 'boolean',
                'modifier_ids' => 'array',
                'modifier_ids.*' => 'exists:restaurant_modifiers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate required_count when selection_type is required
            if ($request->selection_type === 'required' && !$request->required_count) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required count is mandatory when selection type is required'
                ], 422);
            }

            // Check for unique name within the same restaurant (excluding current group)
            $existingGroup = RestaurantModifierGroup::where('restaurant_id', $request->restaurant_id)
                ->where('name', $request->name)
                ->where('id', '!=', $id)
                ->first();

            if ($existingGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'A modifier group with this name already exists for this restaurant'
                ], 422);
            }

            $data = [
                'name' => $request->name,
                'selection_type' => $request->selection_type,
                'required_count' => $request->selection_type === 'required' ? $request->required_count : null,
                'restaurant_id' => $request->restaurant_id,
                'status' => $request->status ?? $modifierGroup->status,
            ];

            $modifierGroup->update($data);

            // Sync modifiers if provided
            if ($request->has('modifier_ids')) {
                $modifierGroup->syncModifiers($request->modifier_ids);
            }

            return response()->json([
                'success' => true,
                'data' => $modifierGroup->load(['restaurant', 'modifiers']),
                'message' => 'Modifier group updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating modifier group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified modifier group
     */
    public function destroy($id): JsonResponse
    {
        try {
            $modifierGroup = RestaurantModifierGroup::find($id);

            if (!$modifierGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier group not found'
                ], 404);
            }

            if (!$modifierGroup->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete modifier group. It is being used by products.'
                ], 422);
            }

            $modifierGroup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modifier group deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting modifier group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modifier groups by restaurant
     */
    public function getByRestaurant($restaurantId): JsonResponse
    {
        try {
            $modifierGroups = RestaurantModifierGroup::where('restaurant_id', $restaurantId)
                ->active()
                ->with('modifiers')
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $modifierGroups,
                'message' => 'Restaurant modifier groups retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving restaurant modifier groups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modifier groups by selection type
     */
    public function getBySelectionType($type): JsonResponse
    {
        try {
            $modifierGroups = RestaurantModifierGroup::where('selection_type', $type)
                ->active()
                ->with(['restaurant', 'modifiers'])
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $modifierGroups,
                'message' => 'Modifier groups by selection type retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier groups by selection type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle modifier group status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $modifierGroup = RestaurantModifierGroup::find($id);

            if (!$modifierGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modifier group not found'
                ], 404);
            }

            $modifierGroup->update(['status' => !$modifierGroup->status]);

            return response()->json([
                'success' => true,
                'data' => $modifierGroup->load(['restaurant', 'modifiers']),
                'message' => 'Modifier group status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating modifier group status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search modifier groups
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = RestaurantModifierGroup::with(['restaurant', 'modifiers']);

            if ($request->has('q') && $request->q) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->q . '%');
                });
            }

            if ($request->has('restaurant_id') && $request->restaurant_id) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            if ($request->has('selection_type') && $request->selection_type) {
                $query->where('selection_type', $request->selection_type);
            }

            $modifierGroups = $query->active()->ordered()->get();

            return response()->json([
                'success' => true,
                'data' => $modifierGroups,
                'message' => 'Search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching modifier groups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get modifier group statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => RestaurantModifierGroup::count(),
                'active' => RestaurantModifierGroup::where('status', true)->count(),
                'inactive' => RestaurantModifierGroup::where('status', false)->count(),
                'required' => RestaurantModifierGroup::where('selection_type', 'required')->count(),
                'optional' => RestaurantModifierGroup::where('selection_type', 'optional')->count(),
                'by_restaurant' => RestaurantModifierGroup::selectRaw('restaurant_id, COUNT(*) as count')
                    ->groupBy('restaurant_id')
                    ->with('restaurant:id,name')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Modifier group statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving modifier group statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available modifiers for a restaurant
     */
    public function getAvailableModifiers($restaurantId): JsonResponse
    {
        try {
            $modifiers = RestaurantModifier::where('restaurant_id', $restaurantId)
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $modifiers,
                'message' => 'Available modifiers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available modifiers: ' . $e->getMessage()
            ], 500);
        }
    }
}

