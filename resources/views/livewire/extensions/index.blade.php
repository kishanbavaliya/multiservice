@section('title', __('Extensions'))
<div x-data="{ openModal: false }">

    <x-baseview title="{{ __('Extensions') }}" showNew="{{ $showDetails }}" actionTitle="{{ __('Install') }}">
        <x-slot:newBtn>
            <x-buttons.plain title="{{ __('Install') }}" bgColor="bg-primary-500" onClick="openModal = true">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                <p>{{ __('Install') }}</p>
            </x-buttons.plain>
        </x-slot:newBtn>
        <div class="mt-10"></div>
        @if ($showDetails ?? true)

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                {{-- extensions settings --}}
                @foreach ($extensions ?? [] as $extension)
                    <x-settings-item title="{{ __($extension->name) }}" wireClick="$emit('{{ $extension->action }}')">
                        {{ svg($extension->icon ?? 'heroicon-o-puzzle')->class('w-5 h-5 mr-4') }}
                    </x-settings-item>
                @endforeach
            </div>

        @endif

        @foreach ($extensions ?? [] as $extension)
            @livewire($extension->component, [], key($extension->id))
        @endforeach


    </x-baseview>


    {{-- new form --}}
    <div x-cloak x-show="openModal">
        <x-plain-modal onCloseClick="openModal = false">
            <form wire:submit.prevent="installExtension">
                <p class="text-xl font-semibold">{{ __('Install Extension') }}</p>
                <div class="">
                    <x-input.filepond wire:model="photo" name="photo" acceptedFileTypes="['application/zip']"
                        allowImagePreview="false" allowFileSizeValidation="true" maxFileSize="1mb" />
                    <div class="h-4"></div>
                    <x-buttons.primary title="{{ __('Install') }}" />
                </div>
            </form>
        </x-plain-modal>
    </div>

</div>
