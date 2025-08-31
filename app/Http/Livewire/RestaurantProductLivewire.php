<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RestaurantProduct;
use App\Models\RestaurantCategory;
use App\Models\RestaurantSubcategory;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantProductLivewire extends Component
{
    use WithPagination, WithFileUploads;

    // Product fields
    public $product_id;
    public $restaurant_id;
    public $category_id;
    public $subcategory_id;
    public $name;
    public $description;
    public $price;
    public $original_price;
    public $discount_percentage;
    public $discount_amount;
    public $image;
    public $image_url;
    public $ingredients;
    public $allergens;
    public $preparation_time;
    public $calories;
    public $dietary_info;
    public $is_available = true;
    public $is_featured = false;
    public $is_popular = false;
    public $is_recommended = false;
    public $stock_quantity = 0;
    public $track_stock = false;
    public $allow_out_of_stock_orders = false;
    public $allow_customization = false;
    public $sort_order = 0;

    // Filters and search
    public $search = '';
    public $filter_restaurant = '';
    public $filter_category = '';
    public $filter_subcategory = '';
    public $filter_status = '';
    public $filter_featured = '';
    public $filter_price_min = '';
    public $filter_price_max = '';
    public $sort_by = 'created_at';
    public $sort_direction = 'desc';

    // UI state
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $productToDelete;


    protected $rules = [
        'restaurant_id' => 'required|exists:restaurants,id',
        'category_id' => 'required|exists:restaurant_categories,id',
        'subcategory_id' => 'nullable|exists:restaurant_subcategories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'original_price' => 'nullable|numeric|min:0',
        'discount_percentage' => 'nullable|numeric|min:0|max:100',
        'discount_amount' => 'nullable|numeric|min:0',
        'image' => 'nullable|image|max:2048',
        'ingredients' => 'nullable|string',
        'allergens' => 'nullable|string',
        'preparation_time' => 'nullable|integer|min:0',
        'calories' => 'nullable|integer|min:0',
        'dietary_info' => 'nullable|string',
        'stock_quantity' => 'nullable|integer|min:0',
        'sort_order' => 'integer|min:0',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function updatedOriginalPrice()
    {
        $this->calculatePrice();
    }

    public function updatedDiscountPercentage()
    {
        $this->calculatePrice();
    }

    protected function calculatePrice()
    {
        if ($this->original_price && $this->discount_percentage) {
            $this->price = (float) $this->original_price * (1 - (float) $this->discount_percentage / 100);
            $this->discount_amount = (float) $this->original_price - $this->price;
        } else {
            $this->price = (float) $this->original_price ?: 0;
            $this->discount_amount = 0;
        }
    }

    public function render()
    {
        try {
            $query = RestaurantProduct::with(['restaurant', 'category', 'subcategory']);

            // Apply search filter
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('ingredients', 'like', '%' . $this->search . '%');
                });
            }

            // Apply filters
            if ($this->filter_restaurant) {
                $query->where('restaurant_id', $this->filter_restaurant);
            }

            if ($this->filter_category) {
                $query->where('category_id', $this->filter_category);
            }

            if ($this->filter_subcategory) {
                $query->where('subcategory_id', $this->filter_subcategory);
            }

            if ($this->filter_status !== '') {
                $query->where('is_available', $this->filter_status);
            }

            if ($this->filter_featured !== '') {
                $query->where('is_featured', $this->filter_featured);
            }

            if ($this->filter_price_min !== '') {
                $query->where('price', '>=', $this->filter_price_min);
            }

            if ($this->filter_price_max !== '') {
                $query->where('price', '<=', $this->filter_price_max);
            }

            // Apply sorting
            $query->orderBy($this->sort_by, $this->sort_direction);

            $products = $query->paginate(10);
            $restaurants = Restaurant::active()->orderBy('name')->get();
            $categories = RestaurantCategory::active()->orderBy('name')->get();
            $subcategories = collect();

            if ($this->restaurant_id) {
                $subcategories = RestaurantSubcategory::where('restaurant_id', $this->restaurant_id)
                    ->active()
                    ->orderBy('name')
                    ->get();
            }

            return view('livewire.restaurant-product-livewire', [
                'products' => $products,
                'restaurants' => $restaurants,
                'categories' => $categories,
                'subcategories' => $subcategories,
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            logger('RestaurantProductLivewire render error: ' . $e->getMessage());
            
            // Return a view with error information
            return view('livewire.restaurant-product-livewire', [
                'products' => collect(),
                'restaurants' => collect(),
                'categories' => collect(),
                'subcategories' => collect(),
                'error' => 'An error occurred while loading the data. Please check the logs for more details.'
            ]);
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($productId)
    {
        $product = RestaurantProduct::findOrFail($productId);
        
        $this->product_id = $product->id;
        $this->restaurant_id = $product->restaurant_id;
        $this->category_id = $product->category_id;
        $this->subcategory_id = $product->subcategory_id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = (float) $product->price;
        $this->original_price = (float) $product->original_price;
        $this->discount_percentage = (float) $product->discount_percentage;
        $this->discount_amount = (float) $product->discount_amount;
        $this->image_url = $product->image_url;
        $this->ingredients = $product->ingredients;
        $this->allergens = $product->allergens;
        $this->preparation_time = $product->preparation_time;
        $this->calories = $product->calories;
        $this->dietary_info = $product->dietary_info;
        $this->is_available = $product->is_available;
        $this->is_featured = $product->is_featured;
        $this->is_popular = $product->is_popular;
        $this->is_recommended = $product->is_recommended;
        $this->stock_quantity = $product->stock_quantity;
        $this->track_stock = $product->track_stock;
        $this->allow_out_of_stock_orders = $product->allow_out_of_stock_orders;
        $this->allow_customization = $product->allow_customization;
        $this->sort_order = $product->sort_order;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Calculate final price before validation
        $this->calculatePrice();
        
        $this->validate();

        $data = [
            'restaurant_id' => $this->restaurant_id,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'ingredients' => $this->ingredients,
            'allergens' => $this->allergens,
            'preparation_time' => $this->preparation_time,
            'calories' => $this->calories,
            'dietary_info' => $this->dietary_info,
            'is_available' => $this->is_available,
            'is_featured' => $this->is_featured,
            'is_popular' => $this->is_popular,
            'is_recommended' => $this->is_recommended,
            'stock_quantity' => $this->stock_quantity,
            'track_stock' => $this->track_stock,
            'allow_out_of_stock_orders' => $this->allow_out_of_stock_orders,
            'allow_customization' => $this->allow_customization,
            'sort_order' => $this->sort_order,
        ];

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('restaurant-products/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        if ($this->isEditing) {
            $product = RestaurantProduct::findOrFail($this->product_id);
            $product->update($data);
            
            session()->flash('message', 'Product updated successfully!');
        } else {
            $product = RestaurantProduct::create($data);
            
            session()->flash('message', 'Product created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($productId)
    {
        $this->productToDelete = $productId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $product = RestaurantProduct::findOrFail($this->productToDelete);
        
        // Delete associated images
        if ($product->image_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image_url));
        }
        
        $product->delete();
        
        session()->flash('message', 'Product deleted successfully!');
        $this->confirmingDelete = false;
        $this->productToDelete = null;
    }

    public function toggleStatus($productId)
    {
        $product = RestaurantProduct::findOrFail($productId);
        $product->update(['is_available' => !$product->is_available]);
        session()->flash('message', 'Product status updated successfully!');
    }

    public function toggleFeatured($productId)
    {
        $product = RestaurantProduct::findOrFail($productId);
        $product->update(['is_featured' => !$product->is_featured]);
        session()->flash('message', 'Product featured status updated successfully!');
    }

    public function togglePopular($productId)
    {
        $product = RestaurantProduct::findOrFail($productId);
        $product->update(['is_popular' => !$product->is_popular]);
        session()->flash('message', 'Product popular status updated successfully!');
    }

    public function toggleRecommended($productId)
    {
        $product = RestaurantProduct::findOrFail($productId);
        $product->update(['is_recommended' => !$product->is_recommended]);
        session()->flash('message', 'Product recommended status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->product_id = null;
        $this->restaurant_id = '';
        $this->category_id = '';
        $this->subcategory_id = '';
        $this->name = '';
        $this->description = '';
        $this->price = 0;
        $this->original_price = 0;
        $this->discount_percentage = 0;
        $this->discount_amount = 0;
        $this->image = null;
        $this->image_url = '';
        $this->ingredients = '';
        $this->allergens = '';
        $this->preparation_time = '';
        $this->calories = '';
        $this->dietary_info = '';
        $this->is_available = true;
        $this->is_featured = false;
        $this->is_popular = false;
        $this->is_recommended = false;
        $this->stock_quantity = 0;
        $this->track_stock = false;
        $this->allow_out_of_stock_orders = false;
        $this->allow_customization = false;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function updatedRestaurantId()
    {
        $this->category_id = '';
        $this->subcategory_id = '';
    }

    public function updatedCategoryId()
    {
        $this->subcategory_id = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRestaurant()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterSubcategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterFeatured()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sort_by === $field) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $field;
            $this->sort_direction = 'asc';
        }
    }

    public function getFilteredCategories()
    {
        if ($this->restaurant_id) {
            return RestaurantCategory::where('restaurant_id', $this->restaurant_id)
                ->active()
                ->orderBy('name')
                ->get();
        }
        return collect();
    }

    public function getFilteredSubcategories()
    {
        if ($this->category_id) {
            return RestaurantSubcategory::where('category_id', $this->category_id)
                ->active()
                ->orderBy('name')
                ->get();
        }
        return collect();
    }
}
