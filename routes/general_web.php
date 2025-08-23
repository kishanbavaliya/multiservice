<?php

use App\Http\Controllers\API\AuthRedirectController;
use App\Http\Livewire\InAppSupportPageLivewire;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CMSPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web']], function () {


Route::get('/order:assign', function () {
    Artisan::call('order:assign');

     $output = Artisan::output();

    // Return as JSON or plain text (depending on your needs)
    return response()->json([
        'status' => 'success',
        'output' => $output,
    ]);
});
    //redirect api to authenticated web route
    Route::get('/auth/redirect', [AuthRedirectController::class, 'index'])->name('web.auth.redirect');

    // Pages
    Route::get('privacy/policy', function () {
        $content = view('layouts.includes.privacy')->render();
        $title = __("Privacy Policy");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('privacy');

    Route::get('pages/contact', function () {
        $content = view('layouts.includes.contact')->render();
        $title = __("Contact Info");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('contact');

    Route::get('pages/terms', function () {
        $content = view('layouts.includes.terms')->render();
        $title = __("Terms & Condition");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('terms');

    Route::get('pages/shipping/terms', function () {
        $content = view('layouts.includes.shipping-terms')->render();
        $title = __("Delivery/Shipping Policy");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('shipping.terms');

    Route::get('pages/refund/terms', function () {
        $content = view('layouts.includes.refund-terms')->render();
        $title = __("Refund Policy");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('refund.terms');

    Route::get('pages/cancel/terms', function () {
        $content = view('layouts.includes.cancel-terms')->render();
        $title = __("Cancellation Policy");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('cancel.terms');


    Route::get('pages/payment/terms', function () {
        $content = view('layouts.includes.payment-terms')->render();
        $title = __("Payment Terms & Condition");
        return view('layouts.includes.base', compact('content', 'title'));
    })->name('payment.terms');
    //
    Route::get('support/chat', InAppSupportPageLivewire::class)->name('support.chat');
    Route::get('cms/{slug}', [CMSPageController::class, 'index'])->name('cms.page');
});
