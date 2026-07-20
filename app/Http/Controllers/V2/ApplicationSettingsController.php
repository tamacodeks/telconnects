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
            'app_logo' => 'nullable|image|max:2048',
            'app_currency' => 'required|string|max:10',
            'app_lang' => 'required|string|max:10',
            'app_timezone' => 'required|string|max:120',
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
            $logo = 'logo_inverse.png';
            $request->file('app_logo')->move(public_path('images'), $logo);
        }

        $values = [
            'APP_NAME' => trim((string) $request->input('app_name')),
            'APP_LOGO' => $logo,
            'ENABLE_MULTI_LANG' => $request->boolean('enable_multi_lang') ? '1' : '0',
            'DEFAULT_CURRENCY' => (string) $request->input('app_currency'),
            'DEFAULT_LANG' => (string) $request->input('app_lang'),
            'DEFAULT_TIMEZONE' => (string) $request->input('app_timezone'),
            'PER_PAGE' => (string) $request->input('per_page'),
            'RECORD_ORDER_BY' => (string) $request->input('record_order'),
            'ENABLE_EMAIL' => $request->boolean('enable_email') ? '1' : '0',
            'ENABLE_SLACK' => $request->boolean('enable_slack') ? '1' : '0',
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
        ];

        file_put_contents(base_path('settings.php'), $this->settingsFileContent($values));

        Log::emergency($values['APP_NAME'] . ' Application Settings were updated');
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
                    'logo_url' => asset('images/' . $values['APP_LOGO']),
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
        ];

        $settings = [];
        foreach ($keys as $key => $default) {
            $settings[$key] = array_key_exists($key, $override) ? $override[$key] : $this->setting($key, $default);
        }

        return $settings;
    }

    protected function setting($key, $default = '')
    {
        return defined($key) ? constant($key) : $default;
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
