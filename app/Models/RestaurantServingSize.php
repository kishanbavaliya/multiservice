<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantServingSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function products()
    {
        return $this->hasMany(RestaurantProduct::class, 'serving_size_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('restaurant_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    public function getStatusColorAttribute()
    {
        return $this->status ? 'green' : 'red';
    }

    public function getTypeAttribute()
    {
        return $this->restaurant_id ? 'Restaurant Specific' : 'Global';
    }

    public function getTypeColorAttribute()
    {
        return $this->restaurant_id ? 'blue' : 'purple';
    }

    // Methods
    public function isGlobal()
    {
        return is_null($this->restaurant_id);
    }

    public function isRestaurantSpecific()
    {
        return !is_null($this->restaurant_id);
    }

    public function getUsageCount()
    {
        return $this->products()->count();
    }

    public function canBeDeleted()
    {
        return $this->getUsageCount() === 0;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y H:i') : 'N/A';
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('M d, Y H:i') : 'N/A';
    }
}

