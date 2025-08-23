<?php

namespace App\Http\Livewire\Tables;


use App\Models\WalletTransaction;
use Rappasoft\LaravelLivewireTables\Views\Column;


class UserWalletTransactionTable extends BaseDataTableComponent
{

    public $model = WalletTransaction::class;
    public $walletId;
    protected string $pageName = 'user-wallet-transactions';

    public function mount($walletId)
    {
        $this->walletId = $walletId;
    }

    public function query()
    {

        return WalletTransaction::with('wallet.user', 'payment_method')->whereWalletId($this->walletId);
    }

    public function columns(): array
    {
        return [
            $this->indexColumn(),
            Column::make(__('Type'), 'id')->format(function ($value, $column, $row) {
                return $row->credit ? __("Credit") : __("Debit");
            }),
            Column::make(__('Ref No.'), 'ref')
                ->format(function ($value, $column, $row) {
                    return view('components.table.wallet_transaction_code', $data = [
                        "value" => $value,
                        "model" => $row,
                    ]);
                })->searchable()->sortable(),

            Column::make(__('Amount'), 'amount')->format(function ($value, $column, $row) {
                return view('components.table.price', $data = [
                    "model" => $row,
                    "value" => $value,
                ]);
            })->searchable()->sortable(),

            Column::make(__('Status'))->searchable()->sortable(),
            Column::make(__('Created At'), 'created_at'),
        ];
    }
}