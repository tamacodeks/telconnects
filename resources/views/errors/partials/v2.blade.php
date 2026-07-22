@php
    $statusMeta = [
        '400' => ['title' => 'Bad Request', 'message' => 'The request could not be completed.', 'description' => 'The information sent to the server is invalid or incomplete. Please review the request and try again.', 'icon' => 'warning', 'tone' => 'warning'],
        '401' => ['title' => 'Unauthorized', 'message' => 'You need to sign in before continuing.', 'description' => 'Your session may have expired or this resource requires authentication.', 'icon' => 'key', 'tone' => 'danger'],
        '402' => ['title' => 'Payment Required', 'message' => 'This request requires a valid payment state.', 'description' => 'Please verify the account, balance, or billing status before trying again.', 'icon' => 'card', 'tone' => 'warning'],
        '403' => ['title' => 'Access Denied', 'message' => "You don't have permission to access this page.", 'description' => 'If you believe this is a mistake, please contact your administrator or return to your dashboard.', 'icon' => 'lock', 'tone' => 'danger'],
        '404' => ['title' => 'Page Not Found', 'message' => "The page you're looking for doesn't exist or has been moved.", 'description' => 'Check the address, return to your dashboard, or use the navigation to continue.', 'icon' => 'search', 'tone' => 'info'],
        '405' => ['title' => 'Method Not Allowed', 'message' => 'This page does not support the current request method.', 'description' => 'Please go back and submit the action from the correct page or workflow.', 'icon' => 'ban', 'tone' => 'danger'],
        '406' => ['title' => 'Not Acceptable', 'message' => 'The requested response format is not available.', 'description' => 'Refresh the page or try again with a supported request format.', 'icon' => 'file', 'tone' => 'warning'],
        '407' => ['title' => 'Proxy Authentication Required', 'message' => 'Proxy authentication is required before this request can continue.', 'description' => 'Please verify your network or proxy credentials and try again.', 'icon' => 'key', 'tone' => 'warning'],
        '408' => ['title' => 'Request Timeout', 'message' => 'The request took too long to complete.', 'description' => 'Please check the connection and retry the request.', 'icon' => 'clock', 'tone' => 'warning'],
        '409' => ['title' => 'Conflict', 'message' => 'This request conflicts with the current data state.', 'description' => 'Refresh the page to get the latest information, then try the action again.', 'icon' => 'shuffle', 'tone' => 'warning'],
        '410' => ['title' => 'Gone', 'message' => 'This resource is no longer available.', 'description' => 'The page or record may have been permanently removed from the system.', 'icon' => 'history', 'tone' => 'info'],
        '411' => ['title' => 'Length Required', 'message' => 'The request is missing required length information.', 'description' => 'Please retry the action from the original form or page.', 'icon' => 'file', 'tone' => 'warning'],
        '412' => ['title' => 'Precondition Failed', 'message' => 'A required condition for this request was not met.', 'description' => 'Refresh the page and confirm the latest data before trying again.', 'icon' => 'check', 'tone' => 'warning'],
        '413' => ['title' => 'Payload Too Large', 'message' => 'The submitted data is too large.', 'description' => 'Reduce the file size or amount of submitted data and try again.', 'icon' => 'database', 'tone' => 'warning'],
        '414' => ['title' => 'URI Too Long', 'message' => 'The requested address is too long.', 'description' => 'Shorten the URL or retry the request from the application interface.', 'icon' => 'link', 'tone' => 'warning'],
        '415' => ['title' => 'Unsupported Media Type', 'message' => 'This file or request format is not supported.', 'description' => 'Please use a supported format and submit the request again.', 'icon' => 'file', 'tone' => 'warning'],
        '416' => ['title' => 'Range Not Satisfiable', 'message' => 'The requested content range cannot be served.', 'description' => 'Refresh the page or retry the download from the beginning.', 'icon' => 'arrows', 'tone' => 'warning'],
        '417' => ['title' => 'Expectation Failed', 'message' => 'The server could not meet the expectation in this request.', 'description' => 'Please retry the request or contact support if the issue continues.', 'icon' => 'question', 'tone' => 'warning'],
        '418' => ['title' => 'Request Cannot Be Served', 'message' => 'This request cannot be completed in its current form.', 'description' => 'Return to the previous page and try again with a valid request.', 'icon' => 'warning', 'tone' => 'info'],
        '419' => ['title' => 'Session Expired', 'message' => 'Your session token has expired.', 'description' => 'Refresh the page or sign in again before submitting the form.', 'icon' => 'hourglass', 'tone' => 'warning'],
        '421' => ['title' => 'Misdirected Request', 'message' => 'The request was sent to a destination that cannot handle it.', 'description' => 'Refresh the page and try again from the application.', 'icon' => 'shuffle', 'tone' => 'warning'],
        '422' => ['title' => 'Unable To Process Request', 'message' => 'Some submitted information is invalid.', 'description' => 'Review the form values, correct the highlighted fields, and try again.', 'icon' => 'file-warning', 'tone' => 'warning'],
        '423' => ['title' => 'Locked', 'message' => 'This resource is locked right now.', 'description' => 'The record may be protected by another process or permission rule.', 'icon' => 'lock', 'tone' => 'danger'],
        '424' => ['title' => 'Failed Dependency', 'message' => 'A required related action failed.', 'description' => 'Please retry the workflow from the beginning or contact support.', 'icon' => 'unlink', 'tone' => 'warning'],
        '425' => ['title' => 'Too Early', 'message' => 'The request was sent too early to process safely.', 'description' => 'Wait a moment and retry the action.', 'icon' => 'hourglass', 'tone' => 'warning'],
        '426' => ['title' => 'Upgrade Required', 'message' => 'This request requires an upgraded connection or client.', 'description' => 'Update the client or retry from a supported browser.', 'icon' => 'upload', 'tone' => 'warning'],
        '428' => ['title' => 'Precondition Required', 'message' => 'This request needs an additional condition before it can continue.', 'description' => 'Refresh the page and retry the action with the latest data.', 'icon' => 'shield', 'tone' => 'warning'],
        '429' => ['title' => 'Too Many Requests', 'message' => 'Too many requests were made in a short time.', 'description' => 'Please wait a moment before retrying.', 'icon' => 'speedometer', 'tone' => 'warning'],
        '431' => ['title' => 'Request Headers Too Large', 'message' => 'The request headers are too large.', 'description' => 'Clear browser data or retry with a smaller request.', 'icon' => 'bars', 'tone' => 'warning'],
        '451' => ['title' => 'Unavailable For Legal Reasons', 'message' => 'This resource cannot be displayed because of a legal or policy restriction.', 'description' => 'Contact your administrator if you believe access should be available.', 'icon' => 'gavel', 'tone' => 'danger'],
        '500' => ['title' => 'Internal Server Error', 'message' => 'Our servers encountered an unexpected error.', 'description' => 'The issue has interrupted this request. Please retry or contact support if it continues.', 'icon' => 'server', 'tone' => 'danger'],
        '501' => ['title' => 'Not Implemented', 'message' => 'This request is not supported by the server.', 'description' => 'The requested capability is not available in the current application version.', 'icon' => 'code', 'tone' => 'warning'],
        '502' => ['title' => 'Bad Gateway', 'message' => 'The server received an invalid response from an upstream service.', 'description' => 'Please retry the request. If the issue continues, contact your administrator.', 'icon' => 'plug', 'tone' => 'danger'],
        '503' => ['title' => 'Service Unavailable', 'message' => "We're performing maintenance or the service is temporarily unavailable.", 'description' => 'Please try again shortly. Scheduled maintenance windows may affect access.', 'icon' => 'wrench', 'tone' => 'warning'],
        '504' => ['title' => 'Gateway Timeout', 'message' => 'The upstream service took too long to respond.', 'description' => 'Please retry the request after a moment.', 'icon' => 'clock', 'tone' => 'warning'],
        '505' => ['title' => 'HTTP Version Not Supported', 'message' => 'The HTTP version used by this request is not supported.', 'description' => 'Retry from a supported browser or network client.', 'icon' => 'globe', 'tone' => 'warning'],
        '506' => ['title' => 'Variant Also Negotiates', 'message' => 'The server detected a configuration conflict while processing the request.', 'description' => 'Please contact your administrator if this page keeps appearing.', 'icon' => 'shuffle', 'tone' => 'danger'],
        '507' => ['title' => 'Insufficient Storage', 'message' => 'The server does not have enough storage to complete the request.', 'description' => 'Please retry later or contact support.', 'icon' => 'database', 'tone' => 'danger'],
        '508' => ['title' => 'Loop Detected', 'message' => 'The server detected a processing loop.', 'description' => 'Please contact your administrator if this issue persists.', 'icon' => 'refresh', 'tone' => 'danger'],
        '510' => ['title' => 'Not Extended', 'message' => 'Additional request extensions are required.', 'description' => 'Retry the workflow from the application or contact support.', 'icon' => 'puzzle', 'tone' => 'warning'],
        '511' => ['title' => 'Network Authentication Required', 'message' => 'Network authentication is required before continuing.', 'description' => 'Sign in to the network or captive portal, then try again.', 'icon' => 'wifi', 'tone' => 'warning'],
    ];

    $statusCode = (string) ($status_code ?? $statusCode ?? $errorCode ?? '500');
    $status = $statusMeta[$statusCode] ?? ['title' => 'Unexpected Error', 'message' => 'The request could not be completed.', 'description' => 'Please retry the request or contact your administrator if the issue continues.', 'icon' => 'warning', 'tone' => 'danger'];

    $appName = defined('APP_NAME') ? APP_NAME : config('app.name', 'DEMAT Pro');
    $logoFile = (defined('APP_LOGO') && APP_LOGO && file_exists(public_path('images/' . APP_LOGO))) ? APP_LOGO : 'logo.png';
    $logoUrl = asset('images/' . $logoFile);
    $errorTitle = $error_title ?? $status['title'];
    $errorMessage = $error_message ?? $status['message'];
    $errorDescription = $error_description ?? $status['description'];
    $heroIcon = $hero_icon ?? $status['icon'];
    $errorTone = $error_tone ?? $errorTone ?? $status['tone'];
    $requestedUrl = $requested_url ?? $requestedUrl ?? request()->fullUrl();
    $requestMethod = $request_method ?? $requestMethod ?? request()->method();
    $requiredPermission = $required_permission ?? $requiredPermission ?? request()->attributes->get('required_permission') ?? session('required_permission');
    $supportEmail = $support_email ?? $supportEmail ?? config('mail.from.address');
    $supportPhone = $support_phone ?? $supportPhone ?? config('services.support.phone');
    if (in_array(strtolower((string) $supportEmail), ['hello@example.com', 'support@example.com', 'admin@example.com'], true)) {
        $supportEmail = null;
    }
    $timestamp = $timestamp ?? date('d M Y, H:i:s T');
    $environment = $environment ?? config('app.env');
    $requestId = $request_id ?? $requestId ?? request()->headers->get('X-Request-ID') ?? request()->attributes->get('request_id') ?? session('request_id');

    if (!$requestId) {
        $requestId = strtoupper(substr(hash('sha1', $requestMethod . '|' . $requestedUrl . '|' . date('YmdHi')), 0, 10));
    }

    $requestId = (string) $requestId;
    $requestId = strpos($requestId, '#') === 0 ? $requestId : '#' . strtoupper($requestId);

    $user = null;
    try {
        $user = auth()->user();
    } catch (\Throwable $e) {
        $user = null;
    }

    $currentRole = $current_role ?? $currentRole ?? null;
    if (!$currentRole && $user) {
        $currentRole = $user->username ?: 'Signed-in User';

        try {
            if (!empty($user->group_id)) {
                $groupName = \App\Models\UserGroup::where('id', $user->group_id)->value('name');
                $currentRole = $groupName ?: $currentRole;
            }
        } catch (\Throwable $e) {
            $currentRole = $user->username ?: $currentRole;
        }
    }

    $dashboardUrl = $dashboard_url ?? $dashboardUrl ?? null;
    if (!$dashboardUrl && $user) {
        $dashboardUrl = (\Illuminate\Support\Facades\Route::has('dashboard.v2') && \App\Support\V2Access::userCanUseV2($user))
            ? route('dashboard.v2')
            : url('dashboard');
    }

    $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('login');
    $homeUrl = $home_url ?? $homeUrl ?? ($dashboardUrl ?: url('/'));
    $retryableStatuses = ['408', '409', '419', '425', '429', '500', '502', '503', '504'];
    $authStatuses = ['401', '419', '511'];

    $detailItems = [
        ['label' => 'Current Role', 'value' => $currentRole, 'icon' => 'user'],
        ['label' => 'Required Permission', 'value' => $requiredPermission, 'icon' => 'shield'],
        ['label' => 'Requested URL', 'value' => $requestedUrl, 'icon' => 'link', 'wide' => true],
        ['label' => 'Request Method', 'value' => $requestMethod, 'icon' => 'exchange'],
        ['label' => 'Error Type', 'value' => 'HTTP ' . $statusCode, 'icon' => 'info'],
        ['label' => 'Request ID', 'value' => $requestId, 'icon' => 'hash'],
        ['label' => 'Timestamp', 'value' => $timestamp, 'icon' => 'clock'],
        ['label' => 'Environment', 'value' => $environment, 'icon' => 'cube'],
    ];
    $detailItems = array_values(array_filter($detailItems, function ($item) {
        return isset($item['value']) && trim((string) $item['value']) !== '';
    }));

    $actions = [];
    if ($dashboardUrl) {
        $actions[] = ['label' => 'Back to Dashboard', 'url' => $dashboardUrl, 'icon' => 'home', 'style' => 'primary'];
    } else {
        $actions[] = ['label' => 'Home', 'url' => url('/'), 'icon' => 'home', 'style' => 'primary'];
    }
    $actions[] = ['label' => 'Go Back', 'url' => 'javascript:history.back()', 'icon' => 'arrow-left', 'style' => 'secondary'];
    if (in_array($statusCode, $retryableStatuses, true)) {
        $actions[] = ['label' => 'Refresh', 'url' => $requestedUrl, 'icon' => 'refresh', 'style' => 'ghost'];
    }
    if (in_array($statusCode, $authStatuses, true) || !$user) {
        $actions[] = ['label' => 'Login Again', 'url' => $loginUrl, 'icon' => 'key', 'style' => 'ghost'];
    }
    if ($supportEmail) {
        $actions[] = ['label' => 'Contact Administrator', 'url' => 'mailto:' . $supportEmail, 'icon' => 'mail', 'style' => 'ghost'];
    }

    $themeMode = strtolower((string) session('theme', session('app_theme', 'light')));
    $isDarkTheme = in_array($themeMode, ['dark', 'dark-mode', 'dark_only', 'dark-only'], true);

    $iconSvg = function ($name, $class = '') {
        $icons = [
            'alert-circle' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>',
            'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>',
            'arrows' => '<polyline points="7 7 3 12 7 17"></polyline><polyline points="17 7 21 12 17 17"></polyline><line x1="3" y1="12" x2="21" y2="12"></line>',
            'ban' => '<circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>',
            'bars' => '<line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line>',
            'card' => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="7" y1="15" x2="10" y2="15"></line>',
            'check' => '<path d="M20 6 9 17l-5-5"></path>',
            'clock' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
            'code' => '<polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline>',
            'cube' => '<path d="m21 16-9 5-9-5V8l9-5 9 5v8Z"></path><path d="m3.3 7.5 8.7 5 8.7-5"></path><path d="M12 22V12"></path>',
            'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"></ellipse><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"></path><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"></path>',
            'exchange' => '<polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><polyline points="14 2 14 8 20 8"></polyline>',
            'file-warning' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="11" x2="12" y2="15"></line><line x1="12" y1="18" x2="12.01" y2="18"></line>',
            'gavel' => '<path d="m14 13-7 7"></path><path d="m7 7 10 10"></path><path d="m10 4 10 10"></path><path d="m4 10 10 10"></path>',
            'globe' => '<circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 0 20"></path><path d="M12 2a15.3 15.3 0 0 0 0 20"></path>',
            'hash' => '<line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line>',
            'history' => '<path d="M3 12a9 9 0 1 0 3-6.7"></path><polyline points="3 3 3 9 9 9"></polyline><polyline points="12 7 12 12 16 14"></polyline>',
            'home' => '<path d="m3 11 9-8 9 8"></path><path d="M5 10v10h14V10"></path><path d="M9 20v-6h6v6"></path>',
            'hourglass' => '<path d="M6 2h12"></path><path d="M6 22h12"></path><path d="M7 2v6a5 5 0 0 0 10 0V2"></path><path d="M7 22v-6a5 5 0 0 1 10 0v6"></path>',
            'info' => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
            'key' => '<circle cx="7.5" cy="15.5" r="3.5"></circle><path d="m10 13 9-9"></path><path d="m15 4 5 5"></path><path d="m17 6-2-2"></path>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.07 0l2-2a5 5 0 0 0-7.07-7.07l-1.15 1.15"></path><path d="M14 11a5 5 0 0 0-7.07 0l-2 2A5 5 0 0 0 12 20.07l1.15-1.15"></path>',
            'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path>',
            'plug' => '<path d="M12 22v-5"></path><path d="M9 8V2"></path><path d="M15 8V2"></path><path d="M6 8h12v3a6 6 0 0 1-12 0V8Z"></path>',
            'puzzle' => '<path d="M20 13v5a2 2 0 0 1-2 2h-5v-3a2 2 0 0 0-4 0v3H4a2 2 0 0 1-2-2v-5h3a2 2 0 0 0 0-4H2V4a2 2 0 0 1 2-2h5v3a2 2 0 0 0 4 0V2h5a2 2 0 0 1 2 2v5h-3a2 2 0 0 0 0 4h3Z"></path>',
            'question' => '<circle cx="12" cy="12" r="10"></circle><path d="M9.1 9a3 3 0 1 1 5.8 1c-.7 1.1-2.9 1.6-2.9 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>',
            'refresh' => '<polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.5 9a9 9 0 0 1 14.8-3.4L23 10"></path><path d="M20.5 15a9 9 0 0 1-14.8 3.4L1 14"></path>',
            'search' => '<circle cx="11" cy="11" r="7"></circle><line x1="16.65" y1="16.65" x2="21" y2="21"></line>',
            'server' => '<rect x="3" y="4" width="18" height="7" rx="2"></rect><rect x="3" y="13" width="18" height="7" rx="2"></rect><line x1="7" y1="7.5" x2="7.01" y2="7.5"></line><line x1="7" y1="16.5" x2="7.01" y2="16.5"></line>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path>',
            'shuffle' => '<polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line><polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line><line x1="4" y1="4" x2="9" y2="9"></line>',
            'speedometer' => '<path d="M12 3a9 9 0 0 1 9 9"></path><path d="M3 12a9 9 0 0 1 9-9"></path><path d="M12 12l4-4"></path><path d="M4 19h16"></path><circle cx="12" cy="12" r="2"></circle>',
            'unlink' => '<path d="M17 7h1a5 5 0 0 1 0 10h-3"></path><path d="M7 17H6A5 5 0 0 1 6 7h3"></path><line x1="8" y1="12" x2="16" y2="12"></line><line x1="3" y1="3" x2="21" y2="21"></line>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line>',
            'user' => '<circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path>',
            'warning' => '<path d="m12 3 10 18H2L12 3Z"></path><line x1="12" y1="9" x2="12" y2="14"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
            'wifi' => '<path d="M5 13a10 10 0 0 1 14 0"></path><path d="M8.5 16.5a5 5 0 0 1 7 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line>',
            'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-3 3-3-3 3-3Z"></path>',
        ];
        $paths = $icons[$name] ?? $icons['warning'];
        $classAttr = trim('error-svg ' . $class);

        return '<svg class="' . e($classAttr) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
    };
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() ?: 'en' }}" class="{{ $isDarkTheme ? 'dark' : '' }}" data-bs-theme="{{ $isDarkTheme ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $errorTitle }} | {{ $appName }}</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    @include('v2.layout.theme', ['themeContext' => 'app'])
    <script>
        (function () {
            try {
                var storedTheme = window.localStorage.getItem('theme') || window.localStorage.getItem('app_theme') || window.localStorage.getItem('v2-theme');
                if (storedTheme && /dark/i.test(storedTheme)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                }
            } catch (error) {}
        })();
    </script>
    <style>
        :root {
            --bg: var(--theme-dashboard-bg, #F4F8FC);
            --bg-strong: rgba(var(--theme-dashboard-bg-rgb, 244, 248, 252), .94);
            --panel: var(--theme-dashboard-card, #FFFFFF);
            --panel-soft: rgba(var(--theme-dashboard-card-rgb, 255, 255, 255), .82);
            --text: var(--theme-dashboard-text, #1F2937);
            --muted: var(--theme-dashboard-muted, #6B7280);
            --border: rgba(var(--theme-dashboard-border-rgb, 216, 227, 238), .94);
            --primary: var(--theme-primary, #1764A8);
            --primary-rgb: var(--theme-primary-rgb, 23, 100, 168);
            --brand-accent: var(--theme-accent, #1DABF2);
            --brand-accent-rgb: var(--theme-accent-rgb, 29, 171, 242);
            --danger: #EF4444;
            --warning: #F59E0B;
            --success: #10B981;
            --info: var(--brand-accent);
            --grid-rgb: var(--theme-dashboard-text-rgb, 31, 41, 55);
            --button-bg: var(--theme-button-bg, var(--primary));
            --button-rgb: var(--theme-button-rgb, var(--primary-rgb));
            --button-text: var(--theme-button-text, #FFFFFF);
            --accent: var(--danger);
            --accent-soft: rgba(239, 68, 68, .16);
            --accent-border: rgba(239, 68, 68, .28);
        }

        html.dark,
        html[data-bs-theme="dark"],
        body.dark-mode,
        body.dark-only,
        body[data-bs-theme="dark"] {
            --bg: var(--theme-dark-surface, #161311);
            --bg-strong: rgba(var(--theme-dark-surface-rgb, 22, 19, 17), .96);
            --panel: var(--theme-dark-card, #221A16);
            --panel-soft: rgba(var(--theme-dark-card-rgb, 34, 26, 22), .88);
            --text: var(--theme-dark-text, #F5F5F5);
            --muted: var(--theme-dark-muted, #A8A8A8);
            --border: rgba(var(--theme-dark-border-rgb, 58, 42, 34), .92);
            --grid-rgb: var(--theme-dark-text-rgb, 245, 245, 245);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            font-family: "Inter", "Rubik", -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 18% 10%, rgba(var(--primary-rgb), .16), transparent 26%),
                radial-gradient(circle at 86% 12%, rgba(var(--brand-accent-rgb), .12), transparent 24%),
                linear-gradient(135deg, var(--bg) 0%, var(--bg-strong) 54%, var(--panel) 100%);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(var(--grid-rgb), .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(var(--grid-rgb), .035) 1px, transparent 1px),
                radial-gradient(circle, rgba(var(--grid-rgb), .12) 1px, transparent 1.5px);
            background-size: 48px 48px, 48px 48px, 120px 120px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .72), transparent 82%);
        }

        .error-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .error-page.is-warning {
            --accent: var(--warning);
            --accent-soft: rgba(245, 158, 11, .16);
            --accent-border: rgba(245, 158, 11, .28);
        }

        .error-page.is-info {
            --accent: var(--info);
            --accent-soft: rgba(14, 165, 233, .16);
            --accent-border: rgba(14, 165, 233, .28);
        }

        .error-page.is-success {
            --accent: var(--success);
            --accent-soft: rgba(16, 185, 129, .15);
            --accent-border: rgba(16, 185, 129, .28);
        }

        .error-card {
            position: relative;
            width: min(100%, 1080px);
            border: 1px solid var(--border);
            border-radius: 20px;
            background:
                linear-gradient(145deg, var(--panel-soft), rgba(var(--primary-rgb), .035)),
                var(--panel);
            box-shadow: 0 30px 100px rgba(var(--primary-rgb), .14);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            overflow: hidden;
            animation: cardIn .45s ease both;
        }

        .error-card::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 24% 24%, var(--accent-soft), transparent 28%),
                linear-gradient(180deg, rgba(var(--grid-rgb), .035), transparent 34%);
        }

        .error-card-inner {
            position: relative;
            z-index: 1;
            padding: 28px;
        }

        .error-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 40px;
        }

        .error-logo-shell {
            width: 178px;
            height: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 16px;
            background: rgba(248, 250, 252, .96);
            box-shadow: 0 16px 38px rgba(var(--primary-rgb), .14);
        }

        .error-logo {
            max-width: 146px;
            max-height: 44px;
            object-fit: contain;
        }

        .error-status-badge {
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            border: 1px solid var(--accent-border);
            border-radius: 999px;
            color: var(--text);
            background: var(--accent-soft);
            font-size: 13px;
            font-weight: 850;
            letter-spacing: .05em;
            animation: badgeIn .5s ease both;
        }

        .error-status-badge::before {
            content: "";
            width: 8px;
            height: 8px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 0 0 5px var(--accent-soft);
        }

        .error-hero {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 44px;
            align-items: center;
        }

        .error-illustration {
            position: relative;
            min-height: 248px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-illustration::before {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            border-radius: 999px;
            background: var(--accent-soft);
            filter: blur(34px);
        }

        .error-illustration-orb {
            position: relative;
            width: 188px;
            height: 188px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 999px;
            color: var(--accent);
            background:
                radial-gradient(circle at 34% 24%, rgba(var(--grid-rgb), .11), transparent 29%),
                linear-gradient(145deg, var(--panel), var(--bg-strong));
            box-shadow:
                inset 0 1px 0 rgba(var(--grid-rgb), .08),
                0 26px 70px rgba(var(--primary-rgb), .16),
                0 0 50px var(--accent-soft);
            animation: floatIcon 4s ease-in-out infinite;
        }

        .error-svg {
            display: inline-block;
            flex: 0 0 auto;
        }

        .error-hero-svg {
            width: 92px;
            height: 92px;
            filter: drop-shadow(0 14px 30px var(--accent-soft));
        }

        .error-eyebrow {
            margin: 0 0 10px;
            color: var(--accent);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .error-title {
            margin: 0;
            color: var(--text);
            font-size: clamp(48px, 6.7vw, 80px);
            line-height: .92;
            font-weight: 900;
            letter-spacing: 0;
        }

        .error-title span {
            display: block;
            margin-top: 10px;
            font-size: clamp(34px, 4.3vw, 56px);
            line-height: 1.05;
        }

        .error-message {
            max-width: 680px;
            margin: 20px 0 0;
            color: var(--text);
            font-size: 22px;
            line-height: 1.45;
            font-weight: 650;
        }

        .error-description {
            max-width: 700px;
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.7;
        }

        .error-details {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1px;
            margin-top: 34px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--border);
            overflow: hidden;
        }

        .error-detail {
            min-width: 0;
            display: flex;
            gap: 12px;
            padding: 16px;
            background: linear-gradient(180deg, var(--panel-soft), var(--bg-strong));
        }

        .error-detail.is-wide {
            grid-column: span 2;
        }

        .error-detail-icon {
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: var(--accent);
            background: var(--accent-soft);
        }

        .error-detail-icon .error-svg {
            width: 15px;
            height: 15px;
        }

        .error-detail-label {
            margin: 0 0 6px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .error-detail-value {
            margin: 0;
            color: var(--text);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .error-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .error-btn {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 18px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 850;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease, color .2s ease;
        }

        .error-btn .error-svg {
            width: 17px;
            height: 17px;
        }

        .error-btn:hover,
        .error-btn:focus,
        .error-btn:active {
            text-decoration: none;
            transform: translateY(-1px) scale(1.02);
            outline: none;
        }

        .error-btn:focus-visible {
            box-shadow: 0 0 0 4px rgba(var(--button-rgb), .24);
        }

        .error-btn-primary {
            color: var(--button-text);
            border-color: rgba(var(--button-rgb), .55);
            background: linear-gradient(135deg, var(--button-bg), var(--primary));
            box-shadow: 0 14px 30px rgba(var(--button-rgb), .24);
        }

        .error-btn-primary:hover,
        .error-btn-primary:focus {
            color: var(--button-text);
            box-shadow: 0 18px 38px rgba(var(--button-rgb), .30);
        }

        .error-btn-secondary {
            color: var(--text);
            background: rgba(var(--grid-rgb), .045);
        }

        .error-btn-secondary:hover,
        .error-btn-secondary:focus {
            color: var(--text);
            border-color: rgba(var(--primary-rgb), .34);
            background: rgba(var(--primary-rgb), .08);
        }

        .error-btn-ghost {
            color: var(--primary);
            background: rgba(var(--primary-rgb), .07);
        }

        .error-btn-ghost:hover,
        .error-btn-ghost:focus {
            color: var(--primary);
            border-color: rgba(var(--primary-rgb), .45);
            background: rgba(var(--primary-rgb), .13);
        }

        .error-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .error-footer strong {
            color: var(--text);
            font-weight: 850;
        }

        .error-footer a {
            color: var(--primary);
            font-weight: 800;
            text-decoration: none;
        }

        .error-footer a:hover,
        .error-footer a:focus {
            color: var(--primary);
            text-decoration: underline;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes badgeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIcon {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        @media (max-width: 991.98px) {
            .error-card-inner {
                padding: 24px;
            }

            .error-hero {
                grid-template-columns: 1fr;
                gap: 22px;
                text-align: center;
            }

            .error-message,
            .error-description {
                margin-left: auto;
                margin-right: auto;
            }

            .error-actions {
                justify-content: center;
            }

            .error-details {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .error-shell {
                align-items: flex-start;
                padding: 16px;
            }

            .error-card-inner {
                padding: 18px;
            }

            .error-header {
                margin-bottom: 26px;
            }

            .error-logo-shell {
                width: 138px;
                height: 50px;
                padding: 7px 12px;
            }

            .error-logo {
                max-width: 114px;
            }

            .error-status-badge {
                height: 32px;
                padding: 0 11px;
                font-size: 12px;
            }

            .error-illustration {
                min-height: 170px;
            }

            .error-illustration-orb {
                width: 138px;
                height: 138px;
            }

            .error-hero-svg {
                width: 70px;
                height: 70px;
            }

            .error-title {
                font-size: 48px;
            }

            .error-title span {
                font-size: 32px;
            }

            .error-message {
                font-size: 18px;
            }

            .error-description {
                font-size: 16px;
            }

            .error-details {
                grid-template-columns: 1fr;
            }

            .error-detail.is-wide {
                grid-column: auto;
            }

            .error-btn {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body class="{{ $isDarkTheme ? 'dark-mode dark-only' : '' }}" data-bs-theme="{{ $isDarkTheme ? 'dark' : 'light' }}">
    <main class="error-shell" role="main">
        <section class="error-page is-{{ $errorTone }}">
            <div class="error-card" aria-labelledby="httpErrorTitle">
                <div class="error-card-inner">
                    <header class="error-header">
                        <div class="error-logo-shell">
                            <img class="error-logo" src="{{ $logoUrl }}" alt="{{ $appName }}">
                        </div>
                        <span class="error-status-badge" aria-label="HTTP status {{ $statusCode }}">HTTP {{ $statusCode }}</span>
                    </header>

                    <div class="error-hero">
                        <div class="error-illustration" aria-hidden="true">
                            <div class="error-illustration-orb">
                                {!! $iconSvg($heroIcon, 'error-hero-svg') !!}
                            </div>
                        </div>

                        <div>
                            <p class="error-eyebrow">HTTP {{ $statusCode }}</p>
                            <h1 class="error-title" id="httpErrorTitle">
                                {{ $statusCode }}
                                <span>{{ $errorTitle }}</span>
                            </h1>
                            <p class="error-message">{{ $errorMessage }}</p>
                            <p class="error-description">{{ $errorDescription }}</p>
                        </div>
                    </div>

                    @if(count($detailItems))
                        <section class="error-details" aria-label="Request details">
                            @foreach($detailItems as $item)
                                <div class="error-detail {{ !empty($item['wide']) ? 'is-wide' : '' }}">
                                    <span class="error-detail-icon" aria-hidden="true">
                                        {!! $iconSvg($item['icon']) !!}
                                    </span>
                                    <div>
                                        <p class="error-detail-label">{{ $item['label'] }}</p>
                                        <p class="error-detail-value">{{ $item['value'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </section>
                    @endif

                    <div class="error-actions" aria-label="Page actions">
                        @foreach($actions as $action)
                            <a class="error-btn error-btn-{{ $action['style'] }}" href="{{ $action['url'] }}">
                                {!! $iconSvg($action['icon']) !!}
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <footer class="error-footer">
                        <strong>Need help?</strong>
                        @if($supportEmail || $supportPhone)
                            Contact your system administrator
                            @if($supportEmail)
                                at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                            @endif
                            @if($supportPhone)
                                {{ $supportEmail ? 'or ' : 'at ' }}<a href="tel:{{ preg_replace('/\s+/', '', $supportPhone) }}">{{ $supportPhone }}</a>
                            @endif
                            .
                        @else
                            Please contact your system administrator.
                        @endif
                    </footer>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
