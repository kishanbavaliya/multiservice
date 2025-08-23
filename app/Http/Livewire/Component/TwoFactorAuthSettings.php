<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthSettings extends Component
{
    public $showingQrCode = false;
    public $showingRecoveryCodes = false;
    public $showingConfirmationForm = false;
    public $confirmationCode;
    public $verificationSuccess = false;
    public $errorMessage = '';

    public function mount()
    {
        if (
            Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') &&
            is_null(Auth::user()->two_factor_confirmed_at)
        ) {
            $this->showingConfirmationForm = true;
        }
    }

    public function enableTwoFactorAuth(EnableTwoFactorAuthentication $enableTwoFactorAuthentication)
    {
        $enableTwoFactorAuthentication(Auth::user());

        $this->showingQrCode = true;
        $this->showingRecoveryCodes = true;
    }

    public function confirmTwoFactorAuth(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication)
    {
        try {
            $confirmTwoFactorAuthentication(Auth::user(), $this->confirmationCode);

            $this->verificationSuccess = true;
            $this->showingConfirmationForm = false;
            $this->errorMessage = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'The provided two-factor authentication code was invalid.';
        }
    }

    public function disableTwoFactorAuth(DisableTwoFactorAuthentication $disableTwoFactorAuthentication)
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->showingQrCode = false;
        $this->showingRecoveryCodes = false;
        $this->verificationSuccess = false;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes)
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->showingRecoveryCodes = true;
    }

    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;
    }

    public function hideRecoveryCodes()
    {
        $this->showingRecoveryCodes = false;
    }

    public function render()
    {
        return view('livewire.component.two-factor-auth-settings', [
            'enabled' => !empty(Auth::user()->two_factor_secret),
            'confirmed' => !is_null(Auth::user()->two_factor_confirmed_at),
        ]);
    }
}
