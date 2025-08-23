<?php

namespace App\Http\Livewire\Tables;

use App\Models\Favourite;
use Rappasoft\LaravelLivewireTables\Views\Column;

class FavouriteVendorTable extends BaseDataTableComponent
{

    public $model = Favourite::class;

    public function query()
    {
        return Favourite::with('user', 'vendor')->whereNotNull('vendor_id');
    }

    public function columns(): array
    {
        return [
            // Column::make(__('ID'), "id")->searchable()->sortable(),
            $this->indexColumn(),
            Column::make(__('Vendor'), 'vendor.name')->searchable()->sortable(),
            Column::make(__('User'), 'user.name')->searchable()->sortable(),
        ];
    }
}