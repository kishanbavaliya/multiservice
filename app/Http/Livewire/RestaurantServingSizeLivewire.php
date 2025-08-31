<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RestaurantServingSize;
use App\Models\Restaurant;

class RestaurantServingSizeLivewire extends Component
{
    use WithPagination;

    // Serving size fields
    public $serving_size_id;
    public $restaurant_id = '';
    public $name = '';
    public $description = '';
    public $status = true;

    // Filters and search
    public $search = '';
    public $filter_restaurant = '';
    public $filter_status = '';
    public $filter_type = '';
    public $sort_by = 'name';
    public $sort_direction = 'asc';

    // UI state
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $servingSizeToDelete;

    protected $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'status' => 'boolean',
        'restaurant_id' => 'required|exists:restaurants,id',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        try {
            $query = RestaurantServingSize::with(['restaurant']);

            // Apply search filter
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            }

            // Apply filters
            if ($this->filter_restaurant) {
                $query->where('restaurant_id', $this->filter_restaurant);
            }

            if ($this->filter_status !== '') {
                $query->where('status', $this->filter_status);
            }

            // Filter type logic removed - all serving sizes are now restaurant-specific

            // Apply sorting
            $query->orderBy($this->sort_by, $this->sort_direction);

            $servingSizes = $query->paginate(15);
            $restaurants = Restaurant::active()->orderBy('name')->get();


            // Debug logging
            logger('RestaurantServingSizeLivewire - Restaurants found: ' . $restaurants->count());
            logger('RestaurantServingSizeLivewire - Restaurant names: ' . $restaurants->pluck('name')->implode(', '));

            return view('livewire.restaurant-serving-size-livewire', [
                'servingSizes' => $servingSizes,
                'restaurants' => $restaurants,
            ]);
        } catch (\Exception $e) {
            logger('RestaurantServingSizeLivewire render error: ' . $e->getMessage());
            
            // Return empty paginated result instead of collection
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1
            );
            
            return view('livewire.restaurant-serving-size-livewire', [
                'servingSizes' => $emptyPaginator,
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

    public function edit($servingSizeId)
    {
        $servingSize = RestaurantServingSize::findOrFail($servingSizeId);
        
        $this->serving_size_id = $servingSize->id;
        $this->restaurant_id = $servingSize->restaurant_id ?? '';
        $this->name = $servingSize->name;
        $this->description = $servingSize->description;
        $this->status = $servingSize->status;

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
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            $servingSize = RestaurantServingSize::findOrFail($this->serving_size_id);
            $servingSize->update($data);
            session()->flash('message', 'Serving size updated successfully!');
        } else {
            RestaurantServingSize::create($data);
            session()->flash('message', 'Serving size created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($servingSizeId)
    {
        $this->servingSizeToDelete = $servingSizeId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $servingSize = RestaurantServingSize::findOrFail($this->servingSizeToDelete);
        
        if (!$servingSize->canBeDeleted()) {
            session()->flash('error', 'Cannot delete serving size. It is being used by products.');
            $this->confirmingDelete = false;
            $this->servingSizeToDelete = null;
            return;
        }
        
        $servingSize->delete();
        
        session()->flash('message', 'Serving size deleted successfully!');
        $this->confirmingDelete = false;
        $this->servingSizeToDelete = null;
    }

    public function toggleStatus($servingSizeId)
    {
        $servingSize = RestaurantServingSize::findOrFail($servingSizeId);
        $servingSize->update(['status' => !$servingSize->status]);
        session()->flash('message', 'Serving size status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->serving_size_id = null;
        $this->restaurant_id = '';
        $this->name = '';
        $this->description = '';
        $this->status = true;
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

    public function updatedFilterType()
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

    public function getGlobalServingSizes()
    {
        return RestaurantServingSize::global()
            ->active()
            ->ordered()
            ->get();
    }

    public function getRestaurantServingSizes($restaurantId)
    {
        return RestaurantServingSize::byRestaurant($restaurantId)
            ->active()
            ->ordered()
            ->get();
    }
}
