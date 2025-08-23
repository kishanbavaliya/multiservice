<div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-5 sm:p-6 bg-white shadow sm:rounded-lg">
            <h3 class="text-lg font-medium text-gray-900">
                {{ __('Two Factor Authentication') }}
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600">
                <p>
                    {{ __('Add additional security to your account using two factor authentication.') }}
                </p>
            </div>

            @if (!$enabled)
                <div class="mt-5">
                    <button type="button" wire:click="enableTwoFactorAuth" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                        {{ __('Enable Two-Factor Authentication') }}
                    </button>
                </div>
            @else
                @if (!$confirmed)
                    <div class="mt-4 p-4 bg-gray-100 rounded-lg">
                        <p class="text-sm font-medium text-gray-900">
                            {{ __('You have enabled two-factor authentication, but it has not been confirmed yet.') }}
                        </p>
                    </div>
                @else
                    <div class="mt-4 p-4 bg-green-100 rounded-lg">
                        <p class="text-sm font-medium text-green-800">
                            {{ __('Two-factor authentication is enabled and confirmed.') }}
                        </p>
                    </div>
                @endif

                <div class="mt-4">
                    @if ($showingQrCode)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                {{ __('Scan this QR code with your authenticator application:') }}
                            </p>

                            <div class="p-4 bg-white border-2 border-gray-300 rounded-lg">
                                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>
                        </div>
                    @endif

                    @if ($showingConfirmationForm || !$confirmed)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                {{ __('Enter the code from your authenticator app to confirm setup:') }}
                            </p>

                            <div class="flex items-center">
                                <input type="text" wire:model.defer="confirmationCode"
                                    class="mt-1 block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                    placeholder="Enter 6-digit code">

                                <button type="button" wire:click="confirmTwoFactorAuth"
                                    class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('Confirm') }}
                                </button>
                            </div>

                            @if ($errorMessage)
                                <p class="mt-2 text-sm text-red-600">{{ $errorMessage }}</p>
                            @endif

                            @if ($verificationSuccess)
                                <p class="mt-2 text-sm text-green-600">
                                    {{ __('Two-factor authentication has been confirmed and enabled!') }}
                                </p>
                            @endif
                        </div>
                    @endif

                    @if ($showingRecoveryCodes)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost') }}
                            </p>

                            <div class="p-4 bg-gray-100 rounded-lg">
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                        <div class="text-sm font-mono bg-white p-2 rounded">{{ $code }}</div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" wire:click="regenerateRecoveryCodes"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('Regenerate Recovery Codes') }}
                                </button>

                                <button type="button" wire:click="hideRecoveryCodes"
                                    class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                    {{ __('Hide Recovery Codes') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mt-4">
                            <button type="button" wire:click="showRecoveryCodes"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                {{ __('Show Recovery Codes') }}
                            </button>
                        </div>
                    @endif

                    <div class="mt-5">
                        <button type="button" wire:click="disableTwoFactorAuth"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
                            {{ __('Disable Two-Factor Authentication') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <x-loading />
</div>
