<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantLivewire extends Component
{
    use WithPagination, WithFileUploads;

    public $restaurant_id;
    public $name;
    public $description;
    public $cuisine_type;
    public $address;
    public $city;
    public $state;
    public $country;
    public $postal_code;
    public $latitude;
    public $longitude;
    public $phone;
    public $email;
    public $website;
    public $opening_hours = [];
    public $delivery_fee = 0;
    public $minimum_order = 0;
    public $min_delivery_time = 30;
    public $max_delivery_time = 60;
    public $delivery_available = true;
    public $pickup_available = true;
    public $delivery_radius;
    public $status = 'active';
    public $is_featured = false;
    public $is_verified = false;
    public $assigned_admin_id;
    public $assigned_manager_id;

    public $logo;
    public $banner;
    public $logo_url;
    public $banner_url;

    public $search = '';
    public $filter_status = '';
    public $filter_cuisine = '';
    public $sort_by = 'created_at';
    public $sort_direction = 'desc';

    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $restaurantToDelete;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'cuisine_type' => 'nullable|string|max:255',
        'address' => 'required|string',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:20',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'website' => 'nullable|url|max:255',
        'delivery_fee' => 'nullable|numeric|min:0',
        'minimum_order' => 'nullable|numeric|min:0',
        'min_delivery_time' => 'required|integer|min:1',
        'max_delivery_time' => 'required|integer|min:1|gte:min_delivery_time',
        'delivery_radius' => 'nullable|numeric|min:0',
        'status' => 'required|in:active,inactive,suspended',
        'assigned_admin_id' => 'nullable|exists:users,id',
        'assigned_manager_id' => 'nullable|exists:users,id',
        'logo' => 'nullable|image|max:2048',
        'banner' => 'nullable|image|max:4096',
    ];

    public function mount()
    {
        $this->initializeOpeningHours();
    }

    public function initializeOpeningHours()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            if (!isset($this->opening_hours[$day])) {
                $this->opening_hours[$day] = [
                    'open' => '09:00',
                    'close' => '22:00',
                    'closed' => false
                ];
            }
        }
    }

    public function render()
    {
        $query = Restaurant::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('cuisine_type', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->filter_status) {
            $query->where('status', $this->filter_status);
        }

        // Apply cuisine filter
        if ($this->filter_cuisine) {
            $query->where('cuisine_type', $this->filter_cuisine);
        }

        // Apply sorting
        $query->orderBy($this->sort_by, $this->sort_direction);

        $restaurants = $query->with(['assignedAdmin', 'assignedManager'])
                            ->paginate(10);

        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->get();

        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['manager', 'restaurant_manager']);
        })->get();

        $cuisineTypes = Restaurant::distinct()->pluck('cuisine_type')->filter();

        return view('livewire.restaurant-livewire', [
            'restaurants' => $restaurants,
            'admins' => $admins,
            'managers' => $managers,
            'cuisineTypes' => $cuisineTypes,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $this->restaurant_id = $restaurant->id;
        $this->name = $restaurant->name;
        $this->description = $restaurant->description;
        $this->cuisine_type = $restaurant->cuisine_type;
        $this->address = $restaurant->address;
        $this->city = $restaurant->city;
        $this->state = $restaurant->state;
        $this->country = $restaurant->country;
        $this->postal_code = $restaurant->postal_code;
        $this->latitude = $restaurant->latitude;
        $this->longitude = $restaurant->longitude;
        $this->phone = $restaurant->phone;
        $this->email = $restaurant->email;
        $this->website = $restaurant->website;
        $this->opening_hours = $restaurant->opening_hours ?? $this->opening_hours;
        $this->delivery_fee = $restaurant->delivery_fee;
        $this->minimum_order = $restaurant->minimum_order;
        $this->min_delivery_time = $restaurant->min_delivery_time;
        $this->max_delivery_time = $restaurant->max_delivery_time;
        $this->delivery_available = $restaurant->delivery_available;
        $this->pickup_available = $restaurant->pickup_available;
        $this->delivery_radius = $restaurant->delivery_radius;
        $this->status = $restaurant->status;
        $this->is_featured = $restaurant->is_featured;
        $this->is_verified = $restaurant->is_verified;
        $this->assigned_admin_id = $restaurant->assigned_admin_id;
        $this->assigned_manager_id = $restaurant->assigned_manager_id;
        $this->logo_url = $restaurant->logo_url;
        $this->banner_url = $restaurant->banner_url;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'cuisine_type' => $this->cuisine_type,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'opening_hours' => $this->opening_hours,
            'delivery_fee' => $this->delivery_fee,
            'minimum_order' => $this->minimum_order,
            'min_delivery_time' => $this->min_delivery_time,
            'max_delivery_time' => $this->max_delivery_time,
            'delivery_available' => $this->delivery_available,
            'pickup_available' => $this->pickup_available,
            'delivery_radius' => $this->delivery_radius,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_verified' => $this->is_verified,
            'assigned_admin_id' => $this->assigned_admin_id,
            'assigned_manager_id' => $this->assigned_manager_id,
        ];

        // Handle logo upload
        if ($this->logo) {
            $logoPath = $this->logo->store('restaurants/logos', 'public');
            $data['logo_url'] = '/storage/' . $logoPath;
        }

        // Handle banner upload
        if ($this->banner) {
            $bannerPath = $this->banner->store('restaurants/banners', 'public');
            $data['banner_url'] = '/storage/' . $bannerPath;
        }

        if ($this->isEditing) {
            $restaurant = Restaurant::findOrFail($this->restaurant_id);
            $restaurant->update($data);
            session()->flash('message', 'Restaurant updated successfully.');
        } else {
            Restaurant::create($data);
            session()->flash('message', 'Restaurant created successfully.');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($restaurantId)
    {
        $this->restaurantToDelete = $restaurantId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $restaurant = Restaurant::findOrFail($this->restaurantToDelete);
        $restaurant->delete();
        
        session()->flash('message', 'Restaurant deleted successfully.');
        $this->confirmingDelete = false;
        $this->restaurantToDelete = null;
    }

    public function toggleStatus($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $restaurant->update([
            'status' => $restaurant->status === 'active' ? 'inactive' : 'active'
        ]);
        
        session()->flash('message', 'Restaurant status updated successfully.');
    }

    public function toggleFeatured($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $restaurant->update([
            'is_featured' => !$restaurant->is_featured
        ]);
        
        session()->flash('message', 'Restaurant featured status updated successfully.');
    }

    public function toggleVerified($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $restaurant->update([
            'is_verified' => !$restaurant->is_verified
        ]);
        
        session()->flash('message', 'Restaurant verification status updated successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'restaurant_id', 'name', 'description', 'cuisine_type', 'address',
            'city', 'state', 'country', 'postal_code', 'latitude', 'longitude',
            'phone', 'email', 'website', 'delivery_fee', 'minimum_order',
            'min_delivery_time', 'max_delivery_time', 'delivery_available',
            'pickup_available', 'delivery_radius', 'status', 'is_featured',
            'is_verified', 'assigned_admin_id', 'assigned_manager_id',
            'logo', 'banner', 'logo_url', 'banner_url'
        ]);
        $this->initializeOpeningHours();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterCuisine()
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
