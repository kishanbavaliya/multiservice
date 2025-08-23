<?php

namespace App\Http\Livewire\Tables;

use App\Models\EarningReport;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Maatwebsite\Excel\Facades\Excel;

class SingleDriverEarningHistoryTable extends BaseDataTableComponent
{

    public $model = EarningReport::class;
    public $userId;

    public function mount($userId)
    {
        $this->userId = $userId;
    }

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
        return EarningReport::with('earnings')->whereHas('earnings', function ($q) {
            return $q->where('user_id', $this->userId);
        })
            ->when($this->getFilter('start_date'), fn($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
            ->when($this->getFilter('end_date'), fn($query, $eDate) => $query->whereDate('created_at', "<=", $eDate));
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Commission'), "rate")->format(function ($value, $column, $row) {
                return view('components.table.plain', $data = [
                    "model" => $row,
                    "text" => $row->rate != null ? "" . $row->rate . "%" : "",
                ]);
            })->searchable()->sortable(),
            Column::make(__('Earning'), "earning")->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value,
                ]);
            })->searchable()->sortable(),
            Column::make(__('Admin Earned'), "commission")->format(function ($value, $column, $row) {
                $text = currencyFormat($value);
                return view('components.table.plain', $data = [
                    "model" => $row,
                    "text" => $text,
                ]);
            })->searchable()->sortable(),
            Column::make(__('Balance'), "balance")->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value,
                ]);
            })->searchable()->sortable(),
            Column::make(__('Order'), 'order.code')->format(function ($value, $column, $row) {
                return view('components.table.order', $data = [
                    "model" => $row->order,
                ]);
            }),
            Column::make(__('Created At'), 'formatted_date'),
        ];
    }
}