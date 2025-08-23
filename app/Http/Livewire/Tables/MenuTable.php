<?php

namespace App\Http\Livewire\Tables;

use App\Models\Menu;
use App\Models\Vendor;
use App\Traits\DBTrait;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuTable extends OrderingBaseDataTableComponent
{
    use DBTrait;
    public $model = Menu::class;



    public function query()
    {

        return Menu::when(Auth::user()->hasRole('manager'), function ($query) {
            return $query->where('vendor_id', Auth::user()->vendor_id);
        });
    }

    public function columns(): array
    {

        $columns = [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('Vendor'), 'vendor.name')
                ->searchable(function ($query, $searchTerm) {
                    return $query->orWhereHas('vendor', function ($query) use ($searchTerm) {
                        $query->where('vendors.name', "LIKE", "%" . $searchTerm . "%");
                    });
                })
                ->sortable(function ($query, string $direction) {
                    return $query->orderByPowerJoins('vendor.name', $direction);
                }),
            $this->activeColumn(),
            Column::make(__('Created At'), 'formatted_date'),
            $this->actionsColumn(),
        ];

        return $columns;
    }

    public function deleteModel()
    {

        try {
            $this->isDemo();
            DB::beginTransaction();
            $menuId = $this->selectedModel->id;
            $tables = $this->getTablesWithColumn("menu_id");
            foreach ($tables as $table) {
                DB::table("$table")
                    ->where('menu_id', $menuId)
                    ->update(['menu_id' => null]);
            }
            $this->selectedModel->delete();
            DB::commit();
            $this->showSuccessAlert(__("Deleted"));
        } catch (\Exception $error) {
            DB::rollBack();
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }
}