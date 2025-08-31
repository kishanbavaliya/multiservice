<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class RestaurantProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id', 'category_id', 'subcategory_id', 'name', 'description',
        'image_url', 'price', 'original_price', 'discount_percentage', 'discount_amount',
        'ingredients', 'allergens', 'preparation_time', 'calories', 'dietary_info',
        'is_available', 'is_featured', 'is_popular', 'is_recommended',
        'stock_quantity', 'track_stock', 'allow_out_of_stock_orders',
        'allow_customization', 'customization_options',
        'sort_order', 'view_count', 'order_count', 'rating', 'total_reviews'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'preparation_time' => 'integer',
        'calories' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'allow_out_of_stock_orders' => 'boolean',
        'allow_customization' => 'boolean',
        'customization_options' => 'array',
        'view_count' => 'integer',
        'order_count' => 'integer',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
    ];

    protected $appends = [
        'image_url_formatted',
        'final_price',
        'discount_percentage_calculated',
        'is_in_stock',
        'average_rating_formatted'
    ];

    // Accessors
    public function getImageUrlFormattedAttribute()
    {
        return $this->image_url ? URL::to($this->image_url) : null;
    }

    public function getFinalPriceAttribute()
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }

    public function getDiscountPercentageCalculatedAttribute()
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100, 2);
        }
        return $this->discount_percentage;
    }

    public function getIsInStockAttribute()
    {
        if (!$this->track_stock) {
            return true;
        }
        
        if ($this->allow_out_of_stock_orders) {
            return true;
        }
        
        return $this->stock_quantity > 0;
    }

    public function getAverageRatingFormattedAttribute()
    {
        return number_format($this->rating, 1);
    }

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(RestaurantCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(RestaurantSubcategory::class, 'subcategory_id');
    }

    public function servingSize()
    {
        return $this->belongsTo(RestaurantServingSize::class, 'serving_size_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('allow_out_of_stock_orders', true)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeByDietaryInfo($query, $dietaryInfo)
    {
        return $query->where('dietary_info', 'like', '%' . $dietaryInfo . '%');
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementOrderCount()
    {
        $this->increment('order_count');
    }

    public function updateRating($newRating)
    {
        $totalRating = ($this->rating * $this->total_reviews) + $newRating;
        $this->total_reviews++;
        $this->rating = $totalRating / $this->total_reviews;
        $this->save();
    }

    public function isDiscounted()
    {
        return $this->discount_percentage > 0 || 
               ($this->original_price && $this->original_price > $this->price);
    }
}
