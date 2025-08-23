<?php

namespace App\Http\Livewire\Settings;


class Page extends BaseSettingsComponent
{

    //
    public $driverDocumentInstructions;
    public $vendorDocumentInstructions;
    public $driverDocumentCount;
    public $vendorDocumentCount;

    //
    public $new_type_id;
    //driver settings
    public $customerRequirements = [];
    public $driverRequirements = [];
    public $vendorRequirements = [];



    public function mount()
    {
        $this->pageSettings();
    }


    public function render()
    {
        return view('livewire.settings.page');
    }



    //
    //PAGE SETTINGS
    public function pageSettings()
    {
        $this->driverDocumentInstructions = setting('page.settings.driverDocumentInstructions', "");
        $this->vendorDocumentInstructions = setting('page.settings.vendorDocumentInstructions', "");
        $this->driverDocumentCount = (int) setting('page.settings.driverDocumentCount', 3);
        $this->vendorDocumentCount = (int) setting('page.settings.vendorDocumentCount', 3);
        //
        $this->customerRequirements = json_decode(setting('page.settings.customerRegisterRequirement'), true);
        $this->driverRequirements = json_decode(setting('page.settings.driverRegisterRequirement'), true);
        $this->vendorRequirements = json_decode(setting('page.settings.vendorRegisterRequirement'), true);
        //
        if (empty($this->customerRequirements)) {
            $this->newRequirement(0);
        }
        if (empty($this->driverRequirements)) {
            $this->newRequirement(1);
        }
        if (empty($this->vendorRequirements)) {
            $this->newRequirement(2);
        }
    }

    public function savePageSettings()
    {

        try {

            $this->isDemo();

            setting([
                'page.settings.driverDocumentInstructions' =>  $this->driverDocumentInstructions,
                'page.settings.vendorDocumentInstructions' =>  $this->vendorDocumentInstructions,
                'page.settings.driverDocumentCount' =>  $this->driverDocumentCount,
                'page.settings.vendorDocumentCount' =>  $this->vendorDocumentCount,
            ])->save();

            $this->setupEditors();
            $this->showSuccessAlert(__("Page Settings saved successfully!"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Page Settings save failed!"));
        }
    }


    public function setupEditors()
    {
        //
        $this->emit('loadSummerNote', "driverDocumentInstructionsEdit", $this->driverDocumentInstructions);
        $this->emit('loadSummerNote', "vendorDocumentInstructionsEdit", $this->vendorDocumentInstructions);
    }

    public function newRequirement($index)
    {
        $type = $this->new_type_id ?? $this->types[0]['id'];
        $input = [
            "type" => $type,
            "qty" => 1,
            "rules" => "",
            "options" => [],
            "title" => "",
            "description" => "",
        ];
        //index == 0 = customer
        if ($index == 0) {
            $this->customerRequirements[] = $input;
        }
        //index == 1 = driver
        if ($index == 1) {
            $this->driverRequirements[] = $input;
        }
        //index == 2 = vendor
        if ($index == 2) {
            $this->vendorRequirements[] = $input;
        }
    }

    public function removeRequirement($index, $key)
    {
        //index == 0 = customer
        if ($index == 0) {
            $requirements = $this->customerRequirements;
            unset($requirements[$key]);
            $this->customerRequirements = array_values($requirements);
        }
        //index == 1 = driver
        if ($index == 1) {
            $requirements = $this->driverRequirements;
            unset($requirements[$key]);
            $this->driverRequirements = array_values($requirements);
        }
        //index == 2 = vendor
        if ($index == 2) {
            $requirements = $this->vendorRequirements;
            unset($requirements[$key]);
            $this->vendorRequirements = array_values($requirements);
        }

        //
    }

    public function newRequirementOption($index, $key)
    {
        $option = [
            "id" => "",
            "name" => "",
        ];
        //index == 0 = customer
        if ($index == 0) {
            $this->customerRequirements[$key]["options"][] = $option;
        }
        //index == 1 = driver
        if ($index == 1) {
            $this->driverRequirements[$key]["options"][] = $option;
        }
        //index == 2 = vendor
        if ($index == 2) {
            $this->vendorRequirements[$key]["options"][] = $option;
        }
    }

    public function removeRequirementOption($index, $key, $optionkey)
    {


        //index == 0 = customer
        if ($index == 0) {
            $options = $this->customerRequirements[$key]["options"];
            unset($options[$key]);
            $this->customerRequirements[$key]["options"] = array_values($options);
        }
        //index == 1 = driver
        if ($index == 1) {
            $options = $this->driverRequirements[$key]["options"];
            unset($options[$key]);
            $this->driverRequirements[$key]["options"] = array_values($options);
        }
        //index == 2 = vendor
        if ($index == 2) {
            $options = $this->vendorRequirements[$key]["options"];
            unset($options[$optionkey]);
            $this->vendorRequirements[$key]["options"] = array_values($options);
        }

        //
    }



    //
    public function saveCustomerRequirements()
    {
        $rules = $this->getRequirementsRule("customerRequirements");
        $this->validate($rules);
        //

        try {
            $this->isDemo();
            setting([
                'page.settings.customerRegisterRequirement' =>  json_encode($this->customerRequirements),
            ])->save();
            $this->showSuccessAlert(__("Customer Requirement Settings saved successfully!"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Page Settings save failed!"));
        }
    }


    public function saveDriverRequirements()
    {
        $rules = $this->getRequirementsRule("driverRequirements");
        $this->validate($rules);
        //

        try {
            $this->isDemo();
            setting([
                'page.settings.driverRegisterRequirement' =>  json_encode($this->driverRequirements),
            ])->save();
            $this->showSuccessAlert(__("Driver Requirement Settings saved successfully!"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Page Settings save failed!"));
        }
    }
    public function saveVendorRequirements()
    {
        $rules = $this->getRequirementsRule("vendorRequirements");
        $this->validate($rules);
        //

        try {
            $this->isDemo();
            setting([
                'page.settings.vendorRegisterRequirement' =>  json_encode($this->vendorRequirements),
            ])->save();
            $this->showSuccessAlert(__("Vendor Requirement Settings saved successfully!"));
        } catch (\Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Page Settings save failed!"));
        }
    }





    //MISC.
    public function getTypesProperty()
    {
        return [
            [
                "id" => "input",
                "name" => "Input"
            ],
            [
                "id" => "option",
                "name" => "Choice/Option"
            ],
            [
                "id" => "file",
                "name" => "File"
            ],
            [
                "id" => "camera",
                "name" => "Camera"
            ],

        ];
    }

    public function getFileRulesProperty()
    {
        return [
            [
                "id" => "image",
                "name" => "Image"
            ],
            [
                "id" => "video",
                "name" => "Video"
            ],
            [
                "id" => "pdf",
                "name" => "PDF"
            ],
            [
                "id" => "zip",
                "name" => "Ziped File"
            ],

            [
                "id" => "docx",
                "name" => "Document"
            ],
        ];
    }

    public function getRequirementsRule($name)
    {
        // "type" => $type,
        //     "qty" => 1,
        //     "rules" => "",
        //     "options" => [],
        //     "title" => "",
        //     "description" => "",

        return [
            // "$name.*.rules" => "required_unless:type,option",
            "$name.*.title" => "required|string",
            "$name.*.options.*.id" => "required_if:type,option",
            "$name.*.options.*.name" => "required_if:type,option",
            "$name.*.qty" => "required_if:type,file|min:1",
        ];
    }
}