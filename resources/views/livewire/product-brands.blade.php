@section('title', __('Item Brands'))
<div>

    <x-baseview title="{{ __('Item Brands') }}" :showNew="auth()->user()->can('manage-product-brands') ?? false">
        <livewire:tables.product-brands-table />
    </x-baseview>

    {{-- new form --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Create Item Brand') }}</p>
            <x-input title="{{ __('Name') }}" name="name" />
        </x-modal>
    </div>

    {{-- update form --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update">
            <p class="text-xl font-semibold">{{ __('Update Item Brand') }}</p>
            <x-input title="{{ __('Name') }}" name="name" />
        </x-modal>
    </div>

    {{-- details modal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal-lg>

            <p class="text-xl font-semibold">{{ $selectedModel->name ?? '' }}
                {{ __('Products') }}</p>
            @if (empty($selectedModel->products ?? []) || count($selectedModel->products ?? []) <= 0)
                <p class="text-sm p-4">{{ __('No product/item attached to brand') }}</p>
            @else
                <div class='grid grid-cols-1 my-4 md:grid-cols-2 lg:grid-cols-3'>
                    @foreach ($selectedModel->products ?? [] as $key => $product)
                        <div><b>{{ $key + 1 }}.</b> {{ $product['name'] }}</div>
                    @endforeach
                </div>
            @endif

        </x-modal-lg>
    </div>


</div>
