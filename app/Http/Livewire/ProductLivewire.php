<?php

namespace App\Http\Livewire;

use App\Models\Menu;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\OptionGroup;
use App\Models\Option;
use App\Models\Tag;
use App\Models\User;
use App\Traits\ProductCategoryTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductLivewire extends ProductTimingLivewire
{
    use ProductCategoryTrait;

    //
    public $model = Product::class;
    public $showDayAssignment = false;
    public $showNewDayAssignment = false;

    //
    public $name;
    public $description;
    public $price;
    public $sku;
    public $barcode;
    public $discount_price = 0;
    public $capacity;
    public $unit;
    public $package_count;
    public $available_qty;
    public $vendor_id;
    public $vendor;
    public $plus_option;
    public $digital;
    public $deliverable = 1;
    public $isActive = 1;
    public $in_order = 1;
    public $age_restricted = 0;
    public $featured = 0;
    public $brand_id;

    //
    public $menusIDs = [];
    public $photos = [];
    public $digitalFile;

    //tags
    public $tagList = [];
    public $selectedTagIds = [];
    //menu
    public $selectedMenuIds = [];

    //option groups + options
    public $optionGroups = [];
    //
    public $menus = [];



    protected $rules = [
        "name" => "required|string",
        "price" => "required|numeric",
        "vendor_id" => "required|exists:vendors,id",
        "photos" => "nullable|array",
    ];


    protected $messages = [
        "vendor_id.exists" => "Invalid vendor selected",
    ];




    public function getListeners()
    {
        return $this->listeners + [
            'setOutOfStock' => 'setOutOfStock',
            'menu_idUpdated' => "menuSelected",
            'changeProductTiming' => 'changeProductTiming',
            'categoriesSelected' => 'categoriesSelected',
            'subcategoriesSelected' => 'subcategoriesSelected',
            'tagsSelected' => 'tagsSelected',
            'vendorMenuSelected' => 'vendorMenuSelected',
            'brand_idUpdated' => 'brandSelected',
        ];
    }

    public function render()
    {
        return view('livewire.products');
    }


    public function newOptionGroup()
    {
        $this->optionGroups[] = [
            "id" => null,
            'name' => '',
            'required' => false,
            'multiple' => false,
            'max_options' => null,
            'options' => []
        ];
        // get index
        $index = count($this->optionGroups) - 1;
        $this->newOption($index);
    }

    public function newOption($index)
    {
        $this->optionGroups[$index]['options'][] = [
            "id" => null,
            'name' => '',
            'price' => null,
        ];
    }

    public function removeOption($optionKey, $optionGroupKey)
    {
        unset($this->optionGroups[$optionGroupKey]['options'][$optionKey]);
        //reindex the array
        $this->optionGroups[$optionGroupKey]['options'] = array_values($this->optionGroups[$optionGroupKey]['options']);
    }


    //
    public function showCreateModal()
    {

        $this->reset();
        parent::showCreateModal();
        $this->plus_option = true;
        $this->notifyCategoriesSelector();
        $this->notifyTagsSelector();
        $this->notifyMenusSelector();
        $this->emit('loadSummerNote', "newContent", "");
    }


    public function save()
    {

        $this->validatePhotos();
        if (empty($this->vendor_id)) {
            $this->vendor_id = \Auth::user()->vendor_id;
        }
        //validate
        $this->validate();

        try {


            DB::beginTransaction();
            $model = new Product();
            $model->name = $this->name;
            $model->sku = $this->sku ?? null;
            $model->barcode = $this->barcode ?? null;
            $model->description = $this->description;
            $model->price = $this->price;
            $model->discount_price = $this->discount_price;
            $model->capacity = $this->capacity;
            $model->unit = $this->unit;
            $model->package_count = $this->package_count;
            $model->available_qty = !empty($this->available_qty) ? $this->available_qty : null;
            $model->vendor_id = $this->vendor_id ?? \Auth::user()->vendor_id;
            $model->featured = $this->featured ?? false;
            $model->plus_option = $this->plus_option ?? false;
            $model->digital = $this->digital ?? false;
            $model->deliverable = $this->digital ? false : $this->deliverable;
            $model->is_active = $this->isActive;
            $model->in_order = $this->in_order;
            $model->age_restricted = $this->age_restricted;
            $model->brand_id = $this->brand_id;
            $model->save();

            if ($this->photos) {

                $model->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $model->addMedia($photo)
                        ->usingFileName(genFileName($photo))
                        ->toMediaCollection();
                }
                $this->photos = null;
            }

            if ($this->digitalFile && $this->digital) {

                $model->clearDigitalFiles();
                $model->saveDigitalFile($this->digitalFile);
                $this->digitalFile = null;
            }
            //remove null values from the array
            $categories = Category::whereIn('id', $this->categoriesIDs ?? [])->get();
            $this->categoriesIDs = $categories->pluck('id')->toArray();
            $subCategories = Subcategory::whereIn('id', $this->subCategoriesIDs ?? [])->get();
            $this->subCategoriesIDs = $subCategories->pluck('id')->toArray();
            //
            $model->categories()->attach($this->categoriesIDs);
            $model->sub_categories()->attach($this->subCategoriesIDs);
            $model->tags()->sync($this->selectedTagIds);
            $model->menus()->sync($this->selectedMenuIds);

            //loop through the option groups
            $vendor_id = $model->vendor_id;
            foreach ($this->optionGroups as $mOptionGroup) {
                $optionGroup = OptionGroup::updateOrCreate([
                    "id" => $mOptionGroup['id'],
                    "vendor_id" => $vendor_id,
                ], [
                    "name" => $mOptionGroup['name'],
                    "multiple" => $mOptionGroup['multiple'],
                    "required" => $mOptionGroup['required'],
                    "max_options" => $mOptionGroup['max_options'] ?? null,
                ]);
                //sync the options
                $mOptionGroupOptions = collect($mOptionGroup['options']);
                foreach ($mOptionGroupOptions as $mOptionGroupOption) {
                    $option = Option::updateOrCreate([
                        "id" => $mOptionGroupOption['id'],
                        "vendor_id" => $vendor_id,
                    ], [
                        "name" => $mOptionGroupOption['name'],
                        "price" => $mOptionGroupOption['price'],
                        "product_id" => $model->id,
                        "is_active" => true,
                    ]);
                    //sync the option with the option group
                    $option->option_group_id = $optionGroup->id;
                    $option->save();
                    //sync the option with the product
                    $option->products()->syncWithoutDetaching($model->id);
                }
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Product") . " " . __('created successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Product") . " " . __('creation failed!'));
        }
    }

    // Updating model
    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        $this->name = $this->selectedModel->name;
        $this->sku = $this->selectedModel->sku;
        $this->barcode = $this->selectedModel->barcode;
        $this->description = $this->selectedModel->description;
        $this->price = $this->selectedModel->price;
        $this->discount_price = $this->selectedModel->discount_price;
        $this->capacity = $this->selectedModel->capacity;
        $this->unit = $this->selectedModel->unit;
        $this->package_count = $this->selectedModel->package_count;
        $this->available_qty = $this->selectedModel->available_qty;
        $this->vendor_id = $this->selectedModel->vendor_id;
        $this->vendor = $this->selectedModel->vendor;
        $this->plus_option = $this->selectedModel->plus_option ?? true;
        $this->digital = $this->selectedModel->digital;
        $this->deliverable = $this->selectedModel->deliverable;
        $this->isActive = $this->selectedModel->is_active;
        $this->featured = $this->selectedModel->featured;
        $this->in_order = $this->selectedModel->in_order;
        $this->age_restricted = $this->selectedModel->age_restricted;
        $this->brand_id = $this->selectedModel->brand_id;

        //load option groups
        $this->optionGroups = [];
        $optionGroups = $this->selectedModel->optionGroups;
        foreach ($optionGroups as $optionGroup) {
            $optionGroupOptions = [];

            foreach ($optionGroup->options as $option) {
                $optionGroupOptions[] = [
                    "id" => $option->id,
                    'name' => $option->name,
                    'price' => $option->price,
                ];
            }

            $this->optionGroups[] = [
                "id" => $optionGroup->id,
                'name' => $optionGroup->name,
                'required' => $optionGroup->required,
                'multiple' => $optionGroup->multiple,
                'max_options' => $optionGroup->max_options,
                'options' => $optionGroupOptions,
            ];
        }


        $this->vendor_id = $this->selectedModel->vendor_id;
        $this->emit('preselectedVendorEmit', $this->selectedModel->vendor->name ?? "");
        // categories
        $this->categoriesIDs = $this->selectedModel->categories()->pluck('category_id');
        $this->notifyCategoriesSelector();
        //subcategories
        $this->subCategoriesIDs = $this->selectedModel->sub_categories()->pluck('id');
        $this->notifySubCategoriesSelector();
        //tags
        $this->selectedTagIds = $this->selectedModel->tags->pluck('id');
        $this->notifyTagsSelector();
        //brand
        $this->emit("brand_id_Loaded", $this->brand_id);
        //menus
        $this->selectedMenuIds = $this->selectedModel->menus->pluck('id');
        $this->notifyMenusSelector();
        //clear filepond
        $this->emit('filePondClear');
        //load photos and emit event to show them in filepond
        // $mPhotos = $this->selectedModel->getMedia();
        // foreach ($mPhotos as $photo) {
        //     $this->emit('filepond-add-file', "#editProductInput", $photo->getUrl());
        // }
        $this->photos = [];
        //load summernote with selected product description
        $this->emit('loadSummerNote', "editContent", $this->description);
        //
        parent::showEditModal();
    }

    public function update()
    {
        //validate
        $this->validate(
            [
                "name" => "required|string",
                "price" => "required|numeric",
                "vendor_id" => "required|exists:vendors,id",
            ]
        );

        $this->validatePhotos();

        try {

            DB::beginTransaction();
            $model = $this->selectedModel;
            $model->name = $this->name;
            $model->sku = $this->sku ?? null;
            $model->barcode = $this->barcode ?? null;
            $model->description = $this->description;
            $model->price = $this->price;
            $model->discount_price = $this->discount_price;
            $model->capacity = $this->capacity;
            $model->unit = $this->unit;
            $model->package_count = $this->package_count;
            $model->available_qty = $this->available_qty; //!empty($this->available_qty) ? $this->available_qty : null;
            $model->vendor_id = $this->vendor_id;
            $model->plus_option = $this->plus_option ?? true;
            $model->digital = $this->digital;
            $model->deliverable = $this->digital ? false : $this->deliverable;
            $model->is_active = $this->isActive;
            $model->featured = $this->featured ?? false;
            $model->in_order = $this->in_order;
            $model->age_restricted = $this->age_restricted;
            $model->brand_id = $this->brand_id;
            $model->save();

            if ($this->photos) {

                $model->clearMediaCollection();
                foreach ($this->photos as $photo) {
                    $model->addMedia($photo)
                        ->usingFileName(genFileName($photo))
                        ->toMediaCollection();
                }
                $this->photos = null;
            }

            if ($this->digitalFile && $this->digital) {

                $model->clearDigitalFiles();
                $model->saveDigitalFile($this->digitalFile);
                // collect($this->digitalFiles)->each(
                //     function ($digitalFile)use ($model) {
                //         $model->saveDigitalFile($digitalFile);
                //     }
                // );
                $this->digitalFile = null;
            }
            //remove null values from the array
            $categories = Category::whereIn('id', $this->categoriesIDs)->get();
            $this->categoriesIDs = $categories->pluck('id')->toArray();
            $subCategories = Subcategory::whereIn('id', $this->subCategoriesIDs)->get();
            $this->subCategoriesIDs = $subCategories->pluck('id')->toArray();
            //
            $model->categories()->sync($this->categoriesIDs);
            $model->sub_categories()->sync($this->subCategoriesIDs);
            $model->tags()->sync($this->selectedTagIds);
            $model->menus()->sync($this->selectedMenuIds);

            //loop through the option groups
            $vendor_id = $model->vendor_id;
            foreach ($this->optionGroups as $mOptionGroup) {
                $optionGroup = OptionGroup::updateOrCreate([
                    "id" => $mOptionGroup['id'],
                    "vendor_id" => $vendor_id,
                ], [
                    "name" => $mOptionGroup['name'],
                    "multiple" => $mOptionGroup['multiple'],
                    "required" => $mOptionGroup['required'],
                    "max_options" => $mOptionGroup['max_options'] ?? null,
                ]);
                //sync the options
                $mOptionGroupOptions = collect($mOptionGroup['options']);
                foreach ($mOptionGroupOptions as $mOptionGroupOption) {
                    $option = Option::updateOrCreate([
                        "id" => $mOptionGroupOption['id'],
                        "vendor_id" => $vendor_id,
                    ], [
                        "name" => $mOptionGroupOption['name'],
                        "price" => $mOptionGroupOption['price'],
                        "product_id" => $model->id,
                        "is_active" => true,
                    ]);
                    //sync the option with the option group
                    $option->option_group_id = $optionGroup->id;
                    $option->save();
                    //sync the option with the product
                    $option->products()->syncWithoutDetaching($model->id);
                }
            }

            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Product") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Product") . " " . __('updated failed!'));
        }
    }

    public function validatePhotos()
    {
        //check the length of the selected photos
        $maxPhotoCount = (int) setting('filelimit.max_product_images', 3);
        if ($this->photos != null && count($this->photos) > $maxPhotoCount) {
            $errorMsg = __("You can only select") . " " . $maxPhotoCount . " " . __("photos");
            $this->addError('photos', $errorMsg);
            return;
        }
    }

    //
    public function textAreaChange($data)
    {
        $this->description = $data;
    }

    public function updatedvendorId($value)
    {
        $this->notifyCategoriesSelector();
        $this->notifyTagsSelector();
        $this->notifyMenusSelector();
    }

    //
    public function photoSelected($photos)
    {
        $this->photos = $photos;
    }

    public function brandSelected($brand)
    {
        $this->brand_id = $brand["value"];
    }


    public function getVendorsProperty()
    {

        if (User::find(Auth::id())->hasRole('admin')) {
            return Vendor::active()->get();
        } else {
            return Vendor::where('id', $this->vendor_id)->get();
        }
    }

    public function getHasSubcategoriesProperty()
    {
        return Vendor::find($this->vendor_id)->has_sub_categories ?? false;
    }



    public function setOutOfStock($id)
    {
        try {

            DB::beginTransaction();
            $product = Product::find($id);
            $product->available_qty = 0;
            $product->save();
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Product") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Product") . " " . __('updated failed!'));
        }
    }
}