<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if the user has 2FA enabled and has confirmed it
        if (
            $user &&
            !empty($user->two_factor_secret) &&
            !empty($user->two_factor_confirmed_at) &&
            !session()->has('two_factor_confirmed')
        ) {
            // Store the intended URL to redirect after 2FA verification
            if (!$request->is('two-factor-verify')) {
                session()->put('url.intended', $request->fullUrl());
            }

            // Redirect to the 2FA verification page
            return redirect()->route('two-factor.verify');
        }

        return $next($request);
    }
}
