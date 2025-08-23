@section('title', __('Taxi Orders'))
<div>

    <x-baseview title="{{ __('Taxi Orders') }}" :showNew="true" link="{{ route('taxi.order.new') }}">
        <livewire:tables.taxi-order-table />
    </x-baseview>

    {{-- details moal --}}
    <div x-data="{ open: @entangle('showDetails') }">
        <x-modal-xl>
            <p class="text-xl font-semibold">{{ __('Order Details') }}</p>
            @if (!empty($selectedModel))
                @include('livewire.order.taxi_order_details')
            @endif
        </x-modal-xl>
    </div>

    {{-- edit modal --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update">

            <p class="text-xl font-semibold">{{ __('Edit Order') }}</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-details.item title="{{ __('Code') }}" text="#{{ $selectedModel->code ?? '' }}" />
                <x-details.item title="{{ __('Status') }}" text="{{ ucfirst(__($selectedModel->status ?? '')) }}" />
                <x-details.item title="{{ __('Payment Status') }}"
                    text="{{ ucfirst(__($selectedModel->payment_status ?? '')) }}" />
                <x-details.item title="{{ __('Payment Method') }}"
                    text="{{ __($selectedModel->payment_method->name ?? '') }}" />
            </div>
            <hr />
            <div class="gap-4">
                {{-- with initial emit --}}
                {{-- delivery boy --}}
                @can('order-assign-driver')
                    <x-label for="deliveryBoyId" title="{{ __('Driver') }}">
                        <livewire:select.edit-order-driver-select name="deliveryBoyId" placeholder="{{ __('Search') }}"
                            :searchable="true" />
                    </x-label>
                @endcan


                @hasanyrole('admin')
                    <x-select title="{{ __('Status') }}" :options="$this->order_status ?? []" name="status" :defer="false" />
                @else
                    @can('change-order-status')
                        @if (in_array($selectedModel->status ?? '', ['pending']))
                            <div class="mx-auto w-full flex justify-center items-center mt-2">
                                <div class="flex space-x-2 mx-auto rtl:flex-row-reverse">
                                    <x-buttons.plain bgColor="bg-red-500 w-6/12" wireClick="rejectOrder">
                                        <p class="mx-2">{{ __('Reject') }}</p>
                                        <x-heroicon-o-x class="w-5 h-5" />
                                    </x-buttons.plain>
                                    <x-buttons.plain bgColor="bg-green-500 w-6/12" wireClick="acceptOrder">
                                        <p class="mx-2">{{ __('Accept') }}</p>
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </x-buttons.plain>
                                </div>
                            </div>
                        @else
                            <x-select title="{{ __('Status') }}" :options="$this->order_status ?? []" name="status" :defer="false" />
                        @endif
                    @endcan
                @endhasanyrole
                @can('order-edit-payment-status')
                    <x-select title="{{ __('Payment Status') }}" :options="$this->order_payment_status ?? []" name="paymentStatus" />
                @endcan
                <div class="{{ in_array($status, ['failed', 'cancelled']) ? 'block' : 'hidden' }}">
                    <x-input title="{{ __('Reason') }}" name="note" />
                </div>
            </div>
        </x-modal>
    </div>


    {{-- payment review moal --}}
    <div x-data="{ open: @entangle('showAssign') }">
        <x-modal confirmText="{{ __('Approve') }}" action="approvePayment">

            <p class="text-xl font-semibold">{{ __('Order Payment Proof') }}</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-details.item title="{{ __('Transaction Code') }}"
                    text="{{ $selectedModel->payment->ref ?? '' }}" />
                <x-details.item title="{{ __('Status') }}" text="{{ $selectedModel->payment->status ?? '' }}" />
                <x-details.item title="{{ __('Payment Method') }}"
                    text="{{ $selectedModel->payment_method->name ?? '' }}" />
                <div>
                    <x-label title="{{ __('Transaction Photo') }}" />
                    <a href="{{ $selectedModel->payment->photo ?? '' }}" target="_blank">
                        <img src="{{ $selectedModel->payment->photo ?? '' }}" class="w-32 h-32" />
                    </a>
                </div>
            </div>
        </x-modal>
    </div>


</div>
