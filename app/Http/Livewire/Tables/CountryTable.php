<?php

namespace App\Http\Livewire\Tables;

use App\Models\City;
use App\Models\CityVendor;
use App\Models\Country;
use App\Models\CountryVendor;
use App\Models\State;
use App\Models\StateVendor;
use App\Services\GeoBoundaryService;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Exception;
use Illuminate\Support\Facades\DB;

class CountryTable extends BaseDataTableComponent
{

    public $model = Country::class;
    public $per_page = 20;
    public string $defaultSortColumn = 'is_active';
    public string $defaultSortDirection = 'desc';


    public function query()
    {
        return Country::query();
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

    //
    public function syncModelBoundaries($id)
    {
        try {
            $country = Country::find($id);
            $keyword = $country->name;
            $country->boundaries = GeoBoundaryService::getPlaceBoundaries($keyword, "country");
            $country->save();
            $this->showSuccessAlert(__('Boundaries synced successfully'));
        } catch (Exception $e) {
            $msg = __('Failed to sync boundaries');
            $msg .= ". ";
            $msg .= $e->getMessage();
            $this->showErrorAlert($msg);
        }
    }


    public function initiateDelete($id)
    {
        try {
            $this->selectedModel = $this->model::withTrashed()->find($id);
        } catch (Exception $ex) {
            $this->selectedModel = $this->model::find($id);
        }

        $this->confirm(__('Delete'), [
            'icon' => 'question',
            'toast' => false,
            'text' =>  __('Deleting this country will auto delete states & cities under it. Do you want to continue?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deleteModel',
            'onCancelled' => 'cancelled'
        ]);
    }

    //
    public function deleteModel()
    {

        //delete state - auto delete cities under it
        try {
            $this->isDemo();
            DB::beginTransaction();
            $countryId = $this->selectedModel->id;
            //delete cities and cities of operations
            $citiesIds = City::whereHas('state', function ($query) use ($countryId) {
                return $query->where("country_id", $countryId);
            })->pluck("id");
            City::whereIn('id', $citiesIds)->delete();
            CityVendor::whereIn('city_id', $citiesIds)->delete();
            //delete states and vendor state of operations
            $stateIds = State::where("country_id", $countryId)->pluck('id');
            State::whereIn("id", $stateIds)->delete();
            StateVendor::whereIn('state_id', $stateIds)->delete();
            //
            $this->selectedModel->delete();
            DB::commit();
            $this->showSuccessAlert(__("Deleted"));
        } catch (Exception $error) {
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
            'text' =>  __('De-activating this country, will auto de-activate operation for this country for any parcel/package vendor with this country as an area of operation. Do you still want to continue?'),
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
            //de-activate vendors set country of operations
            CountryVendor::where("country_id", $this->selectedModel->id)->update([
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
