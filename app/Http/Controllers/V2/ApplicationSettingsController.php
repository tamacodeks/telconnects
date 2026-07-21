<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use app\Library\AppHelper;
use app\Library\DBHelper;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Validator;

class ApplicationSettingsController extends Controller
{
    public function index(Request $request)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        return view('v2.app.settings.index', [
            'page_title' => 'Application Settings',
            'currencies' => Currency::orderBy('title')->pluck('title', 'code'),
            'timezones' => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
            'recordMethods' => DBHelper::record_methods(),
            'settings' => $this->settingsPayload(),
        ]);
    }

    public function save(Request $request)
    {
        if ($denied = $this->denyUnlessRoot($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:120',
            'app_logo' => 'nullable|file|mimes:jpeg,jpg,png,gif,bmp,webp|max:2048',
            'app_currency' => 'required|string|max:10',
            'app_lang' => 'required|string|max:10',
            'app_timezone' => 'required|string|max:120',
            'dashboard_welcome_prefix' => 'nullable|string|max:120',
            'dashboard_welcome_subtitle' => 'nullable|string|max:255',
            'dashboard_feature_secure' => 'nullable|string|max:255',
            'dashboard_feature_instant' => 'nullable|string|max:255',
            'dashboard_feature_support' => 'nullable|string|max:255',
            'per_page' => 'required|integer|min:10|max:500',
            'record_order' => 'required|in:ASC,DESC',
            'record_method' => 'required|string|max:50',
            'order_prefix' => 'nullable|string|max:50',
            'transaction_prefix' => 'nullable|string|max:50',
            'payment_emails' => 'nullable|string|max:1000',
            'api_token' => 'nullable|string|max:500',
            'api_end_point' => 'nullable|string|max:500',
            'bus_v2_design' => 'required|in:standard,desk',
            'admin_limit' => 'nullable|integer|min:0|max:999999',
            'manager_limit' => 'nullable|integer|min:0|max:999999',
            'comcod' => 'nullable|string|max:155',
            'tpvcod' => 'nullable|string|max:155',
            'authorization' => 'nullable|string|max:1000',
            'theme_primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_accent_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_login_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_header_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_header_text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_sidebar_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_sidebar_active_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_sidebar_text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_button_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_button_text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dashboard_background_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dashboard_card_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dashboard_text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dashboard_muted_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dashboard_border_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_surface_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_card_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_muted_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_border_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            'app_logo.uploaded' => 'The application logo could not be uploaded. Use an image smaller than 2 MB.',
            'app_logo.file' => 'The application logo could not be uploaded.',
            'app_logo.mimes' => 'The application logo must be a PNG, JPG, JPEG, GIF, BMP, or WebP image.',
            'app_logo.max' => 'The application logo must not be larger than 2 MB.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Please correct the highlighted settings.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()->with('message', $html)->with('message_type', 'warning');
        }

        $logo = $this->setting('APP_LOGO', 'logo_inverse.png');
        if ($request->hasFile('app_logo')) {
            $uploadedLogo = $request->file('app_logo');
            $extension = strtolower((string) ($uploadedLogo->guessExtension() ?: $uploadedLogo->getClientOriginalExtension()));
            $extension = $extension === 'jpeg' ? 'jpg' : $extension;
            $logo = 'logo_inverse.' . $extension;
            $uploadedLogo->move(public_path('images'), $logo);
        }

        $values = [
            'APP_NAME' => trim((string) $request->input('app_name')),
            'APP_LOGO' => $logo,
            'ENABLE_MULTI_LANG' => $this->checkboxEnabled($request, 'enable_multi_lang') ? '1' : '0',
            'DEFAULT_CURRENCY' => (string) $request->input('app_currency'),
            'DEFAULT_LANG' => (string) $request->input('app_lang'),
            'DEFAULT_TIMEZONE' => (string) $request->input('app_timezone'),
            'DASHBOARD_WELCOME_PREFIX' => $this->normalizeText($request->input('dashboard_welcome_prefix', '')),
            'DASHBOARD_WELCOME_SUBTITLE' => $this->normalizeText($request->input('dashboard_welcome_subtitle', '')),
            'DASHBOARD_FEATURE_SECURE' => $this->normalizeText($request->input('dashboard_feature_secure', '')),
            'DASHBOARD_FEATURE_INSTANT' => $this->normalizeText($request->input('dashboard_feature_instant', '')),
            'DASHBOARD_FEATURE_SUPPORT' => $this->normalizeText($request->input('dashboard_feature_support', '')),
            'PER_PAGE' => (string) $request->input('per_page'),
            'RECORD_ORDER_BY' => (string) $request->input('record_order'),
            'ENABLE_EMAIL' => $this->checkboxEnabled($request, 'enable_email') ? '1' : '0',
            'ENABLE_SLACK' => $this->checkboxEnabled($request, 'enable_slack') ? '1' : '0',
            'DEFAULT_RECORD_METHOD' => (string) $request->input('record_method'),
            'PAYMENT_EMAILS' => (string) $request->input('payment_emails', ''),
            'ORDER_PREFIX' => (string) $request->input('order_prefix', ''),
            'TRANSACTION_PREFIX' => (string) $request->input('transaction_prefix', ''),
            'ADMIN_LIMIT' => (string) $request->input('admin_limit', '0'),
            'MANAGER_LIMIT' => (string) $request->input('manager_limit', '0'),
            'API_TOKEN' => (string) $request->input('api_token', ''),
            'API_END_POINT' => (string) $request->input('api_end_point', ''),
            'BUS_V2_DESIGN' => (string) $request->input('bus_v2_design', 'standard'),
            'COMCOD' => (string) $request->input('comcod', ''),
            'TPVCOD' => (string) $request->input('tpvcod', ''),
            'AUTHORIZATION' => (string) $request->input('authorization', ''),
            'THEME_PRIMARY_COLOR' => $this->normalizeColor($request->input('theme_primary_color'), '#1764A8'),
            'THEME_ACCENT_COLOR' => $this->normalizeColor($request->input('theme_accent_color'), '#1DABF2'),
            'THEME_LOGIN_COLOR' => $this->normalizeColor($request->input('theme_login_color'), '#1764A8'),
            'THEME_HEADER_COLOR' => $this->normalizeColor($request->input('theme_header_color'), '#FFFFFF'),
            'THEME_HEADER_TEXT_COLOR' => $this->normalizeColor($request->input('theme_header_text_color'), '#1764A8'),
            'THEME_SIDEBAR_COLOR' => $this->normalizeColor($request->input('theme_sidebar_color'), '#FFFFFF'),
            'THEME_SIDEBAR_ACTIVE_COLOR' => $this->normalizeColor($request->input('theme_sidebar_active_color'), '#1764A8'),
            'THEME_SIDEBAR_TEXT_COLOR' => $this->normalizeColor($request->input('theme_sidebar_text_color'), '#1F2937'),
            'THEME_BUTTON_COLOR' => $this->normalizeColor($request->input('theme_button_color'), '#1764A8'),
            'THEME_BUTTON_TEXT_COLOR' => $this->normalizeColor($request->input('theme_button_text_color'), '#FFFFFF'),
            'THEME_DASHBOARD_BACKGROUND_COLOR' => $this->normalizeColor($request->input('theme_dashboard_background_color'), '#F4F8FC'),
            'THEME_DASHBOARD_CARD_COLOR' => $this->normalizeColor($request->input('theme_dashboard_card_color'), '#FFFFFF'),
            'THEME_DASHBOARD_TEXT_COLOR' => $this->normalizeColor($request->input('theme_dashboard_text_color'), '#1F2937'),
            'THEME_DASHBOARD_MUTED_COLOR' => $this->normalizeColor($request->input('theme_dashboard_muted_color'), '#6B7280'),
            'THEME_DASHBOARD_BORDER_COLOR' => $this->normalizeColor($request->input('theme_dashboard_border_color'), '#D8E3EE'),
            'THEME_DARK_SURFACE_COLOR' => $this->normalizeColor($request->input('theme_dark_surface_color'), '#161311'),
            'THEME_DARK_CARD_COLOR' => $this->normalizeColor($request->input('theme_dark_card_color'), '#221A16'),
            'THEME_DARK_TEXT_COLOR' => $this->normalizeColor($request->input('theme_dark_text_color'), '#F5F5F5'),
            'THEME_DARK_MUTED_COLOR' => $this->normalizeColor($request->input('theme_dark_muted_color'), '#A8A8A8'),
            'THEME_DARK_BORDER_COLOR' => $this->normalizeColor($request->input('theme_dark_border_color'), '#3A2A22'),
        ];

        $values = $this->repairLightTheme($values);

        file_put_contents(base_path('settings.php'), $this->settingsFileContent($values));

        $this->safeEmergencyLog($values['APP_NAME'] . ' Application Settings were updated');
        AppHelper::logger('success', 'Settings Update', 'Application settings were updated from V2');

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:cache');
        } catch (\Throwable $exception) {
            Log::warning('Application settings cache refresh failed: ' . $exception->getMessage());
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => trans('common.msg_update_success'),
                'data' => [
                    'settings' => $this->settingsPayload($values),
                    'logo_url' => asset('images/' . $values['APP_LOGO']) . '?v=' . time(),
                ],
            ]);
        }

        return redirect()->back()->with('message', trans('common.msg_update_success'))->with('message_type', 'success');
    }

    protected function denyUnlessRoot(Request $request)
    {
        if (auth()->check() && (int) auth()->user()->group_id === 1) {
            return null;
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        abort(403);
    }

    protected function settingsPayload(array $override = [])
    {
        $keys = [
            'APP_NAME' => 'DEMAT PRO',
            'APP_LOGO' => 'logo_inverse.png',
            'ENABLE_MULTI_LANG' => '0',
            'DEFAULT_CURRENCY' => 'EUR',
            'DEFAULT_LANG' => 'en',
            'DEFAULT_TIMEZONE' => 'UTC',
            'DASHBOARD_WELCOME_PREFIX' => '',
            'DASHBOARD_WELCOME_SUBTITLE' => '',
            'DASHBOARD_FEATURE_SECURE' => '',
            'DASHBOARD_FEATURE_INSTANT' => '',
            'DASHBOARD_FEATURE_SUPPORT' => '',
            'PER_PAGE' => '25',
            'RECORD_ORDER_BY' => 'DESC',
            'ENABLE_EMAIL' => '0',
            'ENABLE_SLACK' => '0',
            'DEFAULT_RECORD_METHOD' => '',
            'PAYMENT_EMAILS' => '',
            'ORDER_PREFIX' => '',
            'TRANSACTION_PREFIX' => '',
            'ADMIN_LIMIT' => '0',
            'MANAGER_LIMIT' => '0',
            'API_TOKEN' => '',
            'API_END_POINT' => '',
            'BUS_V2_DESIGN' => 'standard',
            'COMCOD' => '',
            'TPVCOD' => '',
            'AUTHORIZATION' => '',
            'THEME_PRIMARY_COLOR' => '#1764A8',
            'THEME_ACCENT_COLOR' => '#1DABF2',
            'THEME_LOGIN_COLOR' => '#1764A8',
            'THEME_HEADER_COLOR' => '#FFFFFF',
            'THEME_HEADER_TEXT_COLOR' => '#1764A8',
            'THEME_SIDEBAR_COLOR' => '#FFFFFF',
            'THEME_SIDEBAR_ACTIVE_COLOR' => '#1764A8',
            'THEME_SIDEBAR_TEXT_COLOR' => '#1F2937',
            'THEME_BUTTON_COLOR' => '#1764A8',
            'THEME_BUTTON_TEXT_COLOR' => '#FFFFFF',
            'THEME_DASHBOARD_BACKGROUND_COLOR' => '#F4F8FC',
            'THEME_DASHBOARD_CARD_COLOR' => '#FFFFFF',
            'THEME_DASHBOARD_TEXT_COLOR' => '#1F2937',
            'THEME_DASHBOARD_MUTED_COLOR' => '#6B7280',
            'THEME_DASHBOARD_BORDER_COLOR' => '#D8E3EE',
            'THEME_DARK_SURFACE_COLOR' => '#161311',
            'THEME_DARK_CARD_COLOR' => '#221A16',
            'THEME_DARK_TEXT_COLOR' => '#F5F5F5',
            'THEME_DARK_MUTED_COLOR' => '#A8A8A8',
            'THEME_DARK_BORDER_COLOR' => '#3A2A22',
        ];

        $settings = [];
        foreach ($keys as $key => $default) {
            $settings[$key] = array_key_exists($key, $override) ? $override[$key] : $this->setting($key, $default);
        }

        return $this->repairLightTheme($settings);
    }

    protected function setting($key, $default = '')
    {
        return defined($key) ? constant($key) : $default;
    }

    protected function normalizeColor($value, $default)
    {
        $value = strtoupper(trim((string) $value));

        return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : $default;
    }

    protected function normalizeText($value)
    {
        return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    }

    protected function repairLightTheme(array $settings)
    {
        $lightBg = strtoupper((string) $settings['THEME_DASHBOARD_BACKGROUND_COLOR']);
        $lightCard = strtoupper((string) $settings['THEME_DASHBOARD_CARD_COLOR']);
        $darkBg = strtoupper((string) $settings['THEME_DARK_SURFACE_COLOR']);
        $darkCard = strtoupper((string) $settings['THEME_DARK_CARD_COLOR']);

        $lightSurfaceLooksDark = $this->colorLooksDark($lightBg) && $this->colorLooksDark($lightCard);
        $lightSurfaceMatchesDark = $lightBg === $darkBg && $lightCard === $darkCard;

        if ($lightSurfaceLooksDark || $lightSurfaceMatchesDark) {
            if ($this->colorLooksDark($settings['THEME_HEADER_COLOR'])) {
                $settings['THEME_HEADER_COLOR'] = '#FFFFFF';
                $settings['THEME_HEADER_TEXT_COLOR'] = '#1F2937';
            }

            if ($this->colorLooksDark($settings['THEME_SIDEBAR_COLOR'])) {
                $settings['THEME_SIDEBAR_COLOR'] = '#FFFFFF';
                $settings['THEME_SIDEBAR_TEXT_COLOR'] = '#1F2937';
            }

            $settings['THEME_DASHBOARD_BACKGROUND_COLOR'] = '#F8F6F4';
            $settings['THEME_DASHBOARD_CARD_COLOR'] = '#FFFFFF';
            $settings['THEME_DASHBOARD_TEXT_COLOR'] = '#1F2937';
            $settings['THEME_DASHBOARD_MUTED_COLOR'] = '#6B7280';
            $settings['THEME_DASHBOARD_BORDER_COLOR'] = '#E7DED7';
        }

        return $settings;
    }

    protected function colorLooksDark($value)
    {
        $value = $this->normalizeColor($value, '#000000');
        $hex = ltrim($value, '#');
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luma = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luma < 110;
    }

    protected function checkboxEnabled(Request $request, $key)
    {
        $value = strtolower(trim((string) $request->input($key, '')));

        return in_array($value, ['1', 'true', 'on', 'yes'], true);
    }

    protected function safeEmergencyLog($message)
    {
        try {
            Log::emergency($message);
        } catch (\Throwable $exception) {
            try {
                Log::warning('Application settings emergency log skipped: ' . $exception->getMessage());
            } catch (\Throwable $ignored) {
                // Settings save must not fail because an external log transport is unavailable.
            }
        }
    }

    protected function settingsFileContent(array $values)
    {
        $content = "<?php \n";
        $content .= "/**\n";
        $content .= " * Created by TAMAEXPRESS\n";
        $content .= " * Developer: Syed Khalid T\n";
        $content .= " * Date: " . date('Y-m-d H:i:s') . "\n";
        $content .= " */\n";

        foreach ($values as $key => $value) {
            $content .= "define('" . $key . "'," . var_export((string) $value, true) . ");\n";
        }

        $content .= "?>";

        return $content;
    }
}
