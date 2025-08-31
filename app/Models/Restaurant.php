<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class Restaurant extends Model
{
    use SoftDeletes;

    protected $table = 'restaurants';

    protected $fillable = [
        'name', 'description', 'logo_url', 'banner_url',
        'cuisine_type', 'address', 'city', 'state', 'country', 'postal_code',
        'latitude', 'longitude', 'phone', 'email', 'website',
        'opening_hours', 'delivery_fee', 'minimum_order', 'min_delivery_time', 
        'max_delivery_time', 'delivery_available', 'pickup_available', 'delivery_radius',
        'status', 'is_featured', 'is_verified', 'rating', 'total_reviews',
        'assigned_admin_id', 'assigned_manager_id'
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'delivery_fee' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'delivery_radius' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'delivery_available' => 'boolean',
        'pickup_available' => 'boolean',
    ];

    protected $appends = [
        'logo_url_formatted',
        'banner_url_formatted',
        'full_address',
        'is_open',
        'delivery_time_range',
        'can_user_manage'
    ];

    // Accessor for logo_url
    public function getLogoUrlFormattedAttribute()
    {
        return $this->logo_url ? URL::to($this->logo_url) : null;
    }

    // Accessor for banner_url
    public function getBannerUrlFormattedAttribute()
    {
        return $this->banner_url ? URL::to($this->banner_url) : null;
    }

    // Accessor for full address
    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->city, $this->state, $this->country, $this->postal_code]);
        return implode(', ', $parts);
    }

    // Check if restaurant is open
    public function getIsOpenAttribute()
    {
        if (!$this->opening_hours) {
            return true; // Default to open if no hours set
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        
        if (!isset($this->opening_hours[$dayOfWeek])) {
            return false;
        }

        $hours = $this->opening_hours[$dayOfWeek];
        if (!isset($hours['open']) || !isset($hours['close'])) {
            return true;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    // Get delivery time range
    public function getDeliveryTimeRangeAttribute()
    {
        return $this->min_delivery_time . '-' . $this->max_delivery_time . ' min';
    }

    // Check if current user can manage this restaurant
    public function getCanUserManageAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        // Super admin can manage all restaurants
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check if user is assigned to this restaurant
        return $this->userRoles()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    // Relationships
    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function userRoles()
    {
        return $this->hasMany(RestaurantUserRole::class);
    }

    public function categories()
    {
        return $this->hasMany(RestaurantCategory::class);
    }

    public function subcategories()
    {
        return $this->hasMany(RestaurantSubcategory::class);
    }

    public function products()
    {
        return $this->hasMany(RestaurantProduct::class);
    }

    public function banners()
    {
        return $this->hasMany(RestaurantBanner::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'restaurant_id', 'id');
    }

    public function offer()
    {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCuisine($query, $cuisineType)
    {
        return $query->where('cuisine_type', $cuisineType);
    }

    public function scopeNearby($query, $latitude, $longitude, $radius = 10)
    {
        return $query->whereRaw(
            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
            [$latitude, $longitude, $latitude, $radius]
        );
    }

    // Methods
    public function getStats()
    {
        return [
            'total_products' => $this->products()->count(),
            'total_categories' => $this->categories()->count(),
            'total_banners' => $this->banners()->count(),
            'total_orders' => 0, // Will be implemented when order system is connected
            'average_rating' => $this->rating,
            'total_reviews' => $this->total_reviews,
        ];
    }

    public function assignUser($userId, $role = 'staff', $permissions = null)
    {
        return $this->userRoles()->updateOrCreate(
            ['user_id' => $userId],
            [
                'role' => $role,
                'permissions' => $permissions,
                'assigned_at' => now(),
                'assigned_by' => Auth::id(),
                'is_active' => true
            ]
        );
    }

    public function removeUser($userId)
    {
        return $this->userRoles()
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }

    public function hasUser($userId)
    {
        return $this->userRoles()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function getUserRole($userId)
    {
        $userRole = $this->userRoles()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        
        return $userRole ? $userRole->role : null;
    }
}
