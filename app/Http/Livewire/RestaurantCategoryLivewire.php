<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RestaurantCategory;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantCategoryLivewire extends Component
{
    use WithPagination, WithFileUploads;

    public $category_id;
    public $restaurant_id;
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
    public $filter_status = '';
    public $filter_featured = '';
    public $sort_by = 'created_at';
    public $sort_direction = 'desc';

    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $categoryToDelete;

    protected $rules = [
        'restaurant_id' => 'required|exists:restaurants,id',
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
        $query = RestaurantCategory::with(['restaurant']);

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

        $categories = $query->paginate(10);
        $restaurants = Restaurant::active()->orderBy('name')->get();

        return view('livewire.restaurant-category-livewire', [
            'categories' => $categories,
            'restaurants' => $restaurants,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($categoryId)
    {
        $category = RestaurantCategory::findOrFail($categoryId);
        
        $this->category_id = $category->id;
        $this->restaurant_id = $category->restaurant_id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->image_url = $category->image_url;
        $this->icon_url = $category->icon_url;
        $this->sort_order = $category->sort_order;
        $this->is_active = $category->is_active;
        $this->is_featured = $category->is_featured;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
        ];

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('restaurant-categories/images', 'public');
            $data['image_url'] = 'storage/' . $imagePath;
        }

        // Handle icon upload
        if ($this->icon) {
            $iconPath = $this->icon->store('restaurant-categories/icons', 'public');
            $data['icon_url'] = 'storage/' . $iconPath;
        }

        if ($this->isEditing) {
            $category = RestaurantCategory::findOrFail($this->category_id);
            $category->update($data);
            session()->flash('message', 'Category updated successfully!');
        } else {
            RestaurantCategory::create($data);
            session()->flash('message', 'Category created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($categoryId)
    {
        $this->categoryToDelete = $categoryId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $category = RestaurantCategory::findOrFail($this->categoryToDelete);
        
        // Delete associated images
        if ($category->image_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $category->image_url));
        }
        if ($category->icon_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $category->icon_url));
        }
        
        $category->delete();
        
        session()->flash('message', 'Category deleted successfully!');
        $this->confirmingDelete = false;
        $this->categoryToDelete = null;
    }

    public function toggleStatus($categoryId)
    {
        $category = RestaurantCategory::findOrFail($categoryId);
        $category->update(['is_active' => !$category->is_active]);
        session()->flash('message', 'Category status updated successfully!');
    }

    public function toggleFeatured($categoryId)
    {
        $category = RestaurantCategory::findOrFail($categoryId);
        $category->update(['is_featured' => !$category->is_featured]);
        session()->flash('message', 'Category featured status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->category_id = null;
        $this->restaurant_id = '';
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
}
