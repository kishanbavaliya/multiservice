<?php

use App\Http\Controllers\API\AppSettingsController;
use App\Http\Controllers\API\UserFirebaseController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CancellationReasonController;
use App\Http\Controllers\API\AccountManagementController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\PartnerController;
use App\Http\Controllers\API\VendorTypeController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\SearchDataController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\DeliveryAddressController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\TrackOrderController;
use App\Http\Controllers\API\PackageOrderController;
use App\Http\Controllers\API\RegularOrderController;
use App\Http\Controllers\API\PackageTypeController;
use App\Http\Controllers\API\ChatNotificationController;
use App\Http\Controllers\API\DriverTypeController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\EarningController;
use App\Http\Controllers\API\ExternalRedirectController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\OrderPaymentCallbackController;
use App\Http\Controllers\API\WebhookPaymentCallbackController;
use App\Http\Controllers\API\OTPController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\RestaurantCategoryController;
use App\Http\Controllers\API\RestaurantSubcategoryController;
use App\Http\Controllers\API\RestaurantProductController;
use App\Http\Controllers\API\RestaurantBannerController;
use App\Http\Controllers\API\RestaurantServingSizeController;
use App\Http\Controllers\API\RestaurantModifierController;
use App\Http\Controllers\API\RestaurantModifierGroupController;
use App\Http\Controllers\API\FoodAppController;
use App\Http\Controllers\API\ProductReviewController;
use App\Http\Controllers\API\ProductReviewSummaryController;
use App\Http\Controllers\API\VendorPackageTypePricingController;
use App\Http\Controllers\API\VendorServiceController;
use App\Http\Controllers\API\VehicleTypeController;
use App\Http\Controllers\API\TaxiOrderController;
use App\Http\Controllers\API\TaxiDriverOrderController;
use App\Http\Controllers\API\DriverLocationSyncController;
use App\Http\Controllers\API\PaymentAccountController;
use App\Http\Controllers\API\PayoutController;
use App\Http\Controllers\API\WalletTransferController;
use App\Http\Controllers\API\LoyaltyPointController;
use App\Http\Controllers\API\GeocoderController;
use App\Http\Controllers\API\ProductMiscController;
use App\Http\Controllers\API\FlashSaleController;
use App\Http\Controllers\API\OnboardingController;
use App\Http\Controllers\API\FaqController;
use App\Http\Controllers\API\SignedMediaController;
use App\Http\Controllers\API\MyVendorController;
use App\Http\Controllers\API\VendorReportController;
use App\Http\Controllers\API\DriverReportController;
use App\Http\Controllers\API\DocumentRequestController;
use App\Http\Controllers\API\FavouriteVendorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\FavoriteLocationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//CRON Job
Route::get('/cron/job', function (Request $request) {
    //
    $appKey  = env("CRON_JOB_KEY", "");
    $urlAppKey = str_ireplace(" ", "+", $request->key);
    //
    if ($appKey != $urlAppKey) {
        return response()->json([
            "message" => "Unauthorized",
        ], 401);
    }

    $artisan = \Artisan::call("schedule:run");
    $output = \Artisan::output();
    return response()->json([
        "message" => "schedule runed",
        "output" => $output
    ]);
})->name('cron.job');


Route::get('/livewire/publish', function (Request $request) {
    $commandArray = ["php", "artisan", "livewire:publish", "--assets"];
    $commandRunner = new \Symfony\Component\Process\Process($commandArray);
    $commandRunner->setWorkingDirectory(base_path());
    $commandRunner->run();

    if (!$commandRunner->isSuccessful()) {
        throw new \Symfony\Component\Process\Exception\ProcessFailedException($commandRunner);
    }

    return response()->json([
        "message" => "publish runed",
    ]);
});


//App settings
Route::get('/app/settings', [AppSettingsController::class, 'index']);
Route::get('/app/onboarding', [OnboardingController::class, 'index']);
Route::get('/app/faqs', [FaqController::class, 'index']);
Route::get('/cancellation/reasons', [CancellationReasonController::class, 'index']);

