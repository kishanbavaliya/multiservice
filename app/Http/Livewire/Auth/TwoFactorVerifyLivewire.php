<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorVerifyLivewire extends Component
{

    public $code = '';
    public $recovery = false;
    public $recoveryCode = '';
    public $errorMessage = '';

    public function mount()
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ensure the user has 2FA enabled
        $user = Auth::user();
        if (empty($user->two_factor_secret) || empty($user->two_factor_confirmed_at)) {
            return redirect()->intended(route('dashboard'));
        }

        // Check if 2FA is already verified for this session
        if (session()->has('two_factor_confirmed')) {
            return redirect()->intended(route('dashboard'));
        }
    }

    public function toggleRecoveryCode()
    {
        $this->recovery = !$this->recovery;
        $this->code = '';
        $this->recoveryCode = '';
        $this->errorMessage = '';
    }

    public function verifyCode()
    {

        $this->validate([
            'code' => 'required|string|size:6',
        ]);

        try {
            $google2fa = new Google2FA();

            // Get the user's 2FA secret
            $secret = decrypt(Auth::user()->two_factor_secret);

            // Verify the code
            if ($google2fa->verifyKey($secret, $this->code)) {
                // Mark as verified for this session
                session()->put('two_factor_confirmed', true);

                // Redirect to the intended URL or dashboard
                return redirect()->intended(route('dashboard'));
            } else {
                $this->errorMessage = 'The provided two-factor authentication code was invalid.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred during verification.';
        }
    }

    public function verifyRecoveryCode()
    {
        $this->validate([
            'recoveryCode' => 'required|string',
        ]);

        try {
            $user = Auth::user();

            // Get recovery codes
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            // Check if the provided code exists in recovery codes
            if (in_array($this->recoveryCode, $recoveryCodes)) {
                // Remove the used recovery code
                $recoveryCodes = array_diff($recoveryCodes, [$this->recoveryCode]);

                // Update recovery codes
                $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
                $user->save();

                // Mark as verified for this session
                session()->put('two_factor_confirmed', true);

                // Redirect to the intended URL or dashboard
                return redirect()->intended(route('dashboard'));
            } else {
                $this->errorMessage = 'The recovery code is invalid.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred during verification.';
        }
    }

    public function render()
    {
        return view('livewire.auth.two-factor-verify')->layout('layouts.guest');
    }
}
