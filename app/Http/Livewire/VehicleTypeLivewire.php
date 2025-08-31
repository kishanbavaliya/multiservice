<?php

namespace App\Http\Livewire;

use App\Models\SubscriptionVendor;
use App\Models\VehicleType;
use Illuminate\Support\Facades\DB;
use Exception;

class VehicleTypeLivewire extends BaseLivewireComponent
{

    public $model = VehicleType::class;

    public $name;
    public $base_fare;
    public $distance_fare;
    public $time_fare;
    public $min_fare;
    public $is_active;
    public $types = [
        ['id' => 'car', 'name' => 'Car'],
        ['id' => 'bike', 'name' => 'Bike'],
    ];
    public $seater;
    public $type;

    protected $rules = [
        "name" => "required|string",
        "base_fare" => "required|numeric",
        "distance_fare" => 'required|numeric',
        "time_fare" => 'required|numeric',
        "min_fare" => 'required|numeric',
        "type" => 'required',
        "photo" => "required|image|max:1024",
        "secondPhoto" => "nullable|image|max:300",
        "seater" => 'required',
    ];

    public function render()
    {
        return view('livewire.taxi.vehicle_types');
    }

    public function save()
    {
        //validate
        $rules = $this->rules;
        $rules["photo"] = "required|image|max:" . setting("filelimit.vendor_type", 200) . "";
        $this->validate($rules);

        try {
            $this->isDemo();
            DB::beginTransaction();
            $vehicleType = new VehicleType();
            $vehicleType->name = $this->name;
            $vehicleType->base_fare = $this->base_fare;
            $vehicleType->distance_fare = $this->distance_fare;
            $vehicleType->time_fare = $this->time_fare;
            $vehicleType->min_fare = $this->min_fare;
            $vehicleType->seater = $this->seater;
            $vehicleType->type = $this->type;
            $vehicleType->is_active = $this->is_active;
            $vehicleType->save();

            if ($this->photo) {
                $vehicleType->clearMediaCollection();
                $vehicleType->addMedia($this->photo->getRealPath())->toMediaCollection();
                $this->photo = null;
            }
            if ($this->secondPhoto) {
                $vehicleType->clearMediaCollection('icon');
                $vehicleType->addMedia($this->secondPhoto->getRealPath())->toMediaCollection('icon');
                $this->secondPhoto = null;
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Vehicle Type") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Vehicle Type") . " " . __('creation failed!'));
        }
    }


    public function initiateEdit($id)
    {
        $this->reset();
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->base_fare = $this->selectedModel->base_fare;
        $this->distance_fare = $this->selectedModel->distance_fare;
        $this->time_fare = $this->selectedModel->time_fare;
        $this->min_fare = $this->selectedModel->min_fare;
        $this->seater = $this->selectedModel->seater;
        $this->type = $this->selectedModel->type;
        $this->is_active = $this->selectedModel->is_active;
        $this->emit('showEditModal');
    }

    public function update()
    {

        //validate
        $this->validate([
            "name" => "required|string",
            "base_fare" => "required|numeric",
            "distance_fare" => 'required|numeric',
            "time_fare" => 'required|numeric',
            "min_fare" => 'required|numeric',
            "seater" => 'required',
            "type" => 'required',
            "photo" => "nullable|sometimes|image|max:" . setting("filelimit.vendor_type", 200) . "",
            "secondPhoto" => "nullable|image|max:300",
        ]);

        try {
            $this->isDemo();
            DB::beginTransaction();
            $this->selectedModel->name = $this->name;
            $this->selectedModel->base_fare = $this->base_fare;
            $this->selectedModel->distance_fare = $this->distance_fare;
            $this->selectedModel->time_fare = $this->time_fare;
            $this->selectedModel->min_fare = $this->min_fare;
            $this->selectedModel->seater = $this->seater;
            $this->selectedModel->type = $this->type;
            $this->selectedModel->is_active = $this->is_active;
            $this->selectedModel->save();

            if ($this->photo) {

                $this->selectedModel->clearMediaCollection();
                $this->selectedModel->addMedia($this->photo->getRealPath())->toMediaCollection();
                $this->photo = null;
            }

            if ($this->secondPhoto) {
                $this->selectedModel->clearMediaCollection('icon');
                $this->selectedModel->addMedia($this->secondPhoto->getRealPath())->toMediaCollection('icon');
                $this->secondPhoto = null;
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Vehicle Type") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Vehicle Type") . " " . __('update failed!'));
        }
    }
}