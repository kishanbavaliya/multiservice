<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RestaurantBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = RestaurantBanner::with(['restaurant']);

            // Apply filters
            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            }

            if ($request->has('banner_type')) {
                $query->where('banner_type', $request->banner_type);
            }

            if ($request->has('position')) {
                $query->where('position', $request->position);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Get currently active banners
            if ($request->boolean('currently_active')) {
                $query->currentlyActive();
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            $banners = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Banners retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'required|exists:restaurants,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'required|image|max:2048',
                'banner_type' => 'required|in:homepage,offers,promotions,featured,sidebar,popup',
                'position' => 'required|integer|min:1|max:6',
                'is_active' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'link_url' => 'nullable|url',
                'target_blank' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('restaurant-banners/images', 'public');
                $data['image_url'] = $imagePath;
            }

            $banner = RestaurantBanner::create($data);

            return response()->json([
                'success' => true,
                'data' => $banner->load('restaurant'),
                'message' => 'Banner created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $banner = RestaurantBanner::with(['restaurant'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $banner,
                'message' => 'Banner retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'restaurant_id' => 'sometimes|required|exists:restaurants,id',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
                'banner_type' => 'sometimes|required|in:homepage,offers,promotions,featured,sidebar,popup',
                'position' => 'sometimes|required|integer|min:1|max:6',
                'is_active' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'link_url' => 'nullable|url',
                'target_blank' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($banner->image_url) {
                    Storage::disk('public')->delete($banner->image_url);
                }
                
                $imagePath = $request->file('image')->store('restaurant-banners/images', 'public');
                $data['image_url'] = $imagePath;
            }

            $banner->update($data);

            return response()->json([
                'success' => true,
                'data' => $banner->load('restaurant'),
                'message' => 'Banner updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);
            
            // Delete associated image
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }
            
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        try {
            $banners = RestaurantBanner::where('restaurant_id', $restaurantId)
                ->currentlyActive()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Restaurant banners retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving restaurant banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners by type
     */
    public function getByType($type)
    {
        try {
            $banners = RestaurantBanner::byType($type)
                ->currentlyActive()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Banners by type retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners by type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners by position
     */
    public function getByPosition($position)
    {
        try {
            $banners = RestaurantBanner::byPosition($position)
                ->currentlyActive()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Banners by position retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners by position: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle banner status
     */
    public function toggleStatus($id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);
            $banner->update(['is_active' => !$banner->is_active]);

            return response()->json([
                'success' => true,
                'data' => $banner,
                'message' => 'Banner status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banner status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increment click count
     */
    public function incrementClick($id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);
            $banner->incrementClick();

            return response()->json([
                'success' => true,
                'message' => 'Click count incremented successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error incrementing click count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Increment impression count
     */
    public function incrementImpression($id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);
            $banner->incrementImpression();

            return response()->json([
                'success' => true,
                'message' => 'Impression count incremented successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error incrementing impression count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banner statistics
     */
    public function getStats($id)
    {
        try {
            $banner = RestaurantBanner::findOrFail($id);
            
            $stats = [
                'click_count' => $banner->click_count,
                'impression_count' => $banner->impression_count,
                'click_through_rate' => $banner->click_through_rate,
                'status' => $banner->status_text,
                'is_currently_active' => $banner->is_currently_active,
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Banner statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banner statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}

