<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantUserRole extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id', 'user_id', 'role', 'permissions', 'is_active',
        'assigned_at', 'expires_at', 'assigned_by'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'role_display_name',
        'is_expired',
        'days_until_expiry'
    ];

    // Accessors
    public function getRoleDisplayNameAttribute()
    {
        $roleNames = [
            'super_admin' => 'Super Admin',
            'restaurant_admin' => 'Restaurant Admin',
            'restaurant_manager' => 'Restaurant Manager',
            'staff' => 'Staff'
        ];
        
        return $roleNames[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->isAfter($this->expires_at);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }
        
        $days = now()->diffInDays($this->expires_at, false);
        return $days > 0 ? $days : 0;
    }

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    // Methods
    public function isActive()
    {
        return $this->is_active && !$this->is_expired;
    }

    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    public function hasAnyPermission($permissions)
    {
        if (!$this->permissions) {
            return false;
        }
        
        return !empty(array_intersect($permissions, $this->permissions));
    }

    public function hasAllPermissions($permissions)
    {
        if (!$this->permissions) {
            return false;
        }
        
        return empty(array_diff($permissions, $this->permissions));
    }

    public function canManageRestaurant()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin', 'restaurant_manager']);
    }

    public function canManageProducts()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin', 'restaurant_manager']);
    }

    public function canManageCategories()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin', 'restaurant_manager']);
    }

    public function canManageBanners()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin', 'restaurant_manager']);
    }

    public function canManageUsers()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin']);
    }

    public function canViewReports()
    {
        return in_array($this->role, ['super_admin', 'restaurant_admin', 'restaurant_manager']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isRestaurantAdmin()
    {
        return $this->role === 'restaurant_admin';
    }

    public function isRestaurantManager()
    {
        return $this->role === 'restaurant_manager';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }
}
