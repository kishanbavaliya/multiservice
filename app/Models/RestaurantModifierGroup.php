<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantModifierGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'selection_type',
        'required_count',
        'restaurant_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'required_count' => 'integer',
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function modifiers()
    {
        return $this->belongsToMany(RestaurantModifier::class, 'restaurant_modifier_group_modifier', 'modifier_group_id', 'modifier_id')
                    ->withPivot('sort_order')
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order', 'asc');
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

    public function scopeBySelectionType($query, $type)
    {
        return $query->where('selection_type', $type);
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

    public function getSelectionTypeTextAttribute()
    {
        return ucfirst($this->selection_type);
    }

    public function getSelectionTypeColorAttribute()
    {
        return $this->selection_type === 'required' ? 'red' : 'blue';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y H:i') : 'N/A';
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('M d, Y H:i') : 'N/A';
    }

    public function getModifiersCountAttribute()
    {
        return $this->modifiers()->count();
    }

    public function getSelectionDescriptionAttribute()
    {
        if ($this->selection_type === 'required') {
            return "Required: {$this->required_count} selection(s)";
        }
        return 'Optional';
    }

    // Methods
    public function canBeDeleted()
    {
        // Add logic here if modifier groups are used by products
        // For now, allow deletion
        return true;
    }

    public function isRequired()
    {
        return $this->selection_type === 'required';
    }

    public function isOptional()
    {
        return $this->selection_type === 'optional';
    }

    public function getAvailableModifiers()
    {
        return RestaurantModifier::where('restaurant_id', $this->restaurant_id)
            ->active()
            ->ordered()
            ->get();
    }

    public function syncModifiers($modifierIds)
    {
        $pivotData = [];
        foreach ($modifierIds as $index => $modifierId) {
            $pivotData[$modifierId] = ['sort_order' => $index + 1];
        }
        
        $this->modifiers()->sync($pivotData);
    }
}

