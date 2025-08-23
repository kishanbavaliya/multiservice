<?php

namespace App\Http\Livewire\Tables;

use App\Models\Favourite;
use Rappasoft\LaravelLivewireTables\Views\Column;

class FavouriteTable extends BaseDataTableComponent
{

    public $model = Favourite::class;

    public function query()
    {
        return Favourite::with('user', 'product')->whereNotNull('product_id');
    }

    public function columns(): array
    {
        return [
            // Column::make(__('ID'), "id")->searchable()->sortable(),
            $this->indexColumn(),
            Column::make(__('Product'), 'product.name')->searchable()->sortable(),
            Column::make(__('User'), 'user.name')->searchable()->sortable(),
        ];
    }
}
