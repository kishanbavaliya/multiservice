<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Subcategory;
use App\Models\Tag;
use App\Models\Vendor;

trait ProductCategoryTrait
{


    public $categoriesIDs;
    public $subCategoriesIDs = [];


    //NOTIFIERS
    public function notifyCategoriesSelector()
    {
        $this->showSelect2("#newCategoriesSelect", $this->categoriesIDs, "categoriesSelected", $this->categories);
        $this->showSelect2("#editCategoriesSelect", $this->categoriesIDs, "categoriesSelected", $this->categories);
    }
    public function notifyTagsSelector()
    {
        $this->showSelect2("#newTagsSelect", $this->selectedTagIds, "tagsSelected", $this->tags);
        $this->showSelect2("#editTagsSelect", $this->selectedTagIds, "tagsSelected", $this->tags);
    }
    public function notifySubCategoriesSelector()
    {
        $this->showSelect2("#newSubCategoriesSelect", $this->subCategoriesIDs, "subcategoriesSelected", $this->subcategories);
        $this->showSelect2("#editSubCategoriesSelect", $this->subCategoriesIDs, "subcategoriesSelected", $this->subcategories);
    }
    public function notifyMenusSelector()
    {
        $this->showSelect2("#newMenusSelect", $this->selectedMenuIds, "vendorMenuSelected", $this->vendor_menu);
        $this->showSelect2("#editMenusSelect", $this->selectedMenuIds, "vendorMenuSelected", $this->vendor_menu);
    }

    //
    //Properties
    public function getCategoriesProperty()
    {
        $selectedVendor = Vendor::find($this->vendor_id);
        return Category::active()->where('vendor_type_id', $selectedVendor->vendor_type_id ?? "")->select(['id', 'name'])->get();
    }
    public function getSubcategoriesProperty()
    {
        return Subcategory::active()->whereIn('category_id', $this->categoriesIDs ?? [])->select(['id', 'name'])->get();
    }

    public function getTagsProperty()
    {
        $selectedVendor = Vendor::find($this->vendor_id);
        return Tag::where('vendor_type_id', $selectedVendor->vendor_type_id ?? "")->select(['id', 'name'])->get();
    }

    public function getVendorMenuProperty()
    {
        return Menu::where('vendor_id', $this->vendor_id)->select(['id', 'name'])->get();
    }


    //SELECTED ACTIONS
    public function categoriesSelected($categories)
    {
        if ($this->categoriesIDs == null) {
            $this->categoriesIDs = [];
        }
        $this->categoriesIDs = $categories;
        //clear subcategories if new
        $this->subCategoriesIDs = [];
        $this->notifySubCategoriesSelector();
    }
    public function subcategoriesSelected($subcategories)
    {
        if ($this->subCategoriesIDs == null) {
            $this->subCategoriesIDs = [];
        }
        $this->subCategoriesIDs =  $subcategories;
    }

    //
    public function tagsSelected($tags)
    {
        if ($this->selectedTagIds == null) {
            $this->selectedTagIds = [];
        }
        $this->selectedTagIds = $tags;
    }

    public function vendorMenuSelected($menuIds)
    {
        if ($this->selectedMenuIds == null) {
            $this->selectedMenuIds = [];
        }
        $this->selectedMenuIds = $menuIds;
    }
}
