<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;
use App\Models\Restaurant;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Offer::with('restaurant');

            // Role-based restriction: restaurant-manager sees only their restaurants
            if ($user && $user->hasRole('restaurant-manager')) {
                $restaurantIds = Restaurant::whereHas('userRoles', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                })->pluck('id')->toArray();

                if (empty($restaurantIds)) {
                    return response()->json([ 'success' => true, 'data' => collect(), 'message' => 'Offers retrieved successfully' ]);
                }

                $query->whereIn('restaurant_id', $restaurantIds);
            }

            // Filters
            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }
            if ($request->has('is_active')) {
                $query->where('active', $request->boolean('is_active'));
            }
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            $perPage = $request->get('per_page', 15);
            $offers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $offers,
                'message' => 'Offers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'required|in:percent,fixed',
                'discount_value' => 'required|numeric|min:0',
                'restaurant_id' => 'nullable|integer|exists:restaurants,id',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date',
                'active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([ 'success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors() ], 422);
            }

            $user = Auth::user();
            // restaurant-manager can only create offers for their restaurants
            if ($user && $user->hasRole('restaurant-manager') && $request->filled('restaurant_id')) {
                $allowed = Restaurant::where('id', $request->restaurant_id)
                    ->whereHas('userRoles', function ($q) use ($user) {
                        $q->where('user_id', $user->id)->where('is_active', true);
                    })->exists();

                if (!$allowed) {
                    return response()->json([ 'success' => false, 'message' => 'Unauthorized to create offer for this restaurant' ], 403);
                }
            }

            $offer = Offer::create($request->all());

            return response()->json([ 'success' => true, 'data' => $offer->load('restaurant'), 'message' => 'Offer created successfully' ], 201);

        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'message' => 'Failed to create offer', 'error' => $e->getMessage() ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $offer = Offer::with('restaurant')->findOrFail($id);
            $user = Auth::user();

            if ($user && $user->hasRole('restaurant-manager')) {
                $restaurantIds = Restaurant::whereHas('userRoles', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                })->pluck('id')->toArray();

                if (!in_array($offer->restaurant_id, $restaurantIds)) {
                    return response()->json([ 'success' => false, 'message' => 'Offer not found' ], 404);
                }
            }

            return response()->json([ 'success' => true, 'data' => $offer, 'message' => 'Offer retrieved successfully' ]);

        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'message' => 'Offer not found' ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'discount_type' => 'sometimes|required|in:percent,fixed',
                'discount_value' => 'sometimes|required|numeric|min:0',
                'restaurant_id' => 'nullable|integer|exists:restaurants,id',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date',
                'active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([ 'success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors() ], 422);
            }

            $user = Auth::user();
            if ($user && $user->hasRole('restaurant-manager')) {
                $restaurantIds = Restaurant::whereHas('userRoles', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                })->pluck('id')->toArray();

                if (!in_array($offer->restaurant_id, $restaurantIds)) {
                    return response()->json([ 'success' => false, 'message' => 'Unauthorized to update this offer' ], 403);
                }
            }

            $offer->update($request->all());

            return response()->json([ 'success' => true, 'data' => $offer->load('restaurant'), 'message' => 'Offer updated successfully' ]);

        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'message' => 'Failed to update offer', 'error' => $e->getMessage() ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);
            $user = Auth::user();
            if ($user && $user->hasRole('restaurant-manager')) {
                $restaurantIds = Restaurant::whereHas('userRoles', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                })->pluck('id')->toArray();

                if (!in_array($offer->restaurant_id, $restaurantIds)) {
                    return response()->json([ 'success' => false, 'message' => 'Unauthorized to delete this offer' ], 403);
                }
            }

            $offer->delete();
            return response()->json([ 'success' => true, 'message' => 'Offer deleted successfully' ]);

        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'message' => 'Failed to delete offer', 'error' => $e->getMessage() ], 500);
        }
    }

    public function getByRestaurant($restaurantId): JsonResponse
    {
        try {
            $offers = Offer::where('restaurant_id', $restaurantId)->where('active', true)->orderBy('created_at', 'desc')->get();
            return response()->json([ 'success' => true, 'data' => $offers, 'message' => 'Offers retrieved successfully' ]);
        } catch (\Exception $e) {
            return response()->json([ 'success' => false, 'message' => 'Failed to retrieve offers', 'error' => $e->getMessage() ], 500);
        }
    }
}
