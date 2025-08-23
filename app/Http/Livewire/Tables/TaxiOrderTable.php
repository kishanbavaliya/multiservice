<?php

namespace App\Http\Livewire\Tables;

use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\Views\Column;


use Illuminate\Support\Facades\Auth;

class TaxiOrderTable extends OrderTable
{




    public function query()
    {

        $user = User::find(Auth::id());


        if ($user->hasRole('admin')) {
            $query = Order::setEagerLoads([]);
        } else if ($user->hasRole('fleet-manager')) {
            $query = Order::whereHas("driver", function ($query) {
                return $query->whereHas("fleets", function ($query) {
                    return $query->where('id', \Auth::user()->fleet()->id ?? null);
                });
            });
        } else {
            $query = Order::setEagerLoads([]);
        }

        return $query->whereDoesntHave("vendor")->when($this->getFilter('payment_method_id'), fn($query, $paymentMethodId) => $query->where('payment_method_id', $paymentMethodId))
            ->when($this->getFilter('status'), fn($query, $status) => $query->currentStatus($status))
            ->when($this->getFilter('payment_status'), fn($query, $pStatus) => $query->where('payment_status', $pStatus))
            ->when($this->getFilter('start_date'), fn($query, $sDate) => $query->whereDate('created_at', ">=", $sDate))
            ->when($this->getFilter('end_date'), fn($query, $eDate) => $query->whereDate('created_at', "<=", $eDate));
    }

    public function columns(): array
    {

        $columns = [
            $this->indexColumn(),
            Column::make(__('Code/Status'), 'code')

                ->format(function ($value, $column, $row) {
                    $text = __(\Str::ucfirst($row->code));
                    //status
                    $status = $row->status;
                    $statusColor = setting("appColorTheme.$status" . "Color", '#0099FF');
                    $text .= "<br/>";
                    $text .= "<span class='text-xs font-medidum' style='color:$statusColor;'>";

                    $text .= __(\Str::ucfirst($row->status));
                    $text .= "</span>";


                    return view('components.table.plain', $data = [
                        "text" => $text
                    ]);
                })

                ->searchable()->sortable(),
            Column::make(__('User'), 'user.name')
                ->format(function ($value, $column, $row) {
                    return view('components.table.user', $data = [
                        "value" => $value,
                        "model" => $row->user,
                    ]);
                })->searchable(
                    function ($query, $search) {
                        return $query->orWhereHas('user', function ($query) use ($search) {
                            return $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                    }
                )->sortable(
                    function ($query, $direction) {
                        return $query->join('users', 'users.id', '=', 'orders.user_id')
                            ->orderBy('users.name', $direction);
                    }
                ),
            Column::make(__('Driver'), 'driver.name')
                ->format(function ($value, $column, $row) {
                    return view('components.table.user', $data = [
                        "value" => $value,
                        "model" => $row->driver,
                    ]);
                })->searchable(
                    function ($query, $search) {
                        return $query->orWhereHas('driver', function ($query) use ($search) {
                            return $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                    }
                )->sortable(
                    function ($query, $direction) {
                        return $query->join('users', 'users.id', '=', 'orders.driver_id')
                            ->orderBy('users.name', $direction);
                    }
                ),

            Column::make(__('Total'), 'total')->format(function ($value, $column, $row) {

                $text = "<p>" . currencyFormat($row->total, $row->currency_symbol) . "</p>";
                $text .= "<span class='text-xs' style='color:$row->payment_status_color;'>" . __(\Str::ucfirst($row->payment_status)) . "</span>";
                return view('components.table.plain', $data = [
                    "text" => $text
                ]);

                // return view('components.table.order-total', $data = [
                //     "model" => $row
                // ]);
            })->searchable()
                ->sortable(),
            Column::make(__('Method'), 'payment_method.name')->searchable(),
        ];

        array_push($columns, Column::make(__('Created At'))->format(function ($value, $column, $row) {
            return view('components.table.formatted_date_time', $data = [
                "model" => $row
            ]);
        }));

        array_push(
            $columns,
            Column::make(__('Actions'))->format(function ($value, $column, $row) {
                return view('components.buttons.order_actions', $data = [
                    "model" => $row
                ]);
            })
        );
        return $columns;
    }


    public function exportSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            return Excel::download(new OrdersExport($this->selectedKeys), 'orders.xlsx');
        } else {
            //
        }
    }
}