<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Offer;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class OfferLivewire extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $offer_id;
    public $title;
    public $description;
    public $discount_type = 'percent';
    public $discount_value = 0;
    public $restaurant_id;
    public $start_at;
    public $end_at;
    public $active = true;
    public $editingId;

    public $search = '';
    public $filter_restaurant = '';
    public $filter_active = '';
    public $showModal = false;
    public $isEditing = false;
    public $confirmingDelete = false;
    public $offerToDelete;

    protected $rules = [
        'title' => 'required|string|max:255',
        'discount_type' => 'required|in:percent,fixed',
        'discount_value' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'restaurant_id.required' => 'Please select a restaurant for the offer.',
    ];

    public function render()
    {
        $user = Auth::user();
        // Use Spatie HasRoles trait methods on User model (role() / hasRole())
        if ($user && $user->hasRole('admin')) {
            $offers = Offer::with('restaurant')->latest()->paginate(15);
        } elseif ($user && $user->hasRole('restaurant-manager')) {
            // Many restaurant-manager users are linked via RestaurantUserRole; try to find their assigned restaurant
            $restaurantId = $user->restaurant_id ?? null;
            // Fallback: attempt to resolve via RestaurantManagerTrait style lookup if available
            if (!$restaurantId && method_exists($user, 'getAssignedRestaurantId')) {
                $restaurantId = $user->getAssignedRestaurantId();
            }
            $offers = Offer::with('restaurant')
                ->when($restaurantId, fn($q) => $q->where('restaurant_id', $restaurantId))
                ->latest()
                ->paginate(15);
        } else {
            $offers = Offer::with('restaurant')->latest()->paginate(15);
        }

        $restaurants = Restaurant::active()->orderBy('name')->get();

        return view('livewire.admin.offer-livewire', [
            'offers' => $offers,
            'restaurants' => $restaurants,
        ]);
    }

    public function create()
    {
    $this->resetInput();
    $this->isEditing = false;
    $this->showModal = true;
    }

    public function edit($id)
    {
        $offer = Offer::findOrFail($id);
    $this->offer_id = $offer->id;
    $this->editingId = $offer->id;
        $this->title = $offer->title;
        $this->description = $offer->description;
        $this->discount_type = $offer->discount_type;
        $this->discount_value = $offer->discount_value;
        $this->restaurant_id = $offer->restaurant_id;
        $this->start_at = $offer->start_at ? $offer->start_at->format('Y-m-d\TH:i') : null;
        $this->end_at = $offer->end_at ? $offer->end_at->format('Y-m-d\TH:i') : null;
        $this->active = $offer->active;
    $this->isEditing = true;
    $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'restaurant_id' => $this->restaurant_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'active' => $this->active,
        ];

        if ($this->editingId) {
            $offer = Offer::findOrFail($this->editingId);
            $offer->update($data);
        } else {
            Offer::create($data);
        }

        session()->flash('message', 'Offer saved.');
    $this->closeModal();
    }

    public function delete($id)
    {
        $this->offerToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function confirmDelete()
    {
        $offer = Offer::findOrFail($this->offerToDelete);
        $offer->delete();
        session()->flash('message', 'Offer deleted.');
        $this->confirmingDelete = false;
        $this->offerToDelete = null;
    }

    protected function resetInput()
    {
        $this->offer_id = null;
        $this->title = '';
        $this->description = '';
        $this->discount_type = 'percent';
        $this->discount_value = 0;
        $this->restaurant_id = '';
        $this->start_at = null;
        $this->end_at = null;
        $this->active = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->resetInput();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRestaurant()
    {
        $this->resetPage();
    }

    public function updatedFilterActive()
    {
        $this->resetPage();
    }
}
