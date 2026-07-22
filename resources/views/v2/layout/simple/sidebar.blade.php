<div class="sidebar-wrapper" sidebar-layout="stroke-svg" data-v2-sidebar>
    <div>
        @php
            $user = auth()->user();
            $canUseV2Sidebar = \App\Support\V2Access::userCanUseV2($user);
            $currentRouteName = \Illuminate\Support\Facades\Route::current()
                ? \Illuminate\Support\Facades\Route::current()->getName()
                : null;
            $hideRetailerDashboardSidebarCards = false;
            $appName = defined('APP_NAME') ? APP_NAME : config('app.name');
            $appLogoFile = (defined('APP_LOGO') && APP_LOGO && file_exists(public_path('images/' . APP_LOGO))) ? APP_LOGO : 'logo.png';
            $appLogoUrl = asset('images/' . $appLogoFile);
            $sidebarHomeUrl = ($canUseV2Sidebar && \Illuminate\Support\Facades\Route::has('dashboard.v2'))
                ? route('dashboard.v2')
                : (\Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('dashboard'));
            $sidebarProfileUrl = ($canUseV2Sidebar && \Illuminate\Support\Facades\Route::has('profile.v2'))
                ? route('profile.v2')
                : url('profile');

            $v2SidebarPathMap = [
                'dashboard' => 'dashboard-v2',
                'dashboard-v2' => 'dashboard-v2',
                'profile' => 'profile-v2',
                'profile-V2' => 'profile-v2',
                'profile-v2' => 'profile-v2',
                'payments' => 'payments-v2',
                'payments-v2' => 'payments-v2',
                'bus' => 'bus-v2',
                'flix-bus' => 'bus-v2',
                'bus-v2' => 'bus-v2',
                'tama-topup' => 'tama-topup-v2',
                'tama-topup-v2' => 'tama-topup-v2',
                'calling-cards' => 'calling-cards-v2',
                'calling-cards-v2' => 'calling-cards-v2',
                'cc-price-lists' => 'cc-price-lists-v2',
                'cc-price-lists-v2' => 'cc-price-lists-v2',
                'cc-pin-history' => 'cc-pin-history-v2',
                'cc-pin-history-v2' => 'cc-pin-history-v2',
                'tickets' => 'tickets-v2',
                'tickets-v2' => 'tickets-v2',
                'orders' => 'orders-v2',
                'orders-v2' => 'orders-v2',
                'transactions' => 'transactions-v2',
                'transactions-v2' => 'transactions-v2',
                'failed_transaction' => 'failed-transactions-v2',
                'failed-transactions-v2' => 'failed-transactions-v2',
                'menus' => 'menus-v2',
                'menus-v2' => 'menus-v2',
                'app-settings' => 'app-settings-v2',
                'app-settings-v2' => 'app-settings-v2',
            ];

            $v2SidebarRouteMap = [
                'dashboard' => 'dashboard',
                'dashboard-v2' => 'dashboard.v2',
                'profile-v2' => 'profile.v2',
                'payments-v2' => 'payments.v2',
                'bus' => 'bus.v2',
                'bus-v2' => 'bus.v2',
                'cc-price-lists-v2' => 'cc-price-lists.v2',
                'cc-pin-history-v2' => 'cc-pin-history.v2',
                'tickets-v2' => 'tickets.v2',
                'orders-v2' => 'orders.v2',
                'transactions-v2' => 'transactions.v2',
                'failed-transactions-v2' => 'failed-transactions.v2',
                'menus' => 'menus.index',
                'menus-v2' => 'menus.v2',
                'app-settings-v2' => 'app-settings.v2',
            ];

            $v2SidebarActiveRouteMap = [
                'dashboard' => ['dashboard'],
                'dashboard-v2' => ['dashboard.v2'],
                'profile' => ['profile.v2'],
                'profile-v2' => ['profile.v2'],
                'profile-V2' => ['profile.v2'],
                'payments' => ['payments.v2'],
                'payments-v2' => ['payments.v2'],
                'bus' => ['bus.v2*', 'flix-bus.download'],
                'bus-v2' => ['bus.v2*', 'flix-bus.download'],
                'menus' => ['menus.index', 'menus.v2*'],
                'menus-v2' => ['menus.v2*', 'menus.index'],
                'app-settings' => ['app-settings.v2*'],
                'app-settings-v2' => ['app-settings.v2*'],
                'tama-topup' => ['tama-topup.v2*', 'topup.v2*'],
                'tama-topup-v2' => ['tama-topup.v2*', 'topup.v2*'],
                'calling-cards' => ['calling-cards.v2*', 'calling-card.v2*'],
                'calling-cards-v2' => ['calling-cards.v2*', 'calling-card.v2*'],
                'cc-price-lists' => ['cc-price-lists.v2*', 'my.cc-price-lists.v2*'],
                'cc-price-lists-v2' => ['cc-price-lists.v2*', 'my.cc-price-lists.v2*'],
                'cc-pin-history' => ['cc-pin-history.v2*'],
                'cc-pin-history-v2' => ['cc-pin-history.v2*'],
                'tickets' => ['tickets.v2*'],
                'tickets-v2' => ['tickets.v2*'],
                'orders' => ['orders.v2'],
                'orders-v2' => ['orders.v2'],
                'transactions' => ['transactions.v2'],
                'transactions-v2' => ['transactions.v2'],
                'failed_transaction' => ['failed-transactions.v2'],
                'failed-transactions-v2' => ['failed-transactions.v2'],
            ];

            $v2SidebarActivePathMap = [
                'dashboard' => ['dashboard', 'dashboard-v2', 'dashboard/summary', 'dashboard/orders', 'dashboard/monthly-transactions', 'dashboard/balances', 'dashboard/service-monthly', 'dashboard/topup-health', 'dashboard/margins', 'dashboard/top-sales'],
                'dashboard-v2' => ['dashboard-v2', 'dashboard', 'dashboard/summary', 'dashboard/orders', 'dashboard/monthly-transactions', 'dashboard/balances', 'dashboard/service-monthly', 'dashboard/topup-health', 'dashboard/margins', 'dashboard/top-sales'],
                'profile' => ['profile', 'profile-v2', 'profile-V2', 'user/edit/profile'],
                'profile-v2' => ['profile-v2', 'profile', 'profile-V2', 'user/edit/profile'],
                'profile-V2' => ['profile-v2', 'profile-V2', 'profile', 'user/edit/profile'],
                'payments' => ['payments', 'payments-v2', 'payments-v2/fetch', 'fetch/payments', 'payment/add', 'payment/update', 'limit/add', 'limit/update', 'limit/delete'],
                'payments-v2' => ['payments-v2', 'payments', 'payments-v2/fetch', 'fetch/payments', 'payment/add', 'payment/update', 'limit/add', 'limit/update', 'limit/delete'],
                'my/payments' => ['my/payments', 'fetch/my/payments'],
                'orders' => ['orders', 'orders-v2', 'fetch/orders'],
                'orders-v2' => ['orders-v2', 'orders', 'fetch/orders'],
                'my/orders' => ['my/orders', 'fetch/my/orders'],
                'transactions' => ['transactions', 'transactions-v2', 'fetch/transactions', 'system_transactions', 'filter_transactions'],
                'transactions-v2' => ['transactions-v2', 'transactions', 'fetch/transactions', 'system_transactions', 'filter_transactions'],
                'my/transactions' => ['my/transactions', 'fetch/my/transactions'],
                'failed_transaction' => ['failed_transaction', 'failed-transactions-v2', 'failed-transactions-v2/fetch', 'fetch/failed_transaction'],
                'failed-transactions-v2' => ['failed-transactions-v2', 'failed_transaction', 'failed-transactions-v2/fetch', 'fetch/failed_transaction'],
                'bus' => ['bus', 'flix-bus', 'bus-v2'],
                'bus-v2' => ['bus-v2', 'bus', 'flix-bus'],
                'tama-topup' => ['tama-topup', 'tama-topup-v1', 'tama-topup-v2', 'tama-topup-france'],
                'tama-topup-v1' => ['tama-topup-v1', 'tama-topup'],
                'tama-topup-v2' => ['tama-topup-v2', 'tama-topup', 'tama-topup-v1', 'tama-topup-france'],
                'tama-topup-france' => ['tama-topup-france', 'tama-topup', 'tama-topup-v2'],
                'calling-cards' => ['calling-cards', 'calling-cards-v2', 'callings-cards', 'mycallingcards', 'print_callingcard', 'print_mycallingcard'],
                'calling-cards-v2' => ['calling-cards-v2', 'calling-cards', 'callings-cards', 'mycallingcards', 'print_callingcard', 'print_mycallingcard'],
                'callings-cards' => ['callings-cards', 'calling-cards', 'calling-cards-v2'],
                'mycallingcards' => ['mycallingcards', 'calling-cards', 'calling-cards-v2'],
                'cc-price-lists' => ['cc-price-lists', 'cc-price-lists-v2', 'cc-price-lists/fetch', 'cc-price-lists/update', 'cc-price-lists-v2/fetch', 'cc-price-lists-v2/update', 'my/cc-price-lists-v2'],
                'cc-price-lists-v2' => ['cc-price-lists-v2', 'cc-price-lists', 'cc-price-lists-v2/fetch', 'cc-price-lists-v2/update', 'cc-price-lists/fetch', 'cc-price-lists/update', 'my/cc-price-lists-v2'],
                'cc-pin-history' => ['cc-pin-history', 'cc-pin-history-v2', 'cc-pin-history/fetch', 'cc-pin-history-v2/fetch', 'tickets-v2', 'tickets-v2/fetch'],
                'cc-pin-history-v2' => ['cc-pin-history-v2', 'cc-pin-history', 'cc-pin-history-v2/fetch', 'cc-pin-history/fetch', 'tickets-v2', 'tickets-v2/fetch'],
                'tickets' => ['tickets', 'tickets-v2', 'tickets/fetch', 'tickets-v2/fetch'],
                'tickets-v2' => ['tickets-v2', 'tickets', 'tickets-v2/fetch', 'tickets/fetch'],
                'my/cc-price-lists' => ['my/cc-price-lists', 'my/cc-price-lists-v2'],
                'cc-price-list/groups' => ['cc-price-list/groups', 'cc-price-list/groups/fetch', 'cc-price-list/groups/edit', 'cc-price-list/groups/update', 'cc-price-list/groups/remove'],
                'cc/report/usage-history' => ['cc/report/usage-history', 'cc/report/usage-history/fetch'],
                'cc/report/pins' => ['cc/report/pins', 'cc/report/pins/fetch'],
                'menus' => ['menus', 'menus-v2', 'menus-v2-data', 'menus-v2-json', 'menu-v2'],
                'menus-v2' => ['menus-v2', 'menus-v2-data', 'menus-v2-json', 'menu-v2', 'menus'],
                'app-settings' => ['app-settings', 'app-settings/save', 'app-settings-v2', 'app-settings-v2/save'],
                'app-settings-v2' => ['app-settings-v2', 'app-settings-v2/save', 'app-settings', 'app-settings/save'],
            ];

            $v2SidebarActiveActionMap = [
                'dashboard' => ['DashboardController@index', 'V2\DashboardController@*'],
                'dashboard-v2' => ['V2\DashboardController@*', 'DashboardController@index'],
                'profile' => ['App\UserController@profile', 'V2\ProfileController@index', 'App\UserController@update_profile'],
                'profile-v2' => ['V2\ProfileController@index', 'App\UserController@profile', 'App\UserController@update_profile'],
                'profile-V2' => ['V2\ProfileController@index', 'App\UserController@profile', 'App\UserController@update_profile'],
                'payments' => ['App\PaymentController@index', 'V2\PaymentController@*', 'App\PaymentController@getPayments', 'App\PaymentController@add_payment', 'App\PaymentController@update_payment', 'App\PaymentController@add_limit', 'App\PaymentController@update_limit', 'App\PaymentController@delete_limit'],
                'payments-v2' => ['V2\PaymentController@*', 'App\PaymentController@index', 'App\PaymentController@getPayments'],
                'my/payments' => ['App\PaymentController@myPayments', 'App\PaymentController@getMyPayments'],
                'orders' => ['App\OrderController@index', 'App\OrderController@indexV2', 'App\OrderController@getOrders'],
                'orders-v2' => ['App\OrderController@indexV2', 'App\OrderController@index', 'App\OrderController@getOrders'],
                'my/orders' => ['App\OrderController@myOrders', 'App\OrderController@fetchMyOrders'],
                'transactions' => ['App\TransactionController@index', 'App\TransactionController@indexV2', 'App\TransactionController@getTransactions', 'App\TransactionController@system_transactions'],
                'transactions-v2' => ['App\TransactionController@indexV2', 'App\TransactionController@index', 'App\TransactionController@getTransactions', 'App\TransactionController@system_transactions'],
                'my/transactions' => ['App\TransactionController@myTransactions', 'App\TransactionController@fetchMyTransactions'],
                'failed_transaction' => ['V2\FailedTransactionController@*', 'App\TransactionController@failed_transaction', 'App\TransactionController@getfailed_transaction'],
                'failed-transactions-v2' => ['V2\FailedTransactionController@*', 'App\TransactionController@failed_transaction', 'App\TransactionController@getfailed_transaction'],
                'bus' => ['Service\TamaBusController@*', 'Service\V2\TamaBusV2Controller@*'],
                'bus-v2' => ['Service\V2\TamaBusV2Controller@*', 'Service\TamaBusController@*'],
                'tama-topup' => ['Service\V2\TamaTopupV2Controller@*', 'Service\V2\TamaTopupV2DingController@*', 'Service\V2\TamaTopupV2ReloadlyController@*', 'Service\V2\TamaTopupV2TransferController@*', 'Service\V2\TamaTopupV2TellusController@*', 'Service\TamaTopupController@*'],
                'tama-topup-v2' => ['Service\V2\TamaTopupV2Controller@*', 'Service\V2\TamaTopupV2DingController@*', 'Service\V2\TamaTopupV2ReloadlyController@*', 'Service\V2\TamaTopupV2TransferController@*', 'Service\V2\TamaTopupV2TellusController@*', 'Service\TamaTopupController@*'],
                'tama-topup-v1' => ['Service\TamaTopupController@*'],
                'tama-topup-france' => ['Service\TamaTopupController@franceindex', 'Service\TamaTopupController@fetchDingcards', 'Service\TamaTopupController@callingcardreviewTopup', 'Service\TamaTopupController@confirm_ding_callingcard', 'Service\TamaTopupController@CallingCardPrint'],
                'calling-cards' => ['Service\V2\CallingCardV2Controller@*', 'Service\CallingCardController@*'],
                'calling-cards-v2' => ['Service\V2\CallingCardV2Controller@*', 'Service\CallingCardController@*'],
                'callings-cards' => ['Service\CallingCardController@bimedia_card_fetch', 'Service\CallingCardController@print_card_bimedia'],
                'mycallingcards' => ['Service\CallingCardController@mycalling_card_fetch', 'Service\CallingCardController@print_mycard'],
                'cc-price-lists' => ['MyService\CallingCard\RateTableController@index', 'MyService\CallingCard\RateTableController@indexV2', 'MyService\CallingCard\RateTableController@fetch_data', 'MyService\CallingCard\RateTableController@update_price'],
                'cc-price-lists-v2' => ['MyService\CallingCard\RateTableController@indexV2', 'MyService\CallingCard\RateTableController@fetch_data', 'MyService\CallingCard\RateTableController@update_price', 'MyService\CallingCard\RateTableController@getMyPriceListsV2'],
                'cc-pin-history' => ['MyService\CallingCard\PinHistoryController@index', 'MyService\CallingCard\PinHistoryController@indexV2', 'MyService\CallingCard\PinHistoryController@fetchPinHistories', 'MyService\CallingCard\PinHistoryController@fetchPinHistoriesV2', 'MyService\CallingCard\TicketController@indexV2', 'MyService\CallingCard\TicketController@fetchMyTicketsV2'],
                'cc-pin-history-v2' => ['MyService\CallingCard\PinHistoryController@indexV2', 'MyService\CallingCard\PinHistoryController@index', 'MyService\CallingCard\PinHistoryController@fetchPinHistoriesV2', 'MyService\CallingCard\PinHistoryController@fetchPinHistories', 'MyService\CallingCard\TicketController@indexV2', 'MyService\CallingCard\TicketController@fetchMyTicketsV2'],
                'tickets' => ['MyService\CallingCard\TicketController@index', 'MyService\CallingCard\TicketController@indexV2', 'MyService\CallingCard\TicketController@fetchMyTickets', 'MyService\CallingCard\TicketController@fetchMyTicketsV2'],
                'tickets-v2' => ['MyService\CallingCard\TicketController@indexV2', 'MyService\CallingCard\TicketController@fetchMyTicketsV2', 'MyService\CallingCard\TicketController@index', 'MyService\CallingCard\TicketController@fetchMyTickets'],
                'my/cc-price-lists' => ['MyService\CallingCard\RateTableController@getMyPriceLists'],
                'cc-price-list/groups' => ['MyService\CallingCard\RateTableGroupController@*'],
                'cc/report/usage-history' => ['MyService\CallingCard\ReportController@pin_usage_history', 'MyService\CallingCard\ReportController@fetch_pin_usage_history'],
                'cc/report/pins' => ['MyService\CallingCard\ReportController@pins_report', 'MyService\CallingCard\ReportController@fetch_pins_report'],
                'menus' => ['App\MenuController@*', 'V2\MenuController@*'],
                'menus-v2' => ['V2\MenuController@*', 'App\MenuController@index'],
                'app-settings' => ['App\SettingsController@app_settings', 'App\SettingsController@save', 'V2\ApplicationSettingsController@*'],
                'app-settings-v2' => ['V2\ApplicationSettingsController@*', 'App\SettingsController@app_settings', 'App\SettingsController@save'],
            ];

            $v2SidebarSectionMap = [
                'dashboard' => 'account',
                'dashboard-v2' => 'account',
                'profile' => 'account',
                'profile-v2' => 'account',
                'profile-V2' => 'account',
                'invoices' => 'account',
                'payment' => 'account',
                'payments' => 'account',
                'payments-v2' => 'account',
                'balance' => 'account',
                'calling-cards' => 'services',
                'calling-cards-v2' => 'services',
                'callings-cards' => 'services',
                'mycallingcards' => 'services',
                'print_callingcard' => 'services',
                'print_mycallingcard' => 'services',
                'tama-topup' => 'services',
                'tama-topup-v1' => 'services',
                'tama-topup-v2' => 'services',
                'tama-topup-france' => 'services',
                'bus' => 'services',
                'bus-v2' => 'services',
                'flix-bus' => 'services',
                'cc-price-lists' => 'services',
                'cc-price-lists-v2' => 'services',
                'my/cc-price-lists' => 'services',
                'cc-price-list/groups' => 'services',
                'orders' => 'history',
                'orders-v2' => 'history',
                'my/orders' => 'history',
                'transactions' => 'history',
                'transactions-v2' => 'history',
                'my/transactions' => 'history',
                'failed_transaction' => 'history',
                'failed-transactions-v2' => 'history',
                'cc/report/usage-history' => 'history',
                'cc/report/pins' => 'history',
                'cc-pin-history' => 'history',
                'cc-pin-history-v2' => 'history',
                'tickets' => 'history',
                'tickets-v2' => 'history',
                'menus' => 'settings',
                'menus-v2' => 'settings',
                'app-settings' => 'settings',
                'app-settings-v2' => 'settings',
                'service-config' => 'settings',
                'support' => 'settings',
                'help' => 'settings',
                'logout' => 'settings',
            ];

            $v2SidebarSectionPrefixMap = [
                'dashboard/' => 'account',
                'profile/' => 'account',
                'profile-v2/' => 'account',
                'profile-V2/' => 'account',
                'invoices/' => 'account',
                'payment/' => 'account',
                'payments/' => 'account',
                'payments-v2/' => 'account',
                'calling-cards/' => 'services',
                'calling-cards-v2/' => 'services',
                'callings-cards/' => 'services',
                'mycallingcards/' => 'services',
                'tama-topup/' => 'services',
                'tama-topup-v1/' => 'services',
                'tama-topup-v2/' => 'services',
                'tama-topup-france/' => 'services',
                'bus/' => 'services',
                'bus-v2/' => 'services',
                'flix-bus/' => 'services',
                'cc-price-lists/' => 'services',
                'cc-price-lists-v2/' => 'services',
                'my/cc-price-lists/' => 'services',
                'cc-price-list/' => 'services',
                'orders/' => 'history',
                'orders-v2/' => 'history',
                'my/orders/' => 'history',
                'transactions/' => 'history',
                'transactions-v2/' => 'history',
                'my/transactions/' => 'history',
                'failed_transaction/' => 'history',
                'failed-transactions-v2/' => 'history',
                'cc/report/' => 'history',
                'cc-pin-history/' => 'history',
                'cc-pin-history-v2/' => 'history',
                'tickets/' => 'history',
                'tickets-v2/' => 'history',
                'menus/' => 'settings',
                'menus-v2/' => 'settings',
                'app-settings/' => 'settings',
                'app-settings-v2/' => 'settings',
                'service-config/' => 'settings',
            ];

            $pathFromValue = function ($value) {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                $path = parse_url($value, PHP_URL_PATH);
                if (is_string($path) && $path !== '') {
                    return trim($path, '/');
                }

                return trim($value, '/');
            };

            $isUnsafeSidebarUrl = function ($value) {
                return preg_match('/^(?:[a-z][a-z0-9+\-.]*:|\/\/)/i', (string) $value) === 1;
            };

            $sidebarTarget = function ($rawUrl) use ($user, $pathFromValue, $isUnsafeSidebarUrl, $v2SidebarRouteMap, $v2SidebarActiveRouteMap, $v2SidebarActivePathMap, $v2SidebarActiveActionMap) {
                $rawUrl = trim((string) $rawUrl);
                if ($rawUrl === '' || $isUnsafeSidebarUrl($rawUrl)) {
                    return [
                        'href' => '#',
                        'path' => '',
                        'active_paths' => [],
                        'active_routes' => [],
                        'active_actions' => [],
                        'badge' => '',
                    ];
                }

                $rawPath = $pathFromValue($rawUrl);
                $mappedPath = \App\Support\V2Access::sidebarPathFor($rawPath, $user);
                $routeName = $v2SidebarRouteMap[$mappedPath] ?? null;
                $targetKeys = array_values(array_unique(array_filter([$rawPath, $mappedPath])));
                $activePaths = $targetKeys;
                $activeRoutes = array_filter([$routeName]);
                $activeActions = [];

                foreach ($targetKeys as $targetKey) {
                    $activePaths = array_merge($activePaths, $v2SidebarActivePathMap[$targetKey] ?? []);
                    $activeRoutes = array_merge($activeRoutes, $v2SidebarActiveRouteMap[$targetKey] ?? []);
                    $activeActions = array_merge($activeActions, $v2SidebarActiveActionMap[$targetKey] ?? []);
                }

                $href = ($routeName && \Illuminate\Support\Facades\Route::has($routeName))
                    ? route($routeName)
                    : url($mappedPath);

                return [
                    'href' => $href,
                    'path' => $mappedPath,
                    'active_paths' => array_values(array_unique(array_filter($activePaths))),
                    'active_routes' => array_values(array_unique(array_filter($activeRoutes))),
                    'active_actions' => array_values(array_unique(array_filter($activeActions))),
                    'badge' => '',
                ];
            };

            $matchesSidebarPattern = function ($value, $pattern) {
                $value = trim((string) $value);
                $pattern = trim((string) $pattern);

                if ($value === '' || $pattern === '') {
                    return false;
                }

                if ($value === $pattern) {
                    return true;
                }

                if (substr($pattern, -1) === '*') {
                    return strpos($value, rtrim($pattern, '*')) === 0;
                }

                return false;
            };

            $isSidebarActive = function (array $target) use ($matchesSidebarPattern) {
                $currentRouteName = \Illuminate\Support\Facades\Route::currentRouteName();
                foreach (($target['active_routes'] ?? []) as $routeName) {
                    if ($matchesSidebarPattern((string) $currentRouteName, (string) $routeName)) {
                        return true;
                    }
                }

                $currentRoute = \Illuminate\Support\Facades\Route::current();
                $currentAction = $currentRoute ? (string) $currentRoute->getActionName() : '';
                $currentAction = preg_replace('/^App\\\\Http\\\\Controllers\\\\/', '', $currentAction);

                foreach (($target['active_actions'] ?? []) as $actionPattern) {
                    if ($matchesSidebarPattern($currentAction, (string) $actionPattern)) {
                        return true;
                    }
                }

                foreach (($target['active_paths'] ?? []) as $path) {
                    if ($path !== '' && (request()->is($path) || request()->is($path . '/*'))) {
                        return true;
                    }
                }

                return false;
            };

            $sidebarTitle = function (array $item) {
                return (defined('ENABLE_MULTI_LANG') && ENABLE_MULTI_LANG == 1 && isset($item['trans_lang']['title'][session('locale')]))
                    ? $item['trans_lang']['title'][session('locale')]
                    : ($item['name'] ?? '');
            };

            $isFrenchSidebar = session('locale') === 'fr';
            $sidebarSectionLabels = $isFrenchSidebar
                ? [
                    'account' => 'Compte',
                    'services' => 'Services',
                    'history' => 'Historique',
                    'settings' => 'Paramètres',
                ]
                : [
                    'account' => 'Account',
                    'services' => 'Services',
                    'history' => 'History',
                    'settings' => 'Settings',
                ];

            $sidebarSectionFor = function (array $item, array $target) use ($v2SidebarSectionMap, $v2SidebarSectionPrefixMap) {
                $validSections = ['account', 'services', 'history', 'settings'];
                $storedSection = strtolower(trim((string) ($item['section'] ?? '')));

                if (in_array($storedSection, $validSections, true)) {
                    return $storedSection;
                }

                $paths = array_values(array_unique(array_filter(array_merge(
                    [(string) ($target['path'] ?? '')],
                    (array) ($target['active_paths'] ?? [])
                ))));

                foreach ($paths as $path) {
                    $path = strtolower(trim((string) $path, '/'));
                    if (isset($v2SidebarSectionMap[$path])) {
                        return $v2SidebarSectionMap[$path];
                    }

                    foreach ($v2SidebarSectionPrefixMap as $prefix => $section) {
                        if (strpos($path . '/', $prefix) === 0) {
                            return $section;
                        }
                    }
                }

                return 'services';

            };
        @endphp

        <div class="logo-wrapper">
            <a href="{{ $sidebarHomeUrl }}" aria-label="{{ $appName }} dashboard">
                <img class="img-fluid for-light" src="{{ $appLogoUrl }}" alt="{{ $appName }}">
                <img class="img-fluid for-dark" src="{{ $appLogoUrl }}" alt="{{ $appName }}">
            </a>
            <button type="button" class="back-btn" aria-label="Close sidebar" data-v2-no-tooltip="true">
                <i class="fa fa-angle-left"></i>
            </button>
        </div>

        <div class="logo-icon-wrapper">
            <a href="{{ $sidebarHomeUrl }}" aria-label="{{ $appName }} dashboard">
                <img class="img-fluid" src="{{ $appLogoUrl }}" alt="{{ $appName }} icon">
            </a>
        </div>

        <nav class="sidebar-main" aria-label="Sidebar navigation">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    @unless($hideRetailerDashboardSidebarCards)
                        <li class="sidebar-main-title text-center v2-sidebar-user-card">
                            @php
                                $defaultAvatarPath = 'images/avatar.png';
                                $defaultAvatarUrl = asset($defaultAvatarPath);
                                $avatarCacheKey = 'avatar_' . (optional($user)->id ?? 'guest') . '_' . optional(optional($user)->updated_at)->timestamp;
                                $image = Cache::remember($avatarCacheKey, 60, function () use ($user, $defaultAvatarPath) {
                                    if (!$user) {
                                        return $defaultAvatarPath;
                                    }

                                    $src_img = $user->getMedia('avatar')->first();
                                    if (!empty($src_img)) {
                                        return optional($src_img)->getUrl('thumb') ?: optional($src_img)->getUrl();
                                    }

                                    $legacyImage = trim((string) ($user->image ?? ''));
                                    if ($legacyImage !== '') {
                                        if (preg_match('/^(?:https?:)?\/\//i', $legacyImage)) {
                                            return $legacyImage;
                                        }

                                        $legacyImage = ltrim($legacyImage, '/');
                                        $legacyCandidates = [
                                            $legacyImage,
                                            'images/' . $legacyImage,
                                            'images/users/' . $legacyImage,
                                            'images/avatar/' . $legacyImage,
                                            'uploads/users/' . $legacyImage,
                                            'uploads/avatar/' . $legacyImage,
                                            'storage/' . $legacyImage,
                                        ];

                                        foreach ($legacyCandidates as $legacyCandidate) {
                                            if (file_exists(public_path($legacyCandidate))) {
                                                return $legacyCandidate;
                                            }
                                        }

                                        return $defaultAvatarPath;
                                    }

                                    return $defaultAvatarPath;
                                });
                                $avatarCandidate = trim((string) $image);
                                if (preg_match('/^\/\//', $avatarCandidate)) {
                                    $avatarUrl = request()->getScheme() . ':' . $avatarCandidate;
                                } elseif (preg_match('/^https?:\/\//i', $avatarCandidate)) {
                                    $avatarUrl = $avatarCandidate;
                                } else {
                                    $avatarPath = ltrim($avatarCandidate, '/');
                                    $avatarUrl = ($avatarPath !== '' && file_exists(public_path($avatarPath)))
                                        ? asset($avatarPath)
                                        : $defaultAvatarUrl;
                                }
                                $userName = trim((string) (optional($user)->username ?? optional($user)->name ?? ''));
                                $displayName = $userName !== '' ? ucfirst($userName) : 'User';
                                $groupName = session('userGroup') ?: (optional(optional($user)->group)->name ?? optional(App\Models\UserGroup::find(optional($user)->group_id))->name);
                                $showSidebarBalance = $user && !in_array($user->group_id, [1, 2, 6]);
                                $sidebarBalanceLabel = __('common.balance');
                                if ($sidebarBalanceLabel === 'common.balance') {
                                    $sidebarBalanceLabel = 'Balance';
                                }
                                $sidebarBalanceValue = $showSidebarBalance
                                    ? App\Library\AppHelper::getBalance($user->id, $user->currency, true)
                                    : null;
                            @endphp
                            <span class="v2-sidebar-avatar-wrap">
                                <img src="{{ $avatarUrl }}" class="img-responsive rounded-circle shadow-sm user-avatar" alt="{{ $displayName }} Avatar" onerror="this.onerror=null;this.src='{{ $defaultAvatarUrl }}';">
                                <span class="v2-sidebar-online-dot" aria-hidden="true"></span>
                            </span>
                            <div class="v2-sidebar-user-copy">
                                <h6 class="font-weight-bold user-name">{{ $displayName }}</h6>
                                <span class="small user-group">{{ $groupName ?? 'Member' }}</span>
                                @if($showSidebarBalance)
                                    <span class="v2-sidebar-inline-balance">
                                        <span class="balance-label">{{ $sidebarBalanceLabel }}</span>
                                        <strong class="balance-display" id="tamaBalance">{{ $sidebarBalanceValue }}</strong>
                                    </span>
                                @endif
                            </div>
                            <a class="v2-sidebar-profile-link" href="{{ $sidebarProfileUrl }}">
                                View profile
                            </a>
                        </li>
                    @endunless

                    @php
                        $sidebar = $user ? App\Library\AppHelper::menus('sidebar', 1, $user->group_id) : [];
                        $sidebarItems = [];
                        $seenSidebarTargets = [];

                        foreach ($sidebar as $menu) {
                            $menuTitle = trim((string) $sidebarTitle($menu));

                            if ($menuTitle === '') {
                                continue;
                            }

                            $menuTarget = $sidebarTarget($menu['url'] ?? '');
                            $menuChildren = is_array($menu['childs'] ?? null) ? $menu['childs'] : [];
                            $visibleMenuChildren = [];
                            $seenChildTargets = [];
                            $dedupeKey = strtolower(trim((string) ($menuTarget['path'] ?: $pathFromValue($menu['url'] ?? '') ?: $menuTitle)));

                            if ($dedupeKey !== '' && isset($seenSidebarTargets[$dedupeKey])) {
                                continue;
                            }

                            if ($dedupeKey !== '') {
                                $seenSidebarTargets[$dedupeKey] = true;
                            }

                            foreach ($menuChildren as $childCandidate) {
                                $childTitle = trim((string) $sidebarTitle($childCandidate));

                                if ($childTitle === '') {
                                    continue;
                                }

                                $childTarget = $sidebarTarget($childCandidate['url'] ?? '');
                                $childDedupeKey = strtolower(trim((string) ($childTarget['path'] ?: $pathFromValue($childCandidate['url'] ?? '') ?: $childTitle)));

                                if ($childDedupeKey !== '' && isset($seenChildTargets[$childDedupeKey])) {
                                    continue;
                                }

                                if ($childDedupeKey !== '') {
                                    $seenChildTargets[$childDedupeKey] = true;
                                }

                                $visibleMenuChildren[] = [
                                    'title' => $childTitle,
                                    'icon' => !empty($childCandidate['icon']) ? $childCandidate['icon'] : 'fa fa-circle',
                                    'target' => $childTarget,
                                    'active' => $isSidebarActive($childTarget),
                                ];
                            }

                            $menuChildren = $visibleMenuChildren;
                            $hasChildren = count($menuChildren) > 0;
                            $isMenuActive = $isSidebarActive($menuTarget);
                            $hasActiveChild = false;

                            foreach ($menuChildren as $childCheck) {
                                if (!empty($childCheck['active'])) {
                                    $hasActiveChild = true;
                                    break;
                                }
                            }

                            $isMenuOpen = $isMenuActive || $hasActiveChild;
                            $menuHref = $hasChildren ? '#' : $menuTarget['href'];
                            $menuIcon = !empty($menu['icon']) ? $menu['icon'] : 'fa fa-home';

                            $sidebarItems[] = [
                                'type' => 'menu',
                                'title' => $menuTitle,
                                'icon' => $menuIcon,
                                'href' => $menuHref,
                                'target' => $menuTarget,
                                'children' => $menuChildren,
                                'has_children' => $hasChildren,
                                'is_open' => $isMenuOpen,
                            ];
                        }

                        $hasSidebarItems = !empty($sidebarItems);
                    @endphp

                    @if(!$hasSidebarItems)
                        <li class="v2-sidebar-empty">
                            <span>No menu items available</span>
                        </li>
                    @else
                        @foreach($sidebarItems as $menuItem)
                            @php
                                $hasChildren = $menuItem['has_children'];
                                $isMenuOpen = $menuItem['is_open'];
                                $menuTarget = $menuItem['target'];
                                $menuChildren = $menuItem['children'];
                            @endphp
                            <li class="sidebar-list {{ $isMenuOpen ? 'active' : '' }} {{ $hasChildren ? 'dropdown' : '' }}">
                                <a href="{{ $menuItem['href'] }}"
                                   class="sidebar-link {{ $hasChildren ? 'sidebar-title' : 'link-nav' }} {{ $isMenuOpen ? 'active' : '' }}"
                                   @if($hasChildren) aria-expanded="{{ $isMenuOpen ? 'true' : 'false' }}" @endif
                                   @if(!$hasChildren && $isMenuOpen) aria-current="page" @endif>
                                    <span class="v2-sidebar-icon-frame">
                                        <i class="{{ $menuItem['icon'] }} dynamic-icon"></i>
                                    </span>
                                    <span class="v2-sidebar-label">{{ $menuItem['title'] }}</span>
                                    @if($hasChildren || !empty($menuTarget['badge']))
                                        <span class="v2-sidebar-trailing">
                                            @if($hasChildren)
                                                <span class="v2-sidebar-child-count">{{ count($menuChildren) }}</span>
                                            @endif
                                            @if(!empty($menuTarget['badge']))
                                                <span class="v2-sidebar-menu-badge {{ $menuItem['badge_class'] ?? '' }}">{{ $menuTarget['badge'] }}</span>
                                            @endif
                                            @if($hasChildren)
                                                <span class="according-menu" aria-hidden="true">
                                                    <i class="fa fa-angle-{{ $isMenuOpen ? 'down' : 'right' }}"></i>
                                                </span>
                                            @endif
                                        </span>
                                    @endif
                                </a>

                                @if($hasChildren)
                                    <ul class="sidebar-submenu" style="display: {{ $isMenuOpen ? 'block' : 'none' }};">
                                        @foreach ($menuChildren as $menu2)
                                            <li class="nav-item {{ $menu2['active'] ? 'active' : '' }}">
                                                <a href="{{ $menu2['target']['href'] }}"
                                                   class="li-a {{ $menu2['active'] ? 'active' : '' }}"
                                                   @if($menu2['active']) aria-current="page" @endif>
                                                    <span class="v2-sidebar-child-icon-frame">
                                                        <i class="{{ $menu2['icon'] }}"></i>
                                                    </span>
                                                    <span>{{ $menu2['title'] }}</span>
                                                    @if(!empty($menu2['target']['badge']))
                                                        <span class="v2-sidebar-menu-badge">{{ $menu2['target']['badge'] }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </nav>
    </div>
</div>
