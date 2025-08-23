@section('title', __('2FA Verification'))
<div class="flex items-center justify-center p-2">
    <div class="m-2 w-full md:w-8/12 lg:w-6/12 mx-auto border my-8 bg-white shadow rounded block md:flex overflow-clip">
        <div class="p-4 md:p-8">
            <p class="font-bold text-2xl py-2">{{ __('2FA Verification') }}</p>
            {{-- view --}}
            <div>
                <div>
                    <div class="mb-4 text-sm text-gray-600">
                        @if (!$recovery)
                            <p>
                                {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                            </p>
                        @else
                            <p>
                                {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                            </p>
                        @endif
                    </div>

                    <x-form-errors />

                    @if (!$recovery)
                        <form wire:submit.prevent="verifyCode">
                            <div class="mt-4">
                                <label for="code" class="block font-medium text-sm text-gray-700">
                                    {{ __('Code') }}
                                </label>
                                <input id="code" type="text" inputmode="numeric"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    wire:model.defer="code" autofocus autocomplete="one-time-code">
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="button"
                                    class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer mr-4"
                                    wire:click="toggleRecoveryCode">
                                    {{ __('Use a recovery code') }}
                                </button>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('Verify') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <form wire:submit.prevent="verifyRecoveryCode">
                            <div class="mt-4">
                                <label for="recovery_code" class="block font-medium text-sm text-gray-700">
                                    {{ __('Recovery Code') }}
                                </label>
                                <input id="recovery_code" type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    wire:model.defer="recoveryCode" autofocus autocomplete="one-time-code">
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="button"
                                    class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer mr-4"
                                    wire:click="toggleRecoveryCode">
                                    {{ __('Use an authentication code') }}
                                </button>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('Verify') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="hidden md:block">
            <img aria-hidden="true" class="object-cover w-full h-full"
                src="{{ getValidValue(setting('loginImage'), asset('images/login.jpeg')) }}" alt="Office" />
        </div>
    </div>
</div>
