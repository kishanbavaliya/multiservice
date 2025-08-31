<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RestaurantBanner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'title',
        'description',
        'image_url',
        'banner_type', // homepage, offers, promotions, featured
        'position',
        'is_active',
        'start_date',
        'end_date',
        'link_url',
        'target_blank',
        'sort_order',
        'click_count',
        'impression_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_blank' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'position' => 'integer',
        'sort_order' => 'integer',
        'click_count' => 'integer',
        'impression_count' => 'integer',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('banner_type', $type);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                    });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('position', 'asc')
                    ->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return asset('storage/' . $value);
        }
        return $value;
    }

    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        $now = now();
        
        if ($this->start_date && $this->start_date > $now) {
            return 'Scheduled';
        }
        
        if ($this->end_date && $this->end_date < $now) {
            return 'Expired';
        }
        
        return 'Active';
    }

    public function getStatusColorAttribute()
    {
        $status = $this->status_text;
        
        switch ($status) {
            case 'Active':
                return 'green';
            case 'Scheduled':
                return 'blue';
            case 'Expired':
                return 'red';
            case 'Inactive':
                return 'gray';
            default:
                return 'gray';
        }
    }

    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }
        
        if ($this->end_date && $this->end_date < $now) {
            return false;
        }
        
        return true;
    }

    // Methods
    public function incrementClick()
    {
        $this->increment('click_count');
    }

    public function incrementImpression()
    {
        $this->increment('impression_count');
    }

    public function getClickThroughRateAttribute()
    {
        if ($this->impression_count == 0) {
            return 0;
        }
        
        return round(($this->click_count / $this->impression_count) * 100, 2);
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('M d, Y H:i') : 'No start date';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('M d, Y H:i') : 'No end date';
    }

    public function getBannerTypeLabelAttribute()
    {
        $types = [
            'homepage' => 'Homepage',
            'offers' => 'Offers',
            'promotions' => 'Promotions',
            'featured' => 'Featured',
            'sidebar' => 'Sidebar',
            'popup' => 'Popup',
        ];
        
        return $types[$this->banner_type] ?? ucfirst($this->banner_type);
    }

    public function getPositionLabelAttribute()
    {
        $positions = [
            1 => 'Top',
            2 => 'Middle',
            3 => 'Bottom',
            4 => 'Left Sidebar',
            5 => 'Right Sidebar',
            6 => 'Full Width',
        ];
        
        return $positions[$this->position] ?? "Position {$this->position}";
    }
}
