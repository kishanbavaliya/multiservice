<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RestaurantModifier;
use App\Models\Restaurant;

class RestaurantModifierLivewire extends Component
{
    use WithPagination;

    // Modifier fields
    public $modifier_id;
    public $restaurant_id = '';
    public $name = '';
    public $description = '';
    public $status = true;

    // Filters and search
    public $search = '';
    public $filter_restaurant = '';
    public $filter_status = '';
    public $sort_by = 'name';
    public $sort_direction = 'asc';

    // UI state
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $modifierToDelete;

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
            $query = RestaurantModifier::with(['restaurant']);

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

            // Apply sorting
            $query->orderBy($this->sort_by, $this->sort_direction);

            $modifiers = $query->paginate(15);
            $restaurants = Restaurant::active()->orderBy('name')->get();

            // Debug logging
            logger('RestaurantModifierLivewire - Restaurants found: ' . $restaurants->count());
            logger('RestaurantModifierLivewire - Restaurant names: ' . $restaurants->pluck('name')->implode(', '));

            return view('livewire.restaurant-modifier-livewire', [
                'modifiers' => $modifiers,
                'restaurants' => $restaurants,
            ]);
        } catch (\Exception $e) {
            logger('RestaurantModifierLivewire render error: ' . $e->getMessage());
            
            // Return empty paginated result instead of collection
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1
            );
            
            return view('livewire.restaurant-modifier-livewire', [
                'modifiers' => $emptyPaginator,
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

    public function edit($modifierId)
    {
        $modifier = RestaurantModifier::findOrFail($modifierId);
        
        $this->modifier_id = $modifier->id;
        $this->restaurant_id = $modifier->restaurant_id;
        $this->name = $modifier->name;
        $this->description = $modifier->description;
        $this->status = $modifier->status;

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
            $modifier = RestaurantModifier::findOrFail($this->modifier_id);
            $modifier->update($data);
            session()->flash('message', 'Modifier updated successfully!');
        } else {
            RestaurantModifier::create($data);
            session()->flash('message', 'Modifier created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($modifierId)
    {
        $this->modifierToDelete = $modifierId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $modifier = RestaurantModifier::findOrFail($this->modifierToDelete);
        
        if (!$modifier->canBeDeleted()) {
            session()->flash('error', 'Cannot delete modifier. It is being used by products.');
            $this->confirmingDelete = false;
            $this->modifierToDelete = null;
            return;
        }
        
        $modifier->delete();
        
        session()->flash('message', 'Modifier deleted successfully!');
        $this->confirmingDelete = false;
        $this->modifierToDelete = null;
    }

    public function toggleStatus($modifierId)
    {
        $modifier = RestaurantModifier::findOrFail($modifierId);
        $modifier->update(['status' => !$modifier->status]);
        session()->flash('message', 'Modifier status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->modifier_id = null;
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

    public function sortBy($field)
    {
        if ($this->sort_by === $field) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $field;
            $this->sort_direction = 'asc';
        }
    }

    public function getRestaurantModifiers($restaurantId)
    {
        return RestaurantModifier::byRestaurant($restaurantId)
            ->active()
            ->ordered()
            ->get();
    }
}
