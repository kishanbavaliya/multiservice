@section('title', __('Vendor Favourites'))
<div>

    <x-baseview title="{{ __('Vendor Favourites') }}">
        <livewire:tables.favourite-vendor-table />
    </x-baseview>

</div>
