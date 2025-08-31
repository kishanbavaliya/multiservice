<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RestaurantProduct;
use App\Models\RestaurantCategory;
use App\Models\RestaurantSubcategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RestaurantProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RestaurantProduct::with(['restaurant', 'category', 'subcategory']);

        // Apply filters
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('ingredients', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
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
            'subcategory_id' => 'nullable|exists:restaurant_subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'ingredients' => 'nullable|string',
            'allergens' => 'nullable|string',
            'preparation_time' => 'nullable|integer|min:0',
            'calories' => 'nullable|integer|min:0',
            'dietary_info' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
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
            $imagePath = $request->file('image')->store('restaurant-products/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        $product = RestaurantProduct::create($data);

        return response()->json([
            'success' => true,
            'data' => $product->load(['restaurant', 'category', 'subcategory']),
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = RestaurantProduct::with(['restaurant', 'category', 'subcategory'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = RestaurantProduct::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'sometimes|required|exists:restaurants,id',
            'category_id' => 'sometimes|required|exists:restaurant_categories,id',
            'subcategory_id' => 'nullable|exists:restaurant_subcategories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'ingredients' => 'nullable|string',
            'allergens' => 'nullable|string',
            'preparation_time' => 'nullable|integer|min:0',
            'calories' => 'nullable|integer|min:0',
            'dietary_info' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
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
            // Delete old image if exists
            if ($product->image_url) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image_url));
            }
            
            $imagePath = $request->file('image')->store('restaurant-products/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product->load(['restaurant', 'category', 'subcategory']),
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = RestaurantProduct::findOrFail($id);
        
        // Delete associated image
        if ($product->image_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image_url));
        }
        
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Get products by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $products = RestaurantProduct::with(['category', 'subcategory'])
            ->where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Restaurant products retrieved successfully'
        ]);
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId)
    {
        $products = RestaurantProduct::with(['restaurant', 'subcategory'])
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Category products retrieved successfully'
        ]);
    }

    /**
     * Get products by subcategory
     */
    public function getBySubcategory($subcategoryId)
    {
        $products = RestaurantProduct::with(['restaurant', 'category'])
            ->where('subcategory_id', $subcategoryId)
            ->where('is_available', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Subcategory products retrieved successfully'
        ]);
    }

    /**
     * Toggle product availability
     */
    public function toggleAvailability($id)
    {
        $product = RestaurantProduct::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product availability updated successfully'
        ]);
    }

    /**
     * Toggle product featured status
     */
    public function toggleFeatured($id)
    {
        $product = RestaurantProduct::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product featured status updated successfully'
        ]);
    }

    /**
     * Get featured products
     */
    public function getFeatured()
    {
        $products = RestaurantProduct::with(['restaurant', 'category', 'subcategory'])
            ->where('is_featured', true)
            ->where('is_available', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Featured products retrieved successfully'
        ]);
    }

    /**
     * Get popular products
     */
    public function getPopular()
    {
        $products = RestaurantProduct::with(['restaurant', 'category', 'subcategory'])
            ->where('is_popular', true)
            ->where('is_available', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Popular products retrieved successfully'
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'category_id' => 'nullable|exists:restaurant_categories,id',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = RestaurantProduct::with(['restaurant', 'category', 'subcategory'])
            ->where('is_available', true);

        // Apply search
        $searchQuery = $request->query;
        $query->where(function ($q) use ($searchQuery) {
            $q->where('name', 'like', '%' . $searchQuery . '%')
              ->orWhere('description', 'like', '%' . $searchQuery . '%')
              ->orWhere('ingredients', 'like', '%' . $searchQuery . '%');
        });

        // Apply filters
        if ($request->restaurant_id) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        $products = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Search completed successfully'
        ]);
    }
}

