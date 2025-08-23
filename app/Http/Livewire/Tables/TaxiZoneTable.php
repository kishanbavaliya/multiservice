<?php

namespace App\Http\Livewire\Tables;

use App\Models\TaxiZone;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;

class TaxiZoneTable extends BaseDataTableComponent
{

    public $model = TaxiZone::class;
    public string $defaultSortColumn = 'is_active';
    public string $defaultSortDirection = 'desc';
    public $checkDemo = true;

    public function filters(): array
    {
        return [
            'start_date' => Filter::make(__('Start Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ]),
            'end_date' => Filter::make(__('End Date'))
                ->date([
                    'min' => now()->subYear()->format('Y-m-d'), // Optional
                    'max' => now()->format('Y-m-d') // Optional
                ])
        ];
    }

    public function query()
    {
        return $this->model::when($this->getFilter('start_date'), fn($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
            ->when($this->getFilter('end_date'), fn($query, $eDate) => $query->whereDate('created_at', "<=", $eDate));
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            $this->activeColumn()->sortable(),
            Column::make(__('Created At'), 'formatted_date'),
            $this->customActionsColumn(
                $showView = false,
                $showEdit = true,
                $showDelete = true,
                $showToggleActive = true
            )
        ];
    }
}