// Auth
Route::group(['middleware' => ['throttle:otp']], function () {
    //otps
    Route::post('otp/send', [OTPController::class, 'sendOTP'])->name('otp.send');
    Route::post('otp/verify', [OTPController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('otp/firebase/verify', [OTPController::class, 'verifyFirebaseToken'])->name('otp.firebase.verify');
});

Route::post('login', [AuthController::class, 'login']);
Route::post('vendor/register', [PartnerController::class, 'vendor']);
Route::post('driver/register', [PartnerController::class, 'driver']);
Route::post('login/qrcode', [AuthController::class, 'qrlogin']);
Route::post('social/login', [SocialLoginController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('verify/phone', [AuthController::class, 'verifyPhoneAccount']);
Route::post('password/reset/init', [AuthController::class, 'passwordReset']);
// Route::get('password/update/{code}/{email}', PasswordResetLivewire::class)->name('password.reset.link');

Route::get('tags', [TagController::class, "index"]);
Route::get('categories', [CategoryController::class, "index"]);
Route::get('banners', [BannerController::class, "index"]);
Route::apiResource('products', ProductController::class);
Route::get('product/review/summary', [ProductReviewSummaryController::class, 'index']);
Route::get('product/frequent', [ProductMiscController::class, 'frequent']);
Route::apiResource('services', ServiceController::class);
Route::get('service/durations', [ServiceController::class, 'durations']);
Route::apiResource('vendors', VendorController::class);
Route::get('vendor/reviews', [ReviewController::class, 'index']);

// Restaurant Management
Route::prefix('restaurants')->group(function () {
    Route::get('/', [RestaurantController::class, 'index']);
    Route::get('/trending', [RestaurantController::class, 'getTopTrendingRestaurants']);
    Route::get('/best-sellers', [RestaurantController::class, 'getBestSellersRestaurants']);
    Route::get('/nearby', [RestaurantController::class, 'getNearbyRestaurants']);
    Route::get('/search', [RestaurantController::class, 'search']);
    Route::get('/stats', [RestaurantController::class, 'getStats']);
    Route::get('/cuisine/{cuisineType}', [RestaurantController::class, 'getByCuisineType']);
    Route::get('/city/{city}', [RestaurantController::class, 'getByCity']);
    Route::get('/{id}', [RestaurantController::class, 'show']);
    Route::get('/{id}/menu', [FoodAppController::class, 'getRestaurantMenu']);
});

// Test route to verify controller works
Route::get('/test-restaurant', [RestaurantController::class, 'index']);

// Food App Routes
Route::get('/get-offers', [RestaurantController::class, 'getOffers']);
Route::get('/categories', [FoodAppController::class, 'getCategories']);
Route::get('/categories/{id}/restaurants', [FoodAppController::class, 'getRestaurantsByCategory']);

// Restaurant Category Management
Route::prefix('restaurant-categories')->group(function () {
    Route::get('/', [RestaurantCategoryController::class, 'index']);
    Route::post('/', [RestaurantCategoryController::class, 'store']);
    Route::get('/{id}', [RestaurantCategoryController::class, 'show']);
    Route::put('/{id}', [RestaurantCategoryController::class, 'update']);
    Route::delete('/{id}', [RestaurantCategoryController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantCategoryController::class, 'getByRestaurant']);
    Route::patch('/{id}/toggle-status', [RestaurantCategoryController::class, 'toggleStatus']);
    Route::patch('/{id}/toggle-featured', [RestaurantCategoryController::class, 'toggleFeatured']);
    Route::get('/search', [RestaurantCategoryController::class, 'search']);
    Route::get('/stats', [RestaurantCategoryController::class, 'getStats']);
});

// Restaurant Subcategory Management
Route::prefix('restaurant-subcategories')->group(function () {
    Route::get('/', [RestaurantSubcategoryController::class, 'index']);
    Route::post('/', [RestaurantSubcategoryController::class, 'store']);
    Route::get('/{id}', [RestaurantSubcategoryController::class, 'show']);
    Route::put('/{id}', [RestaurantSubcategoryController::class, 'update']);
    Route::delete('/{id}', [RestaurantSubcategoryController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantSubcategoryController::class, 'getByRestaurant']);
    Route::get('/category/{categoryId}', [RestaurantSubcategoryController::class, 'getByCategory']);
    Route::patch('/{id}/toggle-status', [RestaurantSubcategoryController::class, 'toggleStatus']);
    Route::patch('/{id}/toggle-featured', [RestaurantSubcategoryController::class, 'toggleFeatured']);
});

// Restaurant Product Management
Route::prefix('restaurant-products')->group(function () {
    Route::get('/', [RestaurantProductController::class, 'index']);
    Route::post('/', [RestaurantProductController::class, 'store']);
    Route::get('/{id}', [RestaurantProductController::class, 'show']);
    Route::put('/{id}', [RestaurantProductController::class, 'update']);
    Route::delete('/{id}', [RestaurantProductController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantProductController::class, 'getByRestaurant']);
    Route::get('/category/{categoryId}', [RestaurantProductController::class, 'getByCategory']);
    Route::get('/subcategory/{subcategoryId}', [RestaurantProductController::class, 'getBySubcategory']);
    Route::patch('/{id}/toggle-availability', [RestaurantProductController::class, 'toggleAvailability']);
    Route::patch('/{id}/toggle-featured', [RestaurantProductController::class, 'toggleFeatured']);
    Route::get('/featured', [RestaurantProductController::class, 'getFeatured']);
    Route::get('/popular', [RestaurantProductController::class, 'getPopular']);
    Route::get('/search', [RestaurantProductController::class, 'search']);
});

// Restaurant Banner Management
Route::prefix('restaurant-banners')->group(function () {
    Route::get('/', [RestaurantBannerController::class, 'index']);
    Route::post('/', [RestaurantBannerController::class, 'store']);
    Route::get('/{id}', [RestaurantBannerController::class, 'show']);
    Route::put('/{id}', [RestaurantBannerController::class, 'update']);
    Route::delete('/{id}', [RestaurantBannerController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantBannerController::class, 'getByRestaurant']);
    Route::get('/type/{type}', [RestaurantBannerController::class, 'getByType']);
    Route::get('/position/{position}', [RestaurantBannerController::class, 'getByPosition']);
    Route::patch('/{id}/toggle-status', [RestaurantBannerController::class, 'toggleStatus']);
    Route::post('/{id}/increment-click', [RestaurantBannerController::class, 'incrementClick']);
    Route::post('/{id}/increment-impression', [RestaurantBannerController::class, 'incrementImpression']);
    Route::get('/{id}/stats', [RestaurantBannerController::class, 'getStats']);
});

// Restaurant Serving Size Management
Route::prefix('restaurant-serving-sizes')->group(function () {
    Route::get('/', [RestaurantServingSizeController::class, 'index']);
    Route::post('/', [RestaurantServingSizeController::class, 'store']);
    Route::get('/{id}', [RestaurantServingSizeController::class, 'show']);
    Route::put('/{id}', [RestaurantServingSizeController::class, 'update']);
    Route::delete('/{id}', [RestaurantServingSizeController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantServingSizeController::class, 'getByRestaurant']);
    Route::get('/global', [RestaurantServingSizeController::class, 'getGlobal']);
    Route::get('/available/{restaurantId}', [RestaurantServingSizeController::class, 'getAvailableForRestaurant']);
    Route::patch('/{id}/toggle-status', [RestaurantServingSizeController::class, 'toggleStatus']);
    Route::get('/search', [RestaurantServingSizeController::class, 'search']);
    Route::get('/stats', [RestaurantServingSizeController::class, 'getStats']);
});

// Restaurant Modifier Management
Route::prefix('restaurant-modifiers')->group(function () {
    Route::get('/', [RestaurantModifierController::class, 'index']);
    Route::post('/', [RestaurantModifierController::class, 'store']);
    Route::get('/{id}', [RestaurantModifierController::class, 'show']);
    Route::put('/{id}', [RestaurantModifierController::class, 'update']);
    Route::delete('/{id}', [RestaurantModifierController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantModifierController::class, 'getByRestaurant']);
    Route::patch('/{id}/toggle-status', [RestaurantModifierController::class, 'toggleStatus']);
    Route::get('/search', [RestaurantModifierController::class, 'search']);
    Route::get('/stats', [RestaurantModifierController::class, 'getStats']);
});

// Restaurant Modifier Group Management
Route::prefix('restaurant-modifier-groups')->group(function () {
    Route::get('/', [RestaurantModifierGroupController::class, 'index']);
    Route::post('/', [RestaurantModifierGroupController::class, 'store']);
    Route::get('/{id}', [RestaurantModifierGroupController::class, 'show']);
    Route::put('/{id}', [RestaurantModifierGroupController::class, 'update']);
    Route::delete('/{id}', [RestaurantModifierGroupController::class, 'destroy']);
    Route::get('/restaurant/{restaurantId}', [RestaurantModifierGroupController::class, 'getByRestaurant']);
    Route::get('/type/{type}', [RestaurantModifierGroupController::class, 'getBySelectionType']);
    Route::patch('/{id}/toggle-status', [RestaurantModifierGroupController::class, 'toggleStatus']);
    Route::get('/search', [RestaurantModifierGroupController::class, 'search']);
    Route::get('/stats', [RestaurantModifierGroupController::class, 'getStats']);
    Route::get('/restaurant/{restaurantId}/available-modifiers', [RestaurantModifierGroupController::class, 'getAvailableModifiers']);
});

Route::get('/menu-items/{id}', [FoodAppController::class, 'getMenuItemDetails']);


//
Route::get('product/reviews', [ProductReviewController::class, 'index']);
Route::apiResource('vendor/types', VendorTypeController::class);
Route::get('coupons/{code}', [CouponController::class, 'show']);
Route::get('coupons', [CouponController::class, 'index']);
Route::get('coupons/details/{id}', [CouponController::class, 'details']);
Route::get('search', [SearchController::class, 'index']);
Route::get('search/data', [SearchDataController::class, 'index']);
Route::get('flash/sales', [FlashSaleController::class, 'index']);

//package delivery
Route::get('package/types', [PackageTypeController::class, 'index']);
Route::get('partner/vehicle/types', [PartnerController::class, 'vehicleTypes']);
Route::get('partner/car/makes', [PartnerController::class, 'carMakes']);
Route::get('partner/car/models', [PartnerController::class, 'carModels']);
//
Route::post('order/payment/callback', [OrderPaymentCallbackController::class, 'order'])->name('api.payment.callback');
Route::post('wallet/topup/callback', [OrderPaymentCallbackController::class, 'wallet'])->name('api.wallet.topup.callback');
Route::post('subscription/callback', [OrderPaymentCallbackController::class, 'subscription'])->name('api.subscription.callback');
Route::any('external/payment/callback/{hash}', [WebhookPaymentCallbackController::class, 'index'])->name('api.payment.webhook');
Route::apiResource('payment/methods', PaymentMethodController::class)->only('index');

//geocoding
Route::get('geocoder/{type}', [GeocoderController::class, 'index']);
Route::get('geocoder/2/reserve', [GeocoderController::class, 'newReverse']);
Route::get('geocoder/place/details', [GeocoderController::class, 'reverseDetails']);
Route::get('/download/digital/files/{id}', [SignedMediaController::class, 'download'])->name('digital.download');
//Server run external apis
Route::apiResource('external/redirect', ExternalRedirectController::class)->only('index');
Route::get('external/web/redirect', [ExternalRedirectController::class, 'webRedirect']);




Route::group(['middleware' => ['auth:sanctum', "user.active.check"]], function () {

    Route::apiResource('favourites', FavouriteController::class);
    Route::apiResource('favourite/vendors', FavouriteVendorController::class);
    Route::post('device/token/sync', [UserFirebaseController::class, 'syncTokens']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('my/profile', [UserController::class, 'myProfile']);
    Route::put('profile/update', [AuthController::class, 'profileUpdate']);
    Route::put('profile/password/update', [AuthController::class, 'changePassword']);
    Route::delete('account/delete', [AccountManagementController::class, 'delete']);
    Route::apiResource('delivery/addresses', DeliveryAddressController::class);
    Route::apiResource('orders', OrderController::class)->only('index', 'show', 'update');
    Route::middleware('throttle.order.api')->group(function () {
        Route::post('orders', [OrderController::class, 'store']);
    });

    Route::post('/track/order', [TrackOrderController::class, "track"]);
    Route::apiResource('rating', RatingController::class)->only('store');
    Route::post('product/reviews', [ProductReviewController::class, 'store']);
    //package delivery
    Route::post('package/order/vendors', [PackageOrderController::class, 'fetchVendors']);
    Route::get('package/order/summary', [PackageOrderController::class, 'summary']);
    Route::get('general/order/delivery/fee/summary', [RegularOrderController::class, 'deliveryFeeSummary']);
    Route::post('general/order/summary', [RegularOrderController::class, 'summary']);
    Route::post('service/order/summary', [RegularOrderController::class, 'serviceSummary']);
    Route::post('package/order/stop/verify/{id}', [PackageOrderController::class, 'verifyOrderStop']);
    //
    Route::post('chat/notification', [ChatNotificationController::class, 'send']);

    //earning
    Route::get('earning/user', [EarningController::class, 'user']);
    Route::get('earning/vendor', [EarningController::class, 'vendor']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('vendor/{id}/details', [VendorController::class, 'fullDeatils']);

    //wallets
    Route::get('wallet/balance', [WalletController::class, 'index']);
    Route::post('wallet/topup', [WalletController::class, 'topup']);
    Route::get('wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('wallet/transfer', [WalletController::class, 'transferBalance']);

    //taxi booking
    Route::get('vehicle/types', [VehicleTypeController::class, 'index']);
    Route::get('vehicle/types/pricing', [VehicleTypeController::class, 'calculateFee']);
    Route::post('taxi/book/order', [TaxiOrderController::class, 'book']);
    Route::get('taxi/current/order', [TaxiOrderController::class, 'current']);
    Route::get('taxi/rateable/order', [TaxiOrderController::class, 'currentRateable']);
    Route::get('taxi/order/cancel/{id}', [TaxiOrderController::class, 'cancelOrder']);
    Route::get('taxi/driver/info/{id}', [TaxiOrderController::class, 'driverInfo']);
    Route::get('taxi/location/available', [TaxiOrderController::class, 'location_available']);
    Route::get('taxi/location/history', [TaxiOrderController::class, 'location_history']);
    // driver section
    Route::group(['middleware' => ['role:driver']], function () {
        //driver location updating
        Route::post('driver/location/sync', [DriverLocationSyncController::class, 'store']);
        Route::post('taxi/order/asignment/reject', [TaxiDriverOrderController::class, 'driverRejectAssignment']);
        Route::post('taxi/order/asignment/accept', [TaxiDriverOrderController::class, 'driverAcceptAssignment']);
    });

    //Payments
    Route::apiResource('payment/accounts', PaymentAccountController::class);
    Route::apiResource('payouts/request', PayoutController::class)->only('store');
    //Wallet transfer
    Route::get('wallet/my/address', [WalletTransferController::class, 'walletAddress']);
    Route::get('wallet/address/search', [WalletTransferController::class, 'walletAddressSearch']);
    Route::post('wallet/address/transfer', [WalletTransferController::class, 'walletTransfer']);
    //loyalty point
    Route::get('loyalty/point/my', [LoyaltyPointController::class, 'current']);
    Route::get('loyalty/point/my/report', [LoyaltyPointController::class, 'report']);
    Route::post('loyalty/point/my/withdraw', [LoyaltyPointController::class, 'withdraw']);

    //
    Route::group(['middleware' => ['role:manager']], function () {

        Route::post('availability/vendor/{id}', [VendorController::class, 'toggleVendorAvailablity']);
        Route::apiResource('/vendor/package/pricing', VendorPackageTypePricingController::class);
        //separate the my services into get, post, put, delete
        Route::get('/my/services', [VendorServiceController::class, 'index']);
        Route::post('/my/services', [VendorServiceController::class, 'store']);
        Route::put('/my/services/{id}', [VendorServiceController::class, 'update']);
        Route::delete('/my/services/{id}', [VendorServiceController::class, 'destroy']);

        //
        Route::get('/my/vendors', [MyVendorController::class, 'index']);
        Route::post('/switch/vendor', [MyVendorController::class, 'switchVendor']);
        Route::get('/my/vendor/sales/report', [VendorReportController::class, 'sales']);
        Route::get('/my/vendor/earnings/report', [VendorReportController::class, 'earnings']);

        //document submission
        Route::post('/my/vendor/document/request/submission', [DocumentRequestController::class, 'vendor']);
    });


    //driver
    Route::group(['middleware' => ['role:driver']], function () {
        Route::post('driver/type/switch', [DriverTypeController::class, 'switchType']);
        Route::post('driver/vehicle/register', [DriverTypeController::class, 'registerVehicle']);
        Route::get('driver/vehicles', [DriverTypeController::class, 'vehicles']);
        Route::post('driver/vehicle/{id}/activate', [DriverTypeController::class, 'activateVehicle']);
        // Route::post('driver/order/accept', [DriverTypeController::class, 'activateVehicle']);
        // Route::post('driver/order/reject', [DriverTypeController::class, 'activateVehicle']);
        //document submission
        Route::post('/driver/document/request/submission', [DocumentRequestController::class, 'driver']);
        //summary report
        Route::get('/driver/payouts/report', [DriverReportController::class, 'payouts']);
        Route::get('/driver/earnings/report', [DriverReportController::class, 'earnings']);
    });

    // Favorite Location
    Route::apiResource('favorites', FavoriteLocationController::class);

});