<?php

namespace App\Http\Livewire\Tables;

use App\Models\Category;
use App\Models\VendorType;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class CategoryTable extends OrderingBaseDataTableComponent
{

    public $model = Category::class;
    public bool $bulkActionsEnabled = true;
    public bool $hideBulkActionsOnEmpty = true;
    public array $bulkActions = [];


    public function mount()
    {
        $isProd = env('APP_ENV') == 'production';
        $this->bulkActionsEnabled = $isProd;
        $this->bulkActions = [
            'massActiveSelected' => __('Active'),
            'massDeactivateSelected' => __('Deactivate'),
            'massDeleteSelected' => __('Delete'),
        ];
    }

    public function filters(): array
    {
        $vendorTypesFilterArray = [
            '' => __('Any'),
        ];
        $vendorTypes = VendorType::assignable()->get();
        foreach ($vendorTypes as $vendorType) {
            $vendorTypesFilterArray[$vendorType->id] = $vendorType->name;
        }
        return [
            'vendor_type' => Filter::make(__("Vendor Type"))
                ->select($vendorTypesFilterArray),
        ];
    }


    public function query()
    {
        return Category::with('vendor_type')->withCount('sub_categories')
            ->when($this->getFilter('vendor_type'), fn($query, $vendorTypeId) => $query->where('vendor_type_id', $vendorTypeId));
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), "id")->searchable()->sortable(),
            $this->xsImageColumn(),
            Column::make(__('Name'), 'name')->searchable()->sortable(),
            Column::make(__('Vendor Type'), 'vendor_type.name')->sortable(function ($query, $direction) {
                //order by category name using join
                return $query->join('vendor_types', 'vendor_types.id', '=', 'categories.vendor_type_id')
                    ->orderBy('vendor_types.name', $direction);
            }),
            Column::make(__('No Subcategories'), 'sub_categories_count')->sortable(),
            $this->activeColumn(),
            Column::make(__('Created At'), 'formatted_date')->sortable(
                function ($query, $direction) {
                    return $query->orderBy('created_at', $direction);
                }
            ),
            $this->actionsColumn(),
        ];
    }



    //Bulk Actions
    public function massActiveSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                //loop through the selected rows
                $models = $this->selectedRowsQuery->get();
                foreach ($models as $model) {
                    $model->is_active = true;
                    $model->save();
                }
                DB::commit();
                $this->showSuccessAlert($totalItems . " " . __("Categories") . " " . __("Activated"));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__("No data selected"));
        }
    }
    public function massDeactivateSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                //loop through the selected rows
                $models = $this->selectedRowsQuery->get();
                foreach ($models as $model) {
                    $model->is_active = false;
                    $model->save();
                }
                DB::commit();
                $this->showSuccessAlert($totalItems . " " . __("Categories") . " " . __("Deactivated"));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__("No data selected"));
        }
    }
    public function massDeleteSelected()
    {
        if ($this->selectedRowsQuery->count() > 0) {
            try {
                $this->isDemo();
                $totalItems = $this->selectedRowsQuery->count();
                DB::beginTransaction();
                //loop through the selected rows
                $models = $this->selectedRowsQuery->get();
                foreach ($models as $model) {
                    try {
                        $this->selectedModel = $model;
                        $this->selectedModel->delete();
                    } catch (Exception $error) {
                        logger("Error deleting Categories", ["error" => $error]);
                    }
                }
                DB::commit();
                $this->selectedModel = null;
                $this->showSuccessAlert($totalItems . " " . __("Categories") . " " . __("Deleted"));
                $this->resetBulk();
            } catch (Exception $error) {
                DB::rollback();
                $this->showErrorAlert($error->getMessage());
            }
        } else {
            $this->showErrorAlert(__("No data selected"));
        }
    }
}