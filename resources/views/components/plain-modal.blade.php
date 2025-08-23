<div class="fixed inset-0 z-20 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div @if ($clickAway ?? false) @click.away="{{ $onCloseClick ?? '' }}" @endif
            class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            {{-- close model  --}}
            @if ($showActions ?? true)
                <button
                    class="absolute p-2 text-white bg-red-500 rounded-full hover:shadow top-4 rtl:left-4 ltr:right-4"
                    @click="{{ $onCloseClick ?? '' }}">
                    <x-heroicon-o-x class="w-5 h-5" />
                </button>
            @endif
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 {{ isRTL() ? 'text-right' : 'text-left' }}">
                {{ $slot }}
            </div>
            @if ($showActions ?? true)
                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="{{ $onCloseClick ?? '' }}">
                        {{ $cancelText ?? __('Close') }}
                    </button>
                </div>
            @endif

        </div>
    </div>
</div>
