<?php

namespace App\Http\Livewire\Tables;

use App\Models\Brand;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ProductBrandsTable extends BaseDataTableComponent
{

    public $model = Brand::class;

    public function query()
    {
        return Brand::query();
    }

    public function columns(): array
    {

        $columns = [
            $this->indexColumn(),
            Column::make(__('Name'), 'name')->addClass("w-8/12")->searchable()->sortable(),
            // Column::make(__('Name'), 'name')->addClass("w-8/12")->searchable()->sortable(),
            Column::make(__('Created At'), 'created_at')->format(function ($value, $column, $row) {
                return $value->translatedFormat('d M Y');
            }),
        ];

        //
        if (auth()->user()->can('manage-product-brands') ?? false) {
            $columns[] = $this->customActionsColumn(
                $showView = true,
                $showEdit = true,
                $showDelete = true,
                $showToggleActive = false,
            );
        }
        return $columns;
    }
}