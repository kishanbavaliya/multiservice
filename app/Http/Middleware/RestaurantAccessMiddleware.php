<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

class RestaurantAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Closure): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin and city-admin can access all restaurants
        if ($user->hasRole(['admin', 'city-admin'])) {
            return $next($request);
        }

        // Restaurant manager can only access their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            // Get restaurant ID from route parameters or request
            $restaurantId = $request->route('restaurant') ?? 
                           $request->route('restaurantId') ?? 
                           $request->input('restaurant_id');

            if ($restaurantId) {
                $restaurant = Restaurant::find($restaurantId);
                
                if (!$restaurant) {
                    abort(404, 'Restaurant not found');
                }

                // Check if user is assigned to this restaurant
                $hasAccess = $restaurant->userRoles()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->exists();

                if (!$hasAccess) {
                    abort(403, 'You do not have access to this restaurant');
                }
            }
        }

        return $next($request);
    }
}
