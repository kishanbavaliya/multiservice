@section('title', __('Product Favourites'))
<div>

    <x-baseview title="{{ __('Product Favourites') }}">
        <livewire:tables.favourite-table />
    </x-baseview>

</div>
