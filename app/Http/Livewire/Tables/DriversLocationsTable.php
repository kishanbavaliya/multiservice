<?php

namespace App\Http\Livewire\Tables;

use App\Models\DriverLocation;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DriversLocationsTable extends BaseDataTableComponent
{

    public $model = DriverLocation::class;



    public function query()
    {
        return DriverLocation::query();
    }


    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Name'), 'driver.name')->searchable()->sortable()->addClass('w-6/12'),
            Column::make(__('LatLng'), 'lat')->format(function ($value, $column, $row) {

                $text =  $row->lat . "," . $row->lng;
                $text =  "<a href='https://www.google.com/maps?q={$text}' target='_blank'>" . $text . "</a>";
                return view('components.table.plain', [
                    "text" => $text,
                ]);
            })->searchable()->sortable(),

            Column::make(__('Status'), 'driver.is_online')->format(function ($value, $column, $row) {

                if ($value) {
                    $text =  __("Online");
                    $textColor = "text-white";
                    $bgColor = "border-0 shadow-sm bg-green-500";
                } else {
                    $text =  __("Offline");
                    $textColor = "text-white";
                    $bgColor = "border-0 shadow-sm bg-red-500";
                }

                return view('components.table.plain_chip', [
                    "text" => $text,
                    "textColor" => $textColor,
                    "bgColor" => $bgColor,
                ]);
            })->sortable(),
            Column::make(__('Free'), 'driver.assigned_orders')->format(function ($value, $column, $row) {

                if ($row->assigned_orders > 0) {
                    $text =  __("Assigned Order");
                    $textColor = "text-white";
                    $bgColor = "border-0 shadow-sm bg-green-500";
                } else {
                    $text =  __("Free");
                    $textColor = "text-white";
                    $bgColor = "border-0 shadow-sm bg-red-500";
                }

                return view('components.table.plain_chip', [
                    "text" => $text,
                    "textColor" => $textColor,
                    "bgColor" => $bgColor,
                ]);
            })->sortable(function ($query, $direction) {
                return $query->orderBy('currently_assigned_orders_count', $direction);
            }),
            Column::make(__('Last Synced'), 'updated_at')->format(function ($value, $column, $row) {
                return \Carbon\Carbon::parse($value)->diffForHumans();
            })->searchable()->sortable(),
        ];
    }
}