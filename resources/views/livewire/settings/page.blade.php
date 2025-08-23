<div wire:init="setupEditors">

    <x-tab.tabview class="shadow pb-10">

        <x-slot name="header">
            <x-tab.header tab="1" title="{{ __('Old Requirement') }}" />
            <x-tab.header tab="2" title="{{ __('New Requirement') }}" />
        </x-slot>

        <x-slot name="body">
            <x-tab.body tab="1">
                <x-form action="savePageSettings" :noClass="true">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div>
                                <x-input.summernote name="driverDocumentInstructions"
                                    title="{{ __('Driver Verification Document Instructions') }}"
                                    id="driverDocumentInstructionsEdit" />
                            </div>
                            <x-input title="{{ __('Max Driver Selectable Documents') }}" name="driverDocumentCount"
                                type="number" />
                        </div>
                        <hr class="my-12 block md:hidden" />
                        {{--  --}}
                        <div>
                            <div>
                                <x-input.summernote name="vendorDocumentInstructions"
                                    title="{{ __('Vendor Verification Document Instructions') }}"
                                    id="vendorDocumentInstructionsEdit" />
                            </div>
                            <x-input title="{{ __('Max Vendor Selectable Documents') }}" name="vendorDocumentCount"
                                type="number" />
                        </div>
                    </div>

                    <x-buttons.primary title="{{ __('Save Changes') }}" />
                </x-form>
            </x-tab.body>

            <x-tab.body tab="2">
                {{-- needed --}}
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        //
                        $mainRequirements = [
                            [
                                'title' => 'Customer Requirements',
                                'action' => 'saveCustomerRequirements',
                                'requirements' => $customerRequirements,
                                'variable' => 'customerRequirements',
                            ],
                            [
                                'title' => 'Driver Requirements',
                                'action' => 'saveDriverRequirements',
                                'requirements' => $driverRequirements,
                                'variable' => 'driverRequirements',
                            ],
                            [
                                'title' => 'Vendor Requirements',
                                'action' => 'saveVendorRequirements',
                                'requirements' => $vendorRequirements,
                                'variable' => 'vendorRequirements',
                            ],
                        ];
                    @endphp
                    @foreach ($mainRequirements as $requirementIndex => $mainRequirement)
                        @php
                            $variable = $mainRequirement['variable'];
                            $sectionTitle = $mainRequirement['title'];
                        @endphp
                        {{-- variable requirements --}}
                        <div class="p-2 border rounded-sm border-dashed space-y-1">
                            <p class="font-bold text-xl my-2 border-b p-2">{{ $sectionTitle }}</p>
                            @foreach ($mainRequirement['requirements'] ?? [] as $key => $customerRequirement)
                                {{-- get type --}}
                                <div class="border border-dashed p-2">
                                    <div class="flex items-center">
                                        <p class="font-semibold text-lg my-2 w-full">
                                            {{ \Str::ucfirst($customerRequirement['type']) }}</p>
                                        {{-- remove button --}}
                                        <div class="w-2/12 mx-auto mt-2">
                                            <x-buttons.plain title="{{ __('Remove') }}" bgColor="bg-red-500"
                                                wireClick="removeRequirement({{ $requirementIndex }},'{{ $key }}')">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </x-buttons.plain>
                                        </div>
                                    </div>
                                    {{-- input --}}
                                    <x-input name="{{ $variable }}.{{ $key }}.title"
                                        title="{{ __('Title') }}" />
                                    @if ($customerRequirement['type'] == 'file')
                                        <div class="flex justify-start space-x-2">
                                            <x-input name="{{ $variable }}.{{ $key }}.qty"
                                                title="{{ __('Quantity') }}" />
                                            <x-select name="{{ $variable }}.{{ $key }}.rules"
                                                title="{{ __('Rules/Requirement') }}" :options="$this->file_rules ?? []"
                                                :noPreSelect="true" />

                                        </div>
                                        <x-textarea h="h-24"
                                            name="{{ $variable }}.{{ $key }}.description"
                                            title="{{ __('Description/Instruction') }}" />
                                    @elseif($customerRequirement['type'] == 'camera')
                                        <x-textarea h="h-24"
                                            name="{{ $variable }}.{{ $key }}.description"
                                            title="{{ __('Description/Instruction') }}" />
                                    @elseif($customerRequirement['type'] == 'option')
                                        <div class="border border-dashed p-2 m-2">
                                            <p class="font-semibold my-2">{{ __('Options') }}</p>
                                            @foreach ($customerRequirement['options'] ?? [] as $optionKey => $customerRequirementOption)
                                                <div class="flex space-x-2 items-end">
                                                    <div class="w-full flex justify-evenly space-x-2">
                                                        <x-input
                                                            name="{{ $variable }}.{{ $key }}.options.{{ $optionKey }}.name"
                                                            title="{{ __('Name') }}" />
                                                        <x-input
                                                            name="{{ $variable }}.{{ $key }}.options.{{ $optionKey }}.id"
                                                            title="{{ __('Value') }}" />
                                                    </div>
                                                    {{-- remove button --}}
                                                    <div class="w-2/12 mx-auto mt-2">
                                                        <x-buttons.plain title="{{ __('Remove') }}"
                                                            bgColor="bg-red-500"
                                                            wireClick="removeRequirementOption({{ $requirementIndex }},'{{ $key }}', '{{ $optionKey }}')">
                                                            <x-heroicon-o-trash class="w-5 h-5" />
                                                        </x-buttons.plain>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="w-full mx-auto mt-2">
                                                <x-buttons.primary title="{{ __('Add') }}" :noMargin="true"
                                                    type="button"
                                                    wireClick="newRequirementOption({{ $requirementIndex }},'{{ $key }}')" />
                                            </div>
                                        </div>
                                    @else
                                        <x-input name="{{ $variable }}.{{ $key }}.rules"
                                            title="{{ __('Rules/Requirement') }}" />
                                    @endif
                                </div>
                            @endforeach
                            <div class="space-y-1 border-t border-dashed p-2 my-2">
                                <p>{{ __('New Entry') }}</p>
                                <div class="w-full mx-auto flex space-x-2">
                                    <div class="w-10/12">
                                        <x-select :options="$this->types ?? []" name="new_type_id" />
                                    </div>
                                    <div class="w-2/12">
                                        <x-buttons.primary title="{{ __('Add') }}" :noMargin="true" type="button"
                                            wireClick="newRequirement({{ $requirementIndex }})" />
                                    </div>
                                </div>
                            </div>
                            {{-- save button --}}
                            <x-buttons.primary type="button" title="{{ __('Save Changes') }}"
                                wireClick="{{ $mainRequirement['action'] }}" />
                            {{--  --}}
                            @if ($errors->get("$variable.*"))
                                <x-form-errors />
                            @endif
                        </div>
                    @endforeach
                </div>

            </x-tab.body>
        </x-slot>

    </x-tab.tabview>


</div>
