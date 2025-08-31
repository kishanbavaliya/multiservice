<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\RestaurantBanner;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RestaurantBannerLivewire extends Component
{
    use WithPagination, WithFileUploads;

    // Banner fields
    public $banner_id;
    public $restaurant_id;
    public $title;
    public $description;
    public $image;
    public $image_url;
    public $banner_type = 'homepage';
    public $position = 1;
    public $is_active = true;
    public $start_date;
    public $end_date;
    public $link_url;
    public $target_blank = false;
    public $sort_order = 0;

    // Filters and search
    public $search = '';
    public $filter_restaurant = '';
    public $filter_type = '';
    public $filter_status = '';
    public $filter_position = '';
    public $sort_by = 'created_at';
    public $sort_direction = 'desc';

    // UI state
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $bannerToDelete;

    protected $rules = [
        'restaurant_id' => 'required|exists:restaurants,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'banner_type' => 'required|in:homepage,offers,promotions,featured,sidebar,popup',
        'position' => 'required|integer|min:1|max:6',
        'is_active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after:start_date',
        'link_url' => 'nullable|url',
        'target_blank' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        try {
            $query = RestaurantBanner::with(['restaurant']);

            // Apply search filter
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            }

            // Apply filters
            if ($this->filter_restaurant) {
                $query->where('restaurant_id', $this->filter_restaurant);
            }

            if ($this->filter_type) {
                $query->where('banner_type', $this->filter_type);
            }

            if ($this->filter_status !== '') {
                $query->where('is_active', $this->filter_status);
            }

            if ($this->filter_position) {
                $query->where('position', $this->filter_position);
            }

            // Apply sorting
            $query->orderBy($this->sort_by, $this->sort_direction);

            $banners = $query->paginate(10);
            $restaurants = Restaurant::active()->orderBy('name')->get();

            return view('livewire.restaurant-banner-livewire', [
                'banners' => $banners,
                'restaurants' => $restaurants,
            ]);
        } catch (\Exception $e) {
            logger('RestaurantBannerLivewire render error: ' . $e->getMessage());
            
            return view('livewire.restaurant-banner-livewire', [
                'banners' => collect(),
                'restaurants' => collect(),
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

    public function edit($bannerId)
    {
        $banner = RestaurantBanner::findOrFail($bannerId);
        
        $this->banner_id = $banner->id;
        $this->restaurant_id = $banner->restaurant_id;
        $this->title = $banner->title;
        $this->description = $banner->description;
        $this->image_url = $banner->image_url;
        $this->banner_type = $banner->banner_type;
        $this->position = $banner->position;
        $this->is_active = $banner->is_active;
        $this->start_date = $banner->start_date ? $banner->start_date->format('Y-m-d\TH:i') : '';
        $this->end_date = $banner->end_date ? $banner->end_date->format('Y-m-d\TH:i') : '';
        $this->link_url = $banner->link_url;
        $this->target_blank = $banner->target_blank;
        $this->sort_order = $banner->sort_order;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'restaurant_id' => $this->restaurant_id,
            'title' => $this->title,
            'description' => $this->description,
            'banner_type' => $this->banner_type,
            'position' => $this->position,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date ? $this->start_date : null,
            'end_date' => $this->end_date ? $this->end_date : null,
            'link_url' => $this->link_url,
            'target_blank' => $this->target_blank,
            'sort_order' => $this->sort_order,
        ];

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('restaurant-banners/images', 'public');
            $data['image_url'] = $imagePath;
        }

        if ($this->isEditing) {
            $banner = RestaurantBanner::findOrFail($this->banner_id);
            $banner->update($data);
            session()->flash('message', 'Banner updated successfully!');
        } else {
            RestaurantBanner::create($data);
            session()->flash('message', 'Banner created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($bannerId)
    {
        $this->bannerToDelete = $bannerId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $banner = RestaurantBanner::findOrFail($this->bannerToDelete);
        
        // Delete associated image
        if ($banner->image_url) {
            Storage::disk('public')->delete($banner->image_url);
        }
        
        $banner->delete();
        
        session()->flash('message', 'Banner deleted successfully!');
        $this->confirmingDelete = false;
        $this->bannerToDelete = null;
    }

    public function toggleStatus($bannerId)
    {
        $banner = RestaurantBanner::findOrFail($bannerId);
        $banner->update(['is_active' => !$banner->is_active]);
        session()->flash('message', 'Banner status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->banner_id = null;
        $this->restaurant_id = '';
        $this->title = '';
        $this->description = '';
        $this->image = null;
        $this->image_url = '';
        $this->banner_type = 'homepage';
        $this->position = 1;
        $this->is_active = true;
        $this->start_date = '';
        $this->end_date = '';
        $this->link_url = '';
        $this->target_blank = false;
        $this->sort_order = 0;
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

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterPosition()
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

    public function getBannerTypes()
    {
        return [
            'homepage' => 'Homepage',
            'offers' => 'Offers',
            'promotions' => 'Promotions',
            'featured' => 'Featured',
            'sidebar' => 'Sidebar',
            'popup' => 'Popup',
        ];
    }

    public function getPositions()
    {
        return [
            1 => 'Top',
            2 => 'Middle',
            3 => 'Bottom',
            4 => 'Left Sidebar',
            5 => 'Right Sidebar',
            6 => 'Full Width',
        ];
    }
}

