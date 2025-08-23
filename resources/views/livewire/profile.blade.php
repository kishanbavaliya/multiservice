@section('title', __('Profile'))
<div>

    <x-baseview title="{{ __('Profile') }}">

        <x-tab.tabview class="shadow pb-10">

            <x-slot name="header">
                <x-tab.header tab="1" title="{{ __('Update Profile') }}" />
                <x-tab.header tab="2" title="{{ __('Change Password') }}" />
                <x-tab.header tab="3" title="{{ __('Two Factor Authentication') }}" />
            </x-slot>

            <x-slot name="body">
                <x-tab.body tab="1">
                    <div class="w-full lg:w-8/12">
                        <x-form action="updateProfile" :noClass="true">
                            <p class="font-semibold">{{ __('Update Profile information') }}</p>
                            <x-media-upload title="{{ __('Profile') }}" name="photo"
                                preview="{{ Auth::user()->photo }}" :photo="$photo" :photoInfo="$photoInfo"
                                types="PNG or JPEG" rules="image/*" />
                            <x-input title="{{ __('Name') }}" type="text" name="name" />
                            <x-input title="{{ __('Email') }}" type="email" name="email" />
                            {{-- <x-input title="{{ __('Phone') }}" type="tel" name="phone" /> --}}
                            <x-phoneselector />
                            <x-buttons.primary title="{{ __('Update') }}" />

                        </x-form>
                    </div>
                </x-tab.body>
                <x-tab.body tab="2">
                    {{-- Password Change --}}
                    <div class="w-full lg:w-8/12">
                        <x-form action="changePassword" :noClass="true">
                            <p class="font-semibold">{{ __('Change Password') }}</p>
                            <x-input title="{{ __('Current Password') }}" type="password" name="current_password" />
                            <x-input title="{{ __('New Password') }}" type="password" name="new_password" />
                            <x-input title="{{ __('Confirm New Password') }}" type="password"
                                name="new_password_confirmation" />
                            <x-buttons.primary title="{{ __('Update') }}" />

                        </x-form>
                    </div>
                </x-tab.body>
                <x-tab.body tab="3">
                    @production
                        <livewire:component.two-factor-auth-settings />
                    @else
                        <div class="text-center text-red-500 p-8">
                            <p>2FA {{ __('is only available in production mode') }}</p>
                        </div>
                    @endproduction
                </x-tab.body>
            </x-slot>

        </x-tab.tabview>
    </x-baseview>


</div>
@include('layouts.partials.phoneselector')
