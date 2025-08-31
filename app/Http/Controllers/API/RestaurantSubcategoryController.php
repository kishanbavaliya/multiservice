<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantSubcategory;
use App\Models\RestaurantCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RestaurantSubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RestaurantSubcategory::with(['restaurant', 'category']);

        // Apply filters
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $subcategories = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $subcategories->items(),
            'pagination' => [
                'current_page' => $subcategories->currentPage(),
                'last_page' => $subcategories->lastPage(),
                'per_page' => $subcategories->perPage(),
                'total' => $subcategories->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'category_id' => 'required|exists:restaurant_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify that the category belongs to the specified restaurant
        $category = RestaurantCategory::where('id', $request->category_id)
            ->where('restaurant_id', $request->restaurant_id)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'The specified category does not belong to the specified restaurant'
            ], 422);
        }

        $data = $request->only([
            'restaurant_id', 'category_id', 'name', 'description', 
            'sort_order', 'is_active', 'is_featured'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('restaurant-subcategories/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('restaurant-subcategories/icons', 'public');
            $data['icon_url'] = 'storage/' . $iconPath;
        }

        $subcategory = RestaurantSubcategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully',
            'data' => $subcategory->load(['restaurant', 'category'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subcategory = RestaurantSubcategory::with(['restaurant', 'category', 'products'])
            ->find($id);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subcategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subcategory = RestaurantSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'sometimes|required|exists:restaurants,id',
            'category_id' => 'sometimes|required|exists:restaurant_categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify that the category belongs to the specified restaurant if both are being updated
        if ($request->has('restaurant_id') && $request->has('category_id')) {
            $category = RestaurantCategory::where('id', $request->category_id)
                ->where('restaurant_id', $request->restaurant_id)
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'The specified category does not belong to the specified restaurant'
                ], 422);
            }
        }

        $data = $request->only([
            'restaurant_id', 'category_id', 'name', 'description', 
            'sort_order', 'is_active', 'is_featured'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($subcategory->image_url) {
                Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->image_url));
            }
            
            $imagePath = $request->file('image')->store('restaurant-subcategories/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($subcategory->icon_url) {
                Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->icon_url));
            }
            
            $iconPath = $request->file('icon')->store('restaurant-subcategories/icons', 'public');
            $data['icon_url'] = 'storage/' . $iconPath;
        }

        $subcategory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory updated successfully',
            'data' => $subcategory->load(['restaurant', 'category'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subcategory = RestaurantSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        // Delete associated images
        if ($subcategory->image_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->image_url));
        }
        if ($subcategory->icon_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->icon_url));
        }

        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully'
        ]);
    }

    /**
     * Get subcategories by restaurant.
     */
    public function getByRestaurant($restaurantId)
    {
        $subcategories = RestaurantSubcategory::with(['category'])
            ->where('restaurant_id', $restaurantId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * Get subcategories by category.
     */
    public function getByCategory($categoryId)
    {
        $subcategories = RestaurantSubcategory::with(['restaurant'])
            ->where('category_id', $categoryId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * Toggle subcategory status.
     */
    public function toggleStatus($id)
    {
        $subcategory = RestaurantSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $subcategory->update(['is_active' => !$subcategory->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory status updated successfully',
            'data' => $subcategory
        ]);
    }

    /**
     * Toggle subcategory featured status.
     */
    public function toggleFeatured($id)
    {
        $subcategory = RestaurantSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $subcategory->update(['is_featured' => !$subcategory->is_featured]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory featured status updated successfully',
            'data' => $subcategory
        ]);
    }
}

