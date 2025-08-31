<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RestaurantSubcategory;
use App\Models\RestaurantCategory;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantSubcategoryLivewire extends Component
{
    use WithPagination, WithFileUploads;

    public $subcategory_id;
    public $restaurant_id;
    public $category_id;
    public $name;
    public $description;
    public $image;
    public $icon;
    public $image_url;
    public $icon_url;
    public $sort_order = 0;
    public $is_active = true;
    public $is_featured = false;

    public $search = '';
    public $filter_restaurant = '';
    public $filter_category = '';
    public $filter_status = '';
    public $filter_featured = '';
    public $sort_by = 'created_at';
    public $sort_direction = 'desc';

    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $subcategoryToDelete;

    protected $rules = [
        'restaurant_id' => 'required|exists:restaurants,id',
        'category_id' => 'required|exists:restaurant_categories,id',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'icon' => 'nullable|image|max:1024',
        'sort_order' => 'integer|min:0',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $query = RestaurantSubcategory::with(['restaurant', 'category']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply restaurant filter
        if ($this->filter_restaurant) {
            $query->where('restaurant_id', $this->filter_restaurant);
        }

        // Apply category filter
        if ($this->filter_category) {
            $query->where('category_id', $this->filter_category);
        }

        // Apply status filter
        if ($this->filter_status !== '') {
            $query->where('is_active', $this->filter_status);
        }

        // Apply featured filter
        if ($this->filter_featured !== '') {
            $query->where('is_featured', $this->filter_featured);
        }

        // Apply sorting
        $query->orderBy($this->sort_by, $this->sort_direction);

        $subcategories = $query->paginate(10);
        $restaurants = Restaurant::active()->orderBy('name')->get();
        $categories = RestaurantCategory::active()->orderBy('name')->get();

        return view('livewire.restaurant-subcategory-livewire', [
            'subcategories' => $subcategories,
            'restaurants' => $restaurants,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($subcategoryId)
    {
        $subcategory = RestaurantSubcategory::findOrFail($subcategoryId);
        
        $this->subcategory_id = $subcategory->id;
        $this->restaurant_id = $subcategory->restaurant_id;
        $this->category_id = $subcategory->category_id;
        $this->name = $subcategory->name;
        $this->description = $subcategory->description;
        $this->image_url = $subcategory->image_url;
        $this->icon_url = $subcategory->icon_url;
        $this->sort_order = $subcategory->sort_order;
        $this->is_active = $subcategory->is_active;
        $this->is_featured = $subcategory->is_featured;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'restaurant_id' => $this->restaurant_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
        ];

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('restaurant-subcategories/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        // Handle icon upload
        if ($this->icon) {
            $iconPath = $this->icon->store('restaurant-subcategories/icons', 'public');
            $data['icon_url'] = 'storage/' . $iconPath;
        }

        if ($this->isEditing) {
            $subcategory = RestaurantSubcategory::findOrFail($this->subcategory_id);
            $subcategory->update($data);
            session()->flash('message', 'Subcategory updated successfully!');
        } else {
            RestaurantSubcategory::create($data);
            session()->flash('message', 'Subcategory created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($subcategoryId)
    {
        $this->subcategoryToDelete = $subcategoryId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $subcategory = RestaurantSubcategory::findOrFail($this->subcategoryToDelete);
        
        // Delete associated images
        if ($subcategory->image_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->image_url));
        }
        if ($subcategory->icon_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $subcategory->icon_url));
        }
        
        $subcategory->delete();
        
        session()->flash('message', 'Subcategory deleted successfully!');
        $this->confirmingDelete = false;
        $this->subcategoryToDelete = null;
    }

    public function toggleStatus($subcategoryId)
    {
        $subcategory = RestaurantSubcategory::findOrFail($subcategoryId);
        $subcategory->update(['is_active' => !$subcategory->is_active]);
        session()->flash('message', 'Subcategory status updated successfully!');
    }

    public function toggleFeatured($subcategoryId)
    {
        $subcategory = RestaurantSubcategory::findOrFail($subcategoryId);
        $subcategory->update(['is_featured' => !$subcategory->is_featured]);
        session()->flash('message', 'Subcategory featured status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->subcategory_id = null;
        $this->restaurant_id = '';
        $this->category_id = '';
        $this->name = '';
        $this->description = '';
        $this->image = null;
        $this->icon = null;
        $this->image_url = '';
        $this->icon_url = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->is_featured = false;
        $this->resetValidation();
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
}

