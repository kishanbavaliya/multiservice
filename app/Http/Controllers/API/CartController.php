<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\RestaurantProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Add item to cart (or increment)
    public function add(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'restaurant_product_id' => 'required|exists:restaurant_products,id',
            'quantity' => 'sometimes|integer|min:1',
            'restaurant_id' => 'sometimes|exists:restaurants,id',
            'restaurant_category_id' => 'sometimes|exists:restaurant_categories,id',
            'restaurant_subcategory_id' => 'sometimes|exists:restaurant_subcategories,id',
            'modifiers' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

    $product = RestaurantProduct::findOrFail($request->restaurant_product_id);

        // find or create cart scoped to this user + restaurant
        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'restaurant_id' => $product->restaurant_id,
        ], [
            'sub_total' => 0,
            'total' => 0,
            'vendor_id' => null,
        ]);

    $quantity = $request->quantity ?? 1;
    // RestaurantProduct exposes final_price accessor
    $unitPrice = $product->final_price ?? $product->price;

    $modifiersPayload = $request->modifiers ?? null;

    // Build query to find existing similar item by restaurant_product_id
    $restaurantProductId = $request->restaurant_product_id;
    $existing = $cart->items()->where('restaurant_product_id', $restaurantProductId)->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->price = $unitPrice;
            // ensure restaurant_product_id matches the canonical id
            $existing->restaurant_product_id = $restaurantProductId;
            if ($request->filled('restaurant_category_id')) $existing->restaurant_category_id = $request->restaurant_category_id;
            if ($request->filled('restaurant_subcategory_id')) $existing->restaurant_subcategory_id = $request->restaurant_subcategory_id;
            if ($request->filled('restaurant_id')) $existing->restaurant_id = $request->restaurant_id;
            $existing->save();
        } else {
            $existing = new CartItem();
            $existing->cart_id = $cart->id;
            $existing->restaurant_product_id = $restaurantProductId;
            $existing->quantity = $quantity;
            $existing->price = $unitPrice;
            // set restaurant specific ids if present
            $existing->restaurant_product_id = $restaurantProductId;
            if ($request->filled('restaurant_category_id')) $existing->restaurant_category_id = $request->restaurant_category_id;
            if ($request->filled('restaurant_subcategory_id')) $existing->restaurant_subcategory_id = $request->restaurant_subcategory_id;
            if ($request->filled('restaurant_id')) $existing->restaurant_id = $request->restaurant_id;
            $existing->save();
            // create modifiers if provided
            if ($modifiersPayload && is_array($modifiersPayload)) {
                foreach ($modifiersPayload as $mod) {
                    $existing->modifiers()->create([
                        'modifier_id' => $mod['id'] ?? null,
                        'modifier_group_id' => $mod['group_id'] ?? null,
                        'name' => $mod['name'] ?? null,
                        'price' => $mod['price'] ?? 0,
                        'quantity' => $mod['quantity'] ?? 1,
                        'restaurant_id' => $mod['restaurant_id'] ?? $request->restaurant_id ?? null,
                    ]);
                }
            }
        }

        // recalc totals
        $subTotal = $cart->items()->get()->reduce(function ($carry, $item) {
            $itemTotal = ($item->price * $item->quantity);
            // add modifiers
            if ($item->modifiers()->exists()) {
                $mods = $item->modifiers()->get();
                foreach ($mods as $m) {
                    $itemTotal += ($m->price * $m->quantity);
                }
            }
            return $carry + $itemTotal;
        }, 0);

        $cart->sub_total = $subTotal;
        $cart->total = $subTotal; // tax/fees handled later by order summary
        $cart->save();

        return response()->json([
            'success' => true,
            'cart' => $cart->load('items.product', 'items.modifiers'),
        ]);
    }

    // View cart for authenticated user (optionally filter by vendor)
    public function view(Request $request)
    {
        $user = Auth::user();
        $vendorId = $request->vendor_id ?? null;

        $query = Cart::with('items.product', 'items.modifiers')->where('user_id', $user->id);
        if ($vendorId) $query->where('vendor_id', $vendorId);

        $carts = $query->get();
        return response()->json($carts);
    }
}
