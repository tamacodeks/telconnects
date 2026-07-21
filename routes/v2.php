<?php

use App\Http\Controllers\App\OrderController;
use App\Http\Controllers\App\TransactionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MyService\CallingCard\RateTableController;
use App\Http\Controllers\Service\V2\CallingCardV2Controller;
use App\Http\Controllers\Service\V2\TamaBusV2Controller;
use App\Http\Controllers\Service\V2\TamaTopupV2Controller;
use App\Http\Controllers\Service\V2\TamaTopupV2DingController;
use App\Http\Controllers\Service\V2\TamaTopupV2ReloadlyController;
use App\Http\Controllers\Service\V2\TamaTopupV2TellusController;
use App\Http\Controllers\Service\V2\TamaTopupV2TransferController;
use App\Http\Controllers\V2\Auth\LoginController as V2LoginController;
use App\Http\Controllers\V2\ApplicationSettingsController as V2ApplicationSettingsController;
use App\Http\Controllers\V2\DashboardController as V2DashboardController;
use App\Http\Controllers\V2\FailedTransactionController as V2FailedTransactionController;
use App\Http\Controllers\V2\PaymentController as V2PaymentController;
use App\Http\Controllers\V2\PinHistoryController as V2PinHistoryController;
use App\Http\Controllers\V2\ProfileController as V2ProfileController;

/*
|--------------------------------------------------------------------------
| V2 Routes
|--------------------------------------------------------------------------
|
| This file contains the V2 auth, dashboard, service, and history routes.
| Legacy routes stay in web.php.
|
*/

$disabledLegacyAuthEndpoint = function () {
    abort(410, 'Legacy authentication endpoint disabled.');
};

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['fw-block-blacklisted']], function () use ($disabledLegacyAuthEndpoint) {
    Route::prefix('v2')->group(function () {
        Route::get('/', [LoginController::class, 'index'])->name('authentication');

        Route::post('secure_login', [V2LoginController::class, 'secureLoginValidate'])
            ->middleware('throttle:10,1')
            ->name('secure.login.validate');

        Route::post('resend-otp', [V2LoginController::class, 'resendOtp'])
            ->middleware('throttle:3,5')
            ->name('resend.otp');

        Route::post('verify-otp', [V2LoginController::class, 'validateOtp'])
            ->middleware('throttle:10,10')
            ->name('verify.otp');

        Route::post('verify-totp', [V2LoginController::class, 'verifyTotp'])
            ->middleware('throttle:10,10')
            ->name('verify.totp');
    });

    Route::get('/', [V2LoginController::class, 'showLoginForm'])->name('login');
    Route::get('login', [V2LoginController::class, 'showLoginForm']);

    Route::get('twostepauthentication', function () {
        return redirect()->route('login');
    });

    Route::post('twostepauthentication', [V2LoginController::class, 'secureLoginValidate'])
        ->middleware('throttle:10,1');

    Route::post('generate_otp', $disabledLegacyAuthEndpoint)->middleware('throttle:3,5');
    Route::post('check_otp', $disabledLegacyAuthEndpoint)->middleware('throttle:10,10');
    Route::post('resend_otp', $disabledLegacyAuthEndpoint)->middleware('throttle:3,5');

    Route::post('login', [V2LoginController::class, 'secureLoginValidate'])
        ->middleware('throttle:10,1');

    Route::post('securelogin', [V2LoginController::class, 'secureLoginValidate'])
        ->middleware('throttle:10,1');

    Route::get('validate_otp', function () {
        return redirect()->route('login');
    });

    Route::post('validate_otp', $disabledLegacyAuthEndpoint)->middleware('throttle:10,10');
});

