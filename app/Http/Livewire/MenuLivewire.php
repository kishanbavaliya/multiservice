<?php

namespace App\Http\Livewire;

use App\Models\Menu;
use App\Models\User;
use App\Models\Vendor;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MenuLivewire extends BaseLivewireComponent
{

    //
    public $model = Menu::class;

    //
    public $name;
    public $isActive = 1;
    public $vendors;
    public $vendor_id;

    protected $rules = [
        "name" => "required|string",
        "vendor_id" => "required|exists:vendors,id",
    ];

    public function mount()
    {
        if (User::find(Auth::id())->role('manager')) {
            $this->vendor_id = Auth::user()->vendor_id;
        }
    }

    public function render()
    {
        return view('livewire.menu');
    }


    public function showCreateModal()
    {
        parent::showCreateModal();
        $this->vendors = Vendor::select('id', 'name')->plainVendor()->active()->get();
    }


    public function save()
    {
        if (User::find(Auth::id())->role('manager')) {
            $this->vendor_id = \Auth::user()->vendor->id;
        }
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = new Menu();
            $model->name = $this->name;
            $model->is_active = $this->isActive;
            $model->vendor_id = $this->vendor_id;
            $model->save();
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Menu") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            logger("error saving", [$error]);
            $this->showErrorAlert($error->getMessage() ?? __("Menu") . " " . __('creation failed!'));
        }
    }

    // Updating model
    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->isActive = $this->selectedModel->is_active;
        $this->emit('showEditModal');
    }

    public function update()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->is_active = $this->isActive;
            $model->save();
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Menu") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Menu") . " " . __('updated failed!'));
        }
    }
}