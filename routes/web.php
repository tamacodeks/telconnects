<?php
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

//\Debugbar::disable();
use app\Library\ApiHelper;
use app\Library\AppHelper;
use App\Support\WaOtp;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

Route::get('working' , function(){
return view('auth.working');
});
Route::get('tellus/check', function () {
    return response()->json([
        'status' => 200,
        'success' => true,
        'message' => 'Tellus check endpoint is up',
        'timestamp' => date('c'),
    ]);
});
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return redirect('/dashboard');
});
Route::get('lang/{lang}', ['as'=>'lang.switch', 'uses'=>'App\LanguageController@switchLang']);
Route::get('translation','App\LanguageController@index');
Route::get('translation/add','App\LanguageController@add');
Route::post('translation/add','App\LanguageController@save');
Route::post('translation/save','App\LanguageController@update');
Route::get('translation/remove/{folder}','App\LanguageController@remove');
Route::get('test',"TestController@index");
Route::get('migrate',"TestController@migrate");

//activity
Route::group(['middleware' => ['auth']], function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/verify_login', [LoginController::class, 'verify']);
    // Route URL
    Route::get('/enable-2fa', 'Auth\TwoFactorController@enable2fa')->name('enable-2fa');
    Route::post('/verify2fa', 'Auth\TwoFactorController@verify2fa');
});
Route::get('restrict',function (Request $request){
    if($request->expectsJson()){
        return ApiHelper::response('403',403,"Access denied!");
    }
    return response()->view('restrict', [], 403);
});

foreach ([400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 421, 422, 423, 424, 425, 426, 428, 429, 431, 451, 500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511] as $statusCode) {
    Route::get("error-{$statusCode}", function () use ($statusCode) {
        $view = $statusCode >= 500 ? 'errors.5xx' : 'errors.4xx';

        return response()->view($view, ['status_code' => $statusCode], $statusCode);
    })->name("error-{$statusCode}");
}

