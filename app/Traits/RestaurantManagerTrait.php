<?php

namespace App\Traits;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

trait RestaurantManagerTrait
{
    /**
     * Get the restaurant assigned to the current user
     */
    public function getAssignedRestaurant(): ?Restaurant
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('restaurant-manager')) {
            return null;
        }

        return Restaurant::whereHas('userRoles', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('is_active', true);
        })->first();
    }

    /**
     * Get the restaurant ID assigned to the current user
     */
    public function getAssignedRestaurantId(): ?int
    {
        $restaurant = $this->getAssignedRestaurant();
        return $restaurant ? $restaurant->id : null;
    }

    /**
     * Check if the current user can access the given restaurant
     */
    public function canAccessRestaurant(?Restaurant $restaurant): bool
    {
        if (!$restaurant) {
            return false;
        }

        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin and city-admin can access all restaurants
        if ($user->hasRole(['admin', 'city-admin'])) {
            return true;
        }

        // Restaurant manager can only access their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            return $restaurant->userRoles()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Scope query to only show restaurants the user can access
     */
    public function scopeAccessible($query)
    {
        $user = Auth::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // No access
        }

        // Admin and city-admin can see all restaurants
        if ($user->hasRole(['admin', 'city-admin'])) {
            return $query;
        }

        // Restaurant manager can only see their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            $restaurantId = $this->getAssignedRestaurantId();
            if ($restaurantId) {
                return $query->where('restaurant_id', $restaurantId);
            }
            return $query->whereRaw('1 = 0'); // No access
        }

        return $query->whereRaw('1 = 0'); // No access
    }

    /**
     * Get all restaurants the current user can access
     */
    public function getAccessibleRestaurants()
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect();
        }

        // Admin and city-admin can see all restaurants
        if ($user->hasRole(['admin', 'city-admin'])) {
            return Restaurant::all();
        }

        // Restaurant manager can only see their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            $restaurant = $this->getAssignedRestaurant();
            return $restaurant ? collect([$restaurant]) : collect();
        }

        return collect();
    }
}
