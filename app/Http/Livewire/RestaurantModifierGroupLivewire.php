<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RestaurantModifierGroup;
use App\Models\RestaurantModifier;
use App\Models\Restaurant;

class RestaurantModifierGroupLivewire extends Component
{
    use WithPagination;

    // Modifier group fields
    public $modifier_group_id;
    public $restaurant_id = '';
    public $name = '';
    public $selection_type = 'optional';
    public $required_count = 1;
    public $status = true;
    public $selected_modifiers = [];

    // Filters and search
    public $search = '';
    public $filter_restaurant = '';
    public $filter_status = '';
    public $filter_selection_type = '';
    public $sort_by = 'name';
    public $sort_direction = 'asc';

    // UI state
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $modifierGroupToDelete;

    protected $rules = [
        'name' => 'required|string|max:100',
        'selection_type' => 'required|in:required,optional',
        'required_count' => 'nullable|integer|min:1',
        'status' => 'boolean',
        'restaurant_id' => 'required|exists:restaurants,id',
        'selected_modifiers' => 'array',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        try {
            $query = RestaurantModifierGroup::with(['restaurant', 'modifiers']);

            // Apply search filter
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            }

            // Apply filters
            if ($this->filter_restaurant) {
                $query->where('restaurant_id', $this->filter_restaurant);
            }

            if ($this->filter_status !== '') {
                $query->where('status', $this->filter_status);
            }

            if ($this->filter_selection_type) {
                $query->where('selection_type', $this->filter_selection_type);
            }

            // Apply sorting
            $query->orderBy($this->sort_by, $this->sort_direction);

            $modifierGroups = $query->paginate(15);
            $restaurants = Restaurant::active()->orderBy('name')->get();
            $availableModifiers = collect();

            // Get available modifiers for the selected restaurant
            if ($this->restaurant_id) {
                $availableModifiers = RestaurantModifier::where('restaurant_id', $this->restaurant_id)
                    ->active()
                    ->ordered()
                    ->get();
            }

            return view('livewire.restaurant-modifier-group-livewire', [
                'modifierGroups' => $modifierGroups,
                'restaurants' => $restaurants,
                'availableModifiers' => $availableModifiers,
            ]);
        } catch (\Exception $e) {
            logger('RestaurantModifierGroupLivewire render error: ' . $e->getMessage());
            
            // Return empty paginated result instead of collection
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1
            );
            
            return view('livewire.restaurant-modifier-group-livewire', [
                'modifierGroups' => $emptyPaginator,
                'restaurants' => collect(),
                'availableModifiers' => collect(),
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

    public function edit($modifierGroupId)
    {
        $modifierGroup = RestaurantModifierGroup::with('modifiers')->findOrFail($modifierGroupId);
        
        $this->modifier_group_id = $modifierGroup->id;
        $this->restaurant_id = $modifierGroup->restaurant_id;
        $this->name = $modifierGroup->name;
        $this->selection_type = $modifierGroup->selection_type;
        $this->required_count = $modifierGroup->required_count ?? 1;
        $this->status = $modifierGroup->status;
        $this->selected_modifiers = $modifierGroup->modifiers->pluck('id')->toArray();

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Validate required_count when selection_type is required
        if ($this->selection_type === 'required') {
            $this->rules['required_count'] = 'required|integer|min:1';
        } else {
            $this->required_count = null;
        }

        $this->validate();

        $data = [
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'selection_type' => $this->selection_type,
            'required_count' => $this->required_count,
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            $modifierGroup = RestaurantModifierGroup::findOrFail($this->modifier_group_id);
            $modifierGroup->update($data);
            $modifierGroup->syncModifiers($this->selected_modifiers);
            session()->flash('message', 'Modifier group updated successfully!');
        } else {
            $modifierGroup = RestaurantModifierGroup::create($data);
            $modifierGroup->syncModifiers($this->selected_modifiers);
            session()->flash('message', 'Modifier group created successfully!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($modifierGroupId)
    {
        $this->modifierGroupToDelete = $modifierGroupId;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $modifierGroup = RestaurantModifierGroup::findOrFail($this->modifierGroupToDelete);
        
        if (!$modifierGroup->canBeDeleted()) {
            session()->flash('error', 'Cannot delete modifier group. It is being used by products.');
            $this->confirmingDelete = false;
            $this->modifierGroupToDelete = null;
            return;
        }
        
        $modifierGroup->delete();
        
        session()->flash('message', 'Modifier group deleted successfully!');
        $this->confirmingDelete = false;
        $this->modifierGroupToDelete = null;
    }

    public function toggleStatus($modifierGroupId)
    {
        $modifierGroup = RestaurantModifierGroup::findOrFail($modifierGroupId);
        $modifierGroup->update(['status' => !$modifierGroup->status]);
        session()->flash('message', 'Modifier group status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->modifier_group_id = null;
        $this->restaurant_id = '';
        $this->name = '';
        $this->selection_type = 'optional';
        $this->required_count = 1;
        $this->status = true;
        $this->selected_modifiers = [];
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

    public function updatedFilterSelectionType()
    {
        $this->resetPage();
    }

    public function updatedRestaurantId()
    {
        // Reset selected modifiers when restaurant changes
        $this->selected_modifiers = [];
    }

    public function updatedSelectionType()
    {
        // Reset required_count when selection type changes
        if ($this->selection_type === 'optional') {
            $this->required_count = null;
        } else {
            $this->required_count = 1;
        }
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

    public function getRestaurantModifierGroups($restaurantId)
    {
        return RestaurantModifierGroup::byRestaurant($restaurantId)
            ->active()
            ->with('modifiers')
            ->ordered()
            ->get();
    }
}
