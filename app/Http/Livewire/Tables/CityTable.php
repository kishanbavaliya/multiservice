<?php

namespace App\Http\Livewire\Tables;

use App\Models\City;
use App\Models\CityVendor;
use App\Services\GeoBoundaryService;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Exception;
use Illuminate\Support\Facades\DB;

class CityTable extends BaseDataTableComponent
{

    public $model = City::class;
    public $per_page = 100;
    public string $defaultSortColumn = 'is_active';
    public string $defaultSortDirection = 'desc';

    public function query()
    {
        return City::with('state.country');
    }

    public function setTableRowClass($row): ?string
    {
        return $row->is_active ? null : 'inactive-item';
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('State'), "state.name")->searchable()->sortable(),
            Column::make(__('Country'), "state.country.name")->searchable()->sortable(),
            //has boundaries column
            Column::make(__('Has Boundaries'), 'boundaries')->format(function ($value, $column, $row) {
                return view('components.table.bool', $data = [
                    "model" => $row,
                    'isTrue' => $value != null,
                ]);
            })->addClass('w-48')->sortable(),
            $this->actionsColumn('components.buttons.geodata_actions')->addClass('w-4/12'),

        ];
    }

    public function syncModelBoundaries($id)
    {
        try {
            $city = City::find($id);
            $keyword = $city->name . ', ' . $city->state->name . ', ' . $city->state->country->name;
            $city->boundaries = GeoBoundaryService::getPlaceBoundaries($keyword, "city");
            $city->save();
            $this->showSuccessAlert(__('Boundaries synced successfully'));
        } catch (\Exception $e) {
            $msg = __('Failed to sync boundaries');
            $msg .= ". ";
            $msg .= $e->getMessage();
            $this->showErrorAlert($msg);
        }
    }


    //
    public function deleteModel()
    {

        //delete state - auto delete cities under it
        try {
            $this->isDemo();
            DB::beginTransaction();
            CityVendor::where("city_id", $this->selectedModel->id)->delete();
            $this->selectedModel->delete();
            DB::commit();
            $this->showSuccessAlert(__("Deleted"));
        } catch (\Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }

    public function initiateDeactivate($id)
    {
        $this->selectedModel = $this->model::find($id);

        $this->confirm(__('Deactivate'), [
            'icon' => 'question',
            'toast' => false,
            'text' =>  __('De-activating this city, will auto de-activate operation for this city for any parcel/package vendor with this city as an area of operation. Do you still want to continue?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deactivateModel',
            'onCancelled' => 'cancelled'
        ]);
    }


    public function deactivateModel()
    {

        try {
            if ($this->checkDemo) {
                $this->isDemo();
            }
            DB::beginTransaction();
            //de-activate vendors set city of operations
            CityVendor::where("city_id", $this->selectedModel->id)->update([
                "is_active" => 0
            ]);
            //
            $this->selectedModel->is_active = false;
            $this->selectedModel->save();
            DB::commit();
            $this->showSuccessAlert(__("Deactivated"));
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert("Failed");
        }
    }
}