//common routes for all
Route::group(['middleware' => ['balanceupdate', 'logout_device','auth']], function () {
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');
});
Route::group(['middleware' => ['balanceupdate','logout_device','totp']],function () {
	Route::group(['middleware' => ['auth', 'fw-block-blacklisted']], function () {
        Route::get('dashboard', 'DashboardController@index')->name('dashboard');
        //users
        Route::get('users', 'App\UserController@index');
        Route::get('user_info', 'App\UserController@user_info');
        Route::get('fetch/users_info', 'App\UserController@getIpData');
        Route::get('fetch/users', 'App\UserController@getRowDetailsData');
        Route::post('users/reset-transaction-corrections', 'App\UserController@resetTransactionCorrections');
        Route::post('users/run-reset-corrections-today', 'App\UserController@runResetCorrectionsToday');
        Route::get('user/view/{id}', 'App\UserController@view');
        Route::get('user/impersonate/{enc}', 'App\UserController@impersonate');
        Route::get('end/impersonate/{enc}', 'App\UserController@end_impersonate');
        Route::get('user/update/{id?}', 'App\UserController@edit');
        Route::post('user/update', 'App\UserController@update');
        Route::get('profile', 'App\UserController@profile');
        Route::post('user/edit/profile', 'App\UserController@update_profile');
        Route::get('user/remove/{id}', 'App\UserController@delete');
        Route::get('check_username','App\UserController@checkUsername');
        Route::get('all_users', 'App\UserController@all_users');
        Route::get('fetch_all_users', 'App\UserController@fetch_all_users');
        Route::get('refresh_popup_seen_users', 'App\UserController@refresh_popup_seen_users');
        Route::get('fetch_refresh_popup_seen_users', 'App\UserController@fetch_refresh_popup_seen_users');

        //banners
        Route::get('banner','App\SettingsController@banner');
        Route::post('add/banner','App\SettingsController@add');
        Route::get('edit/banner/{id}','App\SettingsController@edit');
        Route::get('del_banner/{id}','App\SettingsController@delete');
        Route::get('all_banners','App\SettingsController@all_banners');
        Route::get('view/banner/{id}','App\SettingsController@view_user_banner');
        //orders
        Route::get('orders', 'App\OrderController@index');
        Route::get('fetch/orders', 'App\OrderController@getOrders');

        //payments
        Route::get('payments', 'App\PaymentController@index');
        Route::get('fetch/payments', 'App\PaymentController@getPayments');
        Route::get('payment/add', 'App\PaymentController@add_payment');
        Route::post('payment/update', 'App\PaymentController@update_payment');
        //limit
        Route::get('limit/add', 'App\PaymentController@add_limit');
        Route::post('limit/update', 'App\PaymentController@update_limit');
        Route::post('limit/delete','App\PaymentController@delete_limit');
        //search
        Route::get('search', 'App\SearchController@index');
        Route::get('search/ajax', 'App\SearchController@ajaxSearch');

        //transactions
        Route::get('transactions', 'App\TransactionController@index');
        Route::get('fetch/transactions', 'App\TransactionController@getTransactions');
        //transactions
        Route::get('failed_transaction', 'App\TransactionController@failed_transaction');
        Route::get('fetch/failed_transaction', 'App\TransactionController@getfailed_transaction');

        //Manage Margins
        Route::get('system_transactions','App\TransactionController@system_transactions');
        Route::post('filter_transactions','App\TransactionController@system_transactions');

        //calling card
        Route::get('processAll', 'MyService\CallingCardController@processAll');
    });

    Route::group(['middleware' => ['auth', 'root', 'fw-block-blacklisted']], function () {
        //menus
        Route::get('menus/{id?}', 'App\MenuController@index');
        Route::post('menu/save', 'App\MenuController@save');
        Route::post('menu/re-order', 'App\MenuController@re_order_menu');
        Route::get('menu/remove/{id}', 'App\MenuController@remove');

        //settings
        Route::get('app-settings', 'App\SettingsController@app_settings');
        Route::post('app-settings/save', 'App\SettingsController@save');

        //user-groups
        Route::get('user-groups', 'App\UserGroupController@index');
        Route::get('fetch/user-groups', 'App\UserGroupController@getUserGroups');
        Route::get('user-group/update/{id?}', 'App\UserGroupController@edit');
        Route::post('user-group/update', 'App\UserGroupController@update');
        Route::get('user-group/remove/{id}', 'App\UserGroupController@delete');

        //config send-tama countries
        Route::get('config/send-tama', 'App\ServiceConfigController@config_send_tama');
        Route::post('config/send-tama/save', 'App\ServiceConfigController@save_send_tama_config');

        //manage services
        Route::get('services', 'App\ServiceController@index');
        Route::get('fetch/service', 'App\ServiceController@render_services');
        Route::get('service/update/{id?}', 'App\ServiceController@edit');
        Route::post('service/update', 'App\ServiceController@update');
        Route::get('service/remove/{id}', 'App\ServiceController@delete');

        //currency update
        Route::get('currencies', 'App\CurrencyController@index');
        Route::get('fetch/currencies', 'App\CurrencyController@render_currencies');
        Route::get('currency/update/{id?}', 'App\CurrencyController@edit');
        Route::post('currency/update', 'App\CurrencyController@update');
        Route::get('currency/remove/{id}', 'App\CurrencyController@delete');

        //activity log viewer
        Route::get('activities', 'App\ActivityController@index');
        Route::get('fetch/logs', 'App\ActivityController@fetch_logs');
        Route::get('clear/logs', 'App\ActivityController@clear');

        //app commissions
        Route::get('service-commissions', 'App\ServiceCommissionsController@index');
        Route::get('service-commissions/fetch', 'App\ServiceCommissionsController@fetch_app_service_commissions');
        Route::get('service-commission/update/{id?}', 'App\ServiceCommissionsController@edit');
        Route::post('service-commission/update', 'App\ServiceCommissionsController@update');
        Route::get('service-commission/remove/{id}', 'App\ServiceCommissionsController@delete');

        //service config
        //telecom countries
        Route::get('telecom-countries', 'ServiceConfig\TelecomCountriesController@index');
        Route::get('telecom-countries/fetch', 'ServiceConfig\TelecomCountriesController@fetch_data');
        Route::get('telecom-country/update/{id?}', 'ServiceConfig\TelecomCountriesController@edit');
        Route::post('telecom-country/update', 'ServiceConfig\TelecomCountriesController@update');
        Route::get('telecom-country/remove/{id}', 'ServiceConfig\TelecomCountriesController@delete');

        //telecom-countries
        Route::get('tp-config', 'ServiceConfig\TelecomProviderController@index');
        Route::get('tp-config/fetch', 'ServiceConfig\TelecomProviderController@fetch_data');
        Route::get('tp-config/update/{id?}', 'ServiceConfig\TelecomProviderController@edit');
        Route::post('tp-config/update', 'ServiceConfig\TelecomProviderController@update');
        Route::get('tp-config/remove/{id}', 'ServiceConfig\TelecomProviderController@delete');

        //calling-card-reverse transaction
        Route::get('cc/reverse-transaction', 'App\CallingCardUploadController@index');
        Route::get('cc/reverse-transaction/fetch', 'App\CallingCardUploadController@fetch_data');
        Route::get('cc/reverse-transaction/rollback/{id}', 'App\CallingCardUploadController@rollback');
        Route::post('cc/reverse-transaction/rollback', 'App\CallingCardUploadController@reverse_trans');

        //calling-cards align cards
        Route::get('cc/align/cards', 'App\CallingCardAlignController@index');
        Route::post('cc/align/cards/update', 'App\CallingCardAlignController@update');


        //aleda service manage
        Route::get('aleda/manage', 'Myservice\CallingCard\AledaController@index');
        Route::get('aleda/sync/catalogue', 'Myservice\CallingCard\AledaController@syncCatalogue');
        //aleda statistics
        Route::get('aleda/statistics', 'Aleda\ReportController@index');
        Route::get('aleda/statistics/fetch', 'Aleda\ReportController@fetchAledaReports');
        Route::get('aleda/statistics/usage/{cc_id}', 'Aleda\ReportController@usageHistory');
        Route::get('aleda/statistics/usage/cc/fetch', 'Aleda\ReportController@getFetchHistory');

        //who is online
        Route::get('who-is-online', 'App\UserController@whoIsOnline');
        Route::get('bimedia_service', 'Myservice\CallingCard\BimediaController@index');
        Route::get('bimedia/sync/catalogue', 'Myservice\CallingCard\BimediaController@syncCatalogue');

    });

    Route::group(['middleware' => ['auth', 'service', 'ipaccess', 'restrict-manager', 'fw-block-blacklisted']], function () {
        //ding Calling card
        Route::get('tama-topup-france', 'Service\TamaTopupController@franceindex');
        Route::get('tama-topup-france/fetch/{operation}', 'Service\TamaTopupController@fetchDingcards');
        Route::get('tama-topup-france/review', 'Service\TamaTopupController@callingcardreviewTopup');
        Route::post('tama-topup-france/confirm', 'Service\TamaTopupController@confirm_ding_callingcard');
        Route::get('tama-topup-france/print/receipt/{order_id}', 'Service\TamaTopupController@CallingCardPrint');
        //add to cart setup
        Route::get('send-tama/clear-cart', 'Service\SendTamaController@clearCart');
        Route::get('send-tama/add-to-cart/{product_id}/', 'Service\SendTamaController@getCart');
        Route::get('send-tama/remove-from-cart/{product_id}/', 'Service\SendTamaController@removeFromCart');
        Route::get('send-tama/view-cart/{country_id}', 'Service\SendTamaController@viewCart');
        Route::post('send-tama/update-cart', 'Service\SendTamaController@updateCart');
        Route::post('send-tama/update-cart-from-checkout', 'Service\SendTamaController@updateCartFromCheckout');

        Route::get('send-tama', 'Service\SendTamaController@index');
        Route::get('send-tama/product/{product_id}', 'Service\SendTamaController@view_product');
        Route::get('send-tama/{country_id}/{category_id?}', 'Service\SendTamaController@view_products');
        Route::post('send-tama/confirm/order', 'Service\SendTamaController@confirm_order');
        //tama bus
        Route::get("flix-bus","Service\TamaBusController@index")->middleware('protected_from_ip');
        Route::get("flix-bus/download","Service\TamaBusController@download")->name('flix-bus.download');
        Route::get("flix-bus/download/{link}","Service\TamaBusController@download")->where('link', '.*');
        Route::get("flix-bus/{operation}","Service\TamaBusController@search");
        Route::post("flix-bus/create_reservations","Service\TamaBusController@create_reservations");
        Route::post("flix-bus/add_passengers_details","Service\TamaBusController@add_passenger_details");

        Route::get("bus","Service\TamaBusController@both")->middleware(['protected_from_ip']);
        Route::post("flix-bus/search","Service\TamaBusController@search_bus");
        Route::post("flix-bus/create_reservations_bus","Service\TamaBusController@create_reservations_bus");
        Route::post("flix-bus/confirm","Service\TamaBusController@confirm");

        Route::post("flix-bus/create_reservation_blabus","Service\TamaBusController@create_reservation_blabus");
        Route::post("flix-bus/bla/confirm","Service\TamaBusController@bla_bus_confirm");
        //send tama
//    Route::get('send-tama','Service\SendTamaController@index');
//    Route::get('send-tama/{country_id}','Service\SendTamaController@view_products');
//    Route::get('send-tama/product/{product_id}','Service\SendTamaController@view_product');
//    Route::post('send-tama/confirm/order','Service\SendTamaController@confirm_order');

        //tama-topup
        Route::get('tama-topup-v1', 'Service\TamaTopupController@index')->middleware('protected_from_ip');;
        Route::get('tama-topup/plans', 'Service\TamaTopupController@plans');
        Route::get('tama-topup/plan_s','Service\TamaTopupController@ding_plans');
        Route::get('tama-topup/plan_ts','Service\TamaTopupController@transfer_plans');
        Route::post('tama-topup/confirm/topup', 'Service\TamaTopupController@confirm_topup');
        // Route::get('tama-topup/print/receipt/{order_id}', 'Service\TamaTopupController@printReceipt');
        Route::get('tama-topup/ding', 'Service\TamaTopupController@dingTopup');
        //ding tama-topup
        Route::get('tama-topup/fetch/{operation}', 'Service\TamaTopupController@fetchDingInterface');
        Route::get('/tama-topup/review', 'Service\TamaTopupController@reviewTopup');
        Route::post('tama-topup/ding/confirm/topup', 'Service\TamaTopupController@confirmDingTopup');
        //prepay tama-topup
        Route::get('tama-topup/fetchprepay/{operation}', 'Service\TamaTopupController@fetchPrepayInterface');
        Route::get('/tama-topup/prepay-review','Service\TamaTopupController@reviewTopupprepay');
        Route::post('tama-topup/prepay/confirm/topup', 'Service\TamaTopupController@confirmPrepayTopup');
        //reloadly tama-topup
        Route::get('tama-topup/fetchreloadly/{operation}', 'Service\TamaTopupController@fetchReloadlyInterface');
        Route::get('/tama-topup/reloadly-review','Service\TamaTopupController@reviewTopupreloadly');
        Route::post('tama-topup/reloadly/confirm/topup', 'Service\TamaTopupController@confirmReloadlyTopup');
        //Transfer to tama-topup
        Route::get('tama-topup/fetchtransfer/{operation}', 'Service\TamaTopupController@fetchTransferInterface');
        Route::get('/tama-topup/transfer-review','Service\TamaTopupController@reviewTopuptransfer');
        Route::post('tama-topup/transfer/confirm/topup', 'Service\TamaTopupController@confirmTransferTopup');
        //tama-pay
        Route::get('tama-pay', 'Service\TamaPayController@index');
        Route::post('tama-pay/confirm/order', 'Service\TamaPayController@confirm_order');


        //tama-app
        Route::get('tama-app', 'Service\TamaAppController@index');
        Route::post('tama-app/balance', 'Service\TamaAppController@fetch_balance');
        Route::post('tama-app/confirm/order', 'Service\TamaAppController@confirm_order');
        Route::get('tama-app/print/receipt/{order_id}', 'Service\TamaAppController@printReceipt');

        //tama-family
        Route::get('tama-family', 'Service\TamaFamilyController@index');
        Route::post('tama-family/balance', 'Service\TamaFamilyController@get_user_balance');
        Route::post('tama-family/confirm/order', 'Service\TamaFamilyController@confirm_order');

        //callingcards
        Route::get('calling-cards', 'Service\CallingCardController@index')->middleware('protected_from_ip');
        Route::get('calling-cards/{id}', 'Service\CallingCardController@denominations');
        Route::get('calling-cards/print/{id}', 'Service\CallingCardController@print_card');
        Route::post('calling-cards/print', 'Service\CallingCardController@confirmPrint');
        Route::post('calling-cards/print/aleda', 'Service\CallingCardController@aledaPrintCard');

    });
    Route::group(['middleware' => ['auth', 'service']], function () {
        Route::post('print_card_activated', 'Service\CallingCardController@print_card_activated');
        Route::get('bimedia_stat', 'MyService\CallingCard\BimediaController@bimedia_stat');
        Route::get('bimedia_stat/fetch', 'MyService\CallingCard\BimediaController@fetch_data');
        Route::get('bimedia-cards/{id}', 'Service\CallingCardController@bimedia_card_fetch');
        Route::post('print_card_bimedia', 'Service\CallingCardController@print_card_bimedia');
        Route::get('callings-cards/{id}', 'Service\CallingCardController@bimedia_card_fetch');
        Route::post('print_callingcard', 'Service\CallingCardController@print_card_bimedia');

        Route::get('mycallingcards/{id}', 'Service\CallingCardController@mycalling_card_fetch');
        Route::post('print_mycallingcard', 'Service\CallingCardController@print_mycard');
    });

//myservices
    Route::group(['middleware' => ['auth', 'myservice', 'fw-block-blacklisted']], function () {
        Route::get('bimedia_balance', 'Myservice\CallingCard\BimediaController@index');
        //manage Routing service provider
        Route::get('routing_service', 'MyService\CallingCard\SeriveProvidersController@index');
        Route::get('service_provider', 'MyService\CallingCard\SeriveProvidersController@index');
        Route::get('service_provider/fetch', 'MyService\CallingCard\SeriveProvidersController@fetch_data');
        Route::get('service_provider/update/{id?}', 'MyService\CallingCard\SeriveProvidersController@edit');
        Route::post('service_provider/update', 'MyService\CallingCard\SeriveProvidersController@update');

        //manage telecom providers
        Route::get('telecom-providers', 'MyService\CallingCard\TelecomProvidersController@index');
        Route::get('telecom-providers/fetch', 'MyService\CallingCard\TelecomProvidersController@fetch_data');
        Route::get('telecom-provider/update/{id?}', 'MyService\CallingCard\TelecomProvidersController@edit');
        Route::post('telecom-provider/update', 'MyService\CallingCard\TelecomProvidersController@update');
        Route::get('telecom-provider/remove/{id}', 'MyService\CallingCard\TelecomProvidersController@delete');

        //manage calling cards
        Route::get('cc/manage', 'MyService\CallingCard\CallingCardController@index');
        Route::get('cc/fetch', 'MyService\CallingCard\CallingCardController@fetch_data');
        //manage retailer
        Route::get('cc/manage-resellers/{id?}', 'MyService\CallingCard\CallingCardController@manage_access');
        Route::post('cc/update/reseller-access', 'MyService\CallingCard\CallingCardController@update_retailer');
        //upload new pin or existing one
        Route::get('cc/update/{id?}', 'MyService\CallingCard\CallingCardController@edit');
        Route::post('cc/update', 'MyService\CallingCard\CallingCardController@update');
        //upload pins to existing one
        Route::get('cc/upload-pins/{id}', 'MyService\CallingCard\CallingCardController@upload_pins');
        Route::post('cc/upload-pins', 'MyService\CallingCard\CallingCardController@pin_upload');

        //calling card reports
        //uploaded pin statistics
        Route::get('cc/report/upload-statistics', 'MyService\CallingCard\ReportController@overall_upload_stats');
        Route::get('cc/report/upload-statistics/fetch', 'MyService\CallingCard\ReportController@fetch_overall_upload_stats');
        //pin usage history
        Route::get('cc/report/usage-history', 'MyService\CallingCard\ReportController@pin_usage_history');
        Route::get('cc/report/usage-history/fetch', 'MyService\CallingCard\ReportController@fetch_pin_usage_history');
        //pins report
        Route::get('cc/report/pins', 'MyService\CallingCard\ReportController@pins_report');
        Route::get('cc/report/pins/fetch', 'MyService\CallingCard\ReportController@fetch_pins_report');
        //margin report
        Route::get('cc/report/margins', 'MyService\CallingCard\ReportController@margin_report');
        Route::get('cc/report/margins/fetch', 'MyService\CallingCard\ReportController@fetch_margin_report');

        //price-lists groups
        Route::get('cc-price-list/groups', 'MyService\CallingCard\RateTableGroupController@index');
        Route::get('cc-price-list/groups/fetch', 'MyService\CallingCard\RateTableGroupController@fetch_data');
        Route::get('cc-price-list/groups/edit/{id?}', 'MyService\CallingCard\RateTableGroupController@edit');
        Route::post('cc-price-list/groups/update', 'MyService\CallingCard\RateTableGroupController@update');
        Route::get('cc-price-list/groups/remove/{id}', 'MyService\CallingCard\RateTableGroupController@delete');

        //pin-requests
        Route::get('cc-print-requests', 'MyService\CallingCard\PinHistoryController@viewPinRequests');
        Route::get('cc-print-requests/fetch', 'MyService\CallingCard\PinHistoryController@fetchPinPrintRequests');
        Route::post('cc-print-requests/process', 'MyService\CallingCard\PinHistoryController@processPinPrintRequests');

        Route::get('cc/refresh/price-lists/{user_id}', 'MyService\CallingCard\CallingCardController@callWebHookForMasterRetailer');

    });

    Route::group(['middleware' => ['auth', 'fw-block-blacklisted']], function () {
        Route::middleware(['IsAdminorMaster'])->group(function () {
            //service manage
            Route::get('service-access', 'App\ServiceAccessController@index');
            Route::get('service-access/list', 'App\ServiceAccessController@list');
            Route::post('service-access/retailers', 'App\ServiceAccessController@getRetailers');
            Route::post('service-access/services', 'App\ServiceAccessController@getRetailerServices');
            Route::post('service-access/update', 'App\ServiceAccessController@updateRetailerServices');
        });
        //price-lists
        Route::get('cc-price-lists', 'MyService\CallingCard\RateTableController@index');
        Route::get('cc-price-lists/fetch', 'MyService\CallingCard\RateTableController@fetch_data');
        Route::post('cc-price-lists/update', 'MyService\CallingCard\RateTableController@update_price');
        Route::get('my/cc-price-lists', 'MyService\CallingCard\RateTableController@getMyPriceLists');

        //pin history
        Route::get('cc-pin-history', 'MyService\CallingCard\PinHistoryController@index');
        Route::get('cc-pin-history/fetch', 'MyService\CallingCard\PinHistoryController@fetchPinHistories');
        Route::post('cc-pin-history/print_again/request', 'MyService\CallingCard\PinHistoryController@createPinPrintRequest');
        Route::get('cc-pin-history/print/{pin_id}', 'MyService\CallingCard\PinHistoryController@printPinAgain');
        Route::get('cc-pin-history/contact/{pin_id}', 'MyService\CallingCard\PinHistoryController@getEnquiryNow');
        Route::post('cc-pin-history/contact', 'MyService\CallingCard\PinHistoryController@sendEnquiry');

        //tickets
        Route::get('tickets', 'MyService\CallingCard\TicketController@index');
        Route::get('tickets/fetch', 'MyService\CallingCard\TicketController@fetchMyTickets');
        Route::get('ticket/conversation/{ticket_id}', 'MyService\CallingCard\TicketController@showConversation');
        //manage tickets
        Route::get('tickets/manage', 'MyService\CallingCard\TicketController@manageTickets');
        Route::get('tickets/manage/fetch', 'MyService\CallingCard\TicketController@fetchIncomingTickets');
        Route::post('tickets/comment', 'MyService\CallingCard\TicketController@saveComment');
        //forward ticket
        Route::get('ticket/forward/{ticket_id}', 'MyService\CallingCard\TicketController@forwardTicket');
        Route::post('ticket/close', 'MyService\CallingCard\TicketController@closeTicket');

        //notifications
        Route::get('notifications', 'App\NotificationController@index');
        Route::get('notifications/mark-all-as-read', 'App\NotificationController@markAsRead');

        //inbox
        Route::get('inbox', 'Chat\InboxController@index');

        Route::get('private-chat/{chatroom}', 'Chat\PrivateChatController@index')->name('private.chat.index');
        Route::post('private-chat/{chatroom}', 'Chat\PrivateChatController@store')->name('private.chat.store');
        Route::get('fetch-private-chat/{chatroom}/', 'Chat\PrivateChatController@get')->name('fetch-private.chat');


    });

    Route::group(['middleware' => ['auth', 'parent', 'fw-block-blacklisted']], function () {
        //myorders
        Route::get('my/orders', 'App\OrderController@myOrders');
        Route::get('fetch/my/orders', 'App\OrderController@fetchMyOrders');

        //my transactions
        Route::get('my/transactions', 'App\TransactionController@myTransactions');
        Route::get('fetch/my/transactions', 'App\TransactionController@fetchMyTransactions');

        //my payments
        Route::get('my/payments', 'App\PaymentController@myPayments');
        Route::get('fetch/my/payments', 'App\PaymentController@getMyPayments');
    });
});











