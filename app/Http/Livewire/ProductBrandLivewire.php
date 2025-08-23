<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductBrandLivewire extends BaseLivewireComponent
{

    //
    public $model = Brand::class;
    public $name;

    protected $rules = [
        "name" => "required|string",
    ];


    public function render()
    {
        return view('livewire.product-brands');
    }


    public function showCreateModal()
    {
        if (auth()->user()->can('manage-product-brands') ?? false) {
            parent::showCreateModal();
        } else {
            $this->showWarningAlert(__("You can't have the right permission for this operation"));
        }
    }

    public function save()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = new Brand();
            $model->name = $this->name;
            $model->save();
            DB::commit();
            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Brand") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Brand") . " " . __('creation failed!'));
        }
    }

    // Updating model
    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        parent::showEditModal();
    }

    public function update()
    {
        //validate
        $this->validate();

        try {

            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->save();
            DB::commit();
            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Brand") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Brand") . " " . __('updated failed!'));
        }
    }
}