/*
|--------------------------------------------------------------------------
| Shared Ajax
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['balanceupdate', 'logout_device', 'auth']], function () {
    Route::post('refresh-popup/seen', [TamaTopupV2Controller::class, 'markRefreshPopupSeen']);
});

/*
|--------------------------------------------------------------------------
| Authenticated V2 Area
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['balanceupdate', 'logout_device', 'totp']], function () {
    Route::group(['middleware' => ['auth', 'fw-block-blacklisted']], function () {
        Route::get('dashboard-v2', [V2DashboardController::class, 'index'])
            ->name('dashboard.v2')
            ->middleware('v2.allowed');

        Route::prefix('dashboard')->group(function () {
            Route::get('summary', [V2DashboardController::class, 'summary']);
            Route::get('orders', [V2DashboardController::class, 'orders']);
            Route::get('monthly-transactions', [V2DashboardController::class, 'monthlyTransactions']);
            Route::get('balances', [V2DashboardController::class, 'balances']);
            Route::get('service-monthly', [V2DashboardController::class, 'serviceMonthly']);
            Route::get('topup-health', [V2DashboardController::class, 'topupHealth']);
            Route::get('margins', [V2DashboardController::class, 'margins']);
            Route::get('top-sales', [V2DashboardController::class, 'topSales']);
        });

        Route::get('profile-v2', [V2ProfileController::class, 'index'])
            ->name('profile.v2')
            ->middleware('v2.allowed');

        Route::prefix('app-settings-v2')->middleware('v2.allowed')->group(function () {
            Route::get('/', [V2ApplicationSettingsController::class, 'index'])->name('app-settings.v2');
            Route::post('save', [V2ApplicationSettingsController::class, 'save'])->name('app-settings.v2.save');
        });

        Route::prefix('orders-v2')->middleware('v2.allowed')->group(function () {
            Route::get('/', [OrderController::class, 'indexV2'])->name('orders.v2');
            Route::get('fetch', [OrderController::class, 'getOrders'])->name('orders.v2.data');
        });

        Route::prefix('transactions-v2')->middleware('v2.allowed')->group(function () {
            Route::get('/', [TransactionController::class, 'indexV2'])->name('transactions.v2');
            Route::get('fetch', [TransactionController::class, 'getTransactions'])->name('transactions.v2.data');
        });

        Route::prefix('payments-v2')->middleware('v2.allowed')->group(function () {
            Route::get('/', [V2PaymentController::class, 'index'])->name('payments.v2');
            Route::get('fetch', [V2PaymentController::class, 'data'])->name('payments.v2.fetch');
        });

        Route::prefix('failed-transactions-v2')->middleware('v2.allowed')->group(function () {
            Route::get('/', [V2FailedTransactionController::class, 'index'])->name('failed-transactions.v2');
            Route::get('fetch', [V2FailedTransactionController::class, 'data'])->name('failed-transactions.v2.fetch');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | V2 Services
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => ['auth', 'service', 'ipaccess', 'restrict-manager', 'fw-block-blacklisted']], function () {
        Route::get('bus-v2', [TamaBusV2Controller::class, 'index'])
            ->middleware(['protected_from_ip', 'v2.allowed'])
            ->name('bus.v2');

        Route::group(['prefix' => 'bus', 'middleware' => ['v2.allowed']], function () {
            Route::post('search', [TamaBusV2Controller::class, 'search'])->name('bus.v2.search');
            Route::post('create-reservations-flix', [TamaBusV2Controller::class, 'createFlixReservation'])->name('bus.v2.reserve.flix');
            Route::post('create-reservation-bla', [TamaBusV2Controller::class, 'createBlaReservation'])->name('bus.v2.reserve.bla');
            Route::post('confirm', [TamaBusV2Controller::class, 'confirmFlix'])->name('bus.v2.confirm');
            Route::post('bla/confirm', [TamaBusV2Controller::class, 'confirmBla'])->name('bus.v2.confirm.bla');
            Route::post('reset', [TamaBusV2Controller::class, 'reset'])->name('bus.v2.reset');
            Route::post('trip-stops', [TamaBusV2Controller::class, 'fetchTripStops'])->name('bus.v2.trip.stops');
        });

        Route::get('tama-topup', [TamaTopupV2Controller::class, 'index'])->middleware(['protected_from_ip']);
        Route::get('tama-topup/print/receipt/{order_id}', [TamaTopupV2Controller::class, 'printReceipt']);

        Route::get('tama-topup-v2', [TamaTopupV2Controller::class, 'index'])
            ->middleware(['protected_from_ip', 'v2.allowed']);

        Route::group(['prefix' => 'tama-topup-v2', 'middleware' => ['v2.allowed']], function () {
            Route::get('route', [TamaTopupV2Controller::class, 'route']);
            Route::post('encrypt-review', [TamaTopupV2Controller::class, 'encryptReview']);
            Route::get('review', [TamaTopupV2Controller::class, 'review']);
            Route::get('print/receipt/{order_id}', [TamaTopupV2Controller::class, 'printReceipt']);

            Route::get('fetch/ding/{operation}', [TamaTopupV2DingController::class, 'fetch']);
            Route::post('ding/confirm', [TamaTopupV2DingController::class, 'confirm']);

            Route::get('fetch/reloadly/{operation}', [TamaTopupV2ReloadlyController::class, 'fetch']);
            Route::post('reloadly/confirm', [TamaTopupV2ReloadlyController::class, 'confirm']);

            Route::get('fetch/transfer/{operation}', [TamaTopupV2TransferController::class, 'fetch']);
            Route::post('transfer/confirm', [TamaTopupV2TransferController::class, 'confirm']);

            Route::get('fetch/tellus/{operation}', [TamaTopupV2TellusController::class, 'fetch']);
            Route::post('tellus/confirm', [TamaTopupV2TellusController::class, 'confirm']);
        });

        Route::get('calling-cards-v2', [CallingCardV2Controller::class, 'index'])
            ->middleware(['protected_from_ip', 'v2.allowed']);

        Route::group(['prefix' => 'calling-cards-v2/data', 'middleware' => ['v2.allowed']], function () {
            Route::get('providers', [CallingCardV2Controller::class, 'providers']);
            Route::get('cards/{id}', [CallingCardV2Controller::class, 'cards']);
            Route::post('card-info', [CallingCardV2Controller::class, 'cardInfo']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | V2 Calling Card History
    |--------------------------------------------------------------------------
    */
    Route::group(['middleware' => ['auth', 'fw-block-blacklisted', 'v2.allowed']], function () {
        Route::prefix('cc-price-lists-v2')->group(function () {
            Route::get('/', [RateTableController::class, 'indexV2'])->name('cc-price-lists.v2');
            Route::get('fetch', [RateTableController::class, 'fetch_data'])->name('cc-price-lists.v2.fetch');
            Route::post('update', [RateTableController::class, 'update_price'])->name('cc-price-lists.v2.update');
        });

        Route::prefix('my/cc-price-lists-v2')->group(function () {
            Route::get('/', [RateTableController::class, 'indexV2'])->name('my.cc-price-lists.v2');
            Route::get('fetch', [RateTableController::class, 'getMyPriceListsV2'])->name('my.cc-price-lists.v2.fetch');
        });

        Route::prefix('cc-pin-history-v2')->group(function () {
            Route::get('/', [V2PinHistoryController::class, 'index'])->name('cc-pin-history.v2');
            Route::get('fetch', [V2PinHistoryController::class, 'data'])->name('cc-pin-history.v2.fetch');
            Route::post('print_again/request', [V2PinHistoryController::class, 'createPrintRequest'])->name('cc-pin-history.v2.print-request');
            Route::get('print/{pin_id}', [V2PinHistoryController::class, 'print'])->name('cc-pin-history.v2.print');
            Route::get('contact/{pin_id}', [V2PinHistoryController::class, 'contact'])->name('cc-pin-history.v2.contact');
            Route::post('contact', [V2PinHistoryController::class, 'sendContact'])->name('cc-pin-history.v2.contact.send');
        });
    });
});
