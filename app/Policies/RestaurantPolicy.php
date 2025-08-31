<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any restaurants.
     */
    public function viewAny(User $user): bool
    {
        // Admin and city-admin can view all restaurants
        if ($user->hasRole(['admin', 'city-admin'])) {
            return true;
        }

        // Restaurant manager can only view their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the restaurant.
     */
    public function view(User $user, Restaurant $restaurant): bool
    {
        // Admin and city-admin can view any restaurant
        if ($user->hasRole(['admin', 'city-admin'])) {
            return true;
        }

        // Restaurant manager can only view their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            return $restaurant->userRoles()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create restaurants.
     */
    public function create(User $user): bool
    {
        // Only admin and city-admin can create restaurants
        return $user->hasRole(['admin', 'city-admin']);
    }

    /**
     * Determine whether the user can update the restaurant.
     */
    public function update(User $user, Restaurant $restaurant): bool
    {
        // Admin and city-admin can update any restaurant
        if ($user->hasRole(['admin', 'city-admin'])) {
            return true;
        }

        // Restaurant manager can only update their assigned restaurant
        if ($user->hasRole('restaurant-manager')) {
            return $restaurant->userRoles()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the restaurant.
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        // Only admin and city-admin can delete restaurants
        return $user->hasRole(['admin', 'city-admin']);
    }

    /**
     * Determine whether the user can restore the restaurant.
     */
    public function restore(User $user, Restaurant $restaurant): bool
    {
        // Only admin and city-admin can restore restaurants
        return $user->hasRole(['admin', 'city-admin']);
    }

    /**
     * Determine whether the user can permanently delete the restaurant.
     */
    public function forceDelete(User $user, Restaurant $restaurant): bool
    {
        // Only admin and city-admin can permanently delete restaurants
        return $user->hasRole(['admin', 'city-admin']);
    }

    /**
     * Determine whether the user can manage restaurant categories.
     */
    public function manageCategories(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant subcategories.
     */
    public function manageSubcategories(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant products.
     */
    public function manageProducts(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant banners.
     */
    public function manageBanners(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant serving sizes.
     */
    public function manageServingSizes(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant modifiers.
     */
    public function manageModifiers(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }

    /**
     * Determine whether the user can manage restaurant modifier groups.
     */
    public function manageModifierGroups(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }
}
