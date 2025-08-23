<?php

namespace App\Http\Livewire\Tables;


use App\Models\Order;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DriverOrderTable extends BaseDataTableComponent
{


    public $dataListQuery;
    public $userId;
    protected string $pageName = 'driver-orders';

    public function mount($userId)
    {
        $this->userId = $userId;
    }



    public function query()
    {
        return Order::where("driver_id", $this->userId);
    }

    public function columns(): array
    {

        return [
            $this->indexColumn(),
            Column::make(__('Code'), 'code')
                ->format(function ($value, $column, $row) {
                    return view('components.table.order', $data = [
                        "value" => $value,
                        "model" => $row,
                    ]);
                })->searchable()->sortable(),
            Column::make(__('Status'), 'status')
                ->format(function ($value, $column, $row) {
                    return view('components.table.custom', $data = [
                        "value" => __(\Str::ucfirst($row->status))
                    ]);
                }),
            Column::make(__('Total'), 'total')->format(function ($value, $column, $row) {
                return view('components.table.order-total', $data = [
                    "model" => $row
                ]);
            })->searchable()
                ->sortable(),
            Column::make(__('Created At'), 'created_at'),
        ];
    }
}