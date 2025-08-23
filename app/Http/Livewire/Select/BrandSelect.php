<?php

namespace App\Http\Livewire\Select;

use App\Models\Brand;
use Illuminate\Support\Collection;

class BrandSelect extends BaseLivewireSelect
{
    public function options($searchTerm = null): Collection
    {

        return Brand::where('name', 'like', '%' . $searchTerm . '%')
            ->limit(10)
            ->get()
            ->map(function ($model) {
                return [
                    'value' => $model->id,
                    'description' => $model->name,
                ];
            });
    }


    public function selectedOption($value)
    {
        $brand = Brand::find($value);
        return [
            'value' =>  $value,
            'description' => $brand->name ?? "",
        ];
    }
}