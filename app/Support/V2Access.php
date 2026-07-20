<?php

namespace App\Support;

use Illuminate\Http\Request;

class V2Access
{
    const ALLOWED_USER_IDS = [7,138];

    public static function allowedUserIds()
    {
        return self::ALLOWED_USER_IDS;
    }

    public static function userCanUseV2($user = null)
    {
        if (!$user && function_exists('auth')) {
            $user = auth()->user();
        }

        if (!$user || !isset($user->id)) {
            return false;
        }

        return in_array((int) $user->id, self::allowedUserIds(), true);
    }

    public static function sidebarPathFor($path, $user = null)
    {
        $path = self::normalizePath($path);

        if ($path === '') {
            return '';
        }

        return self::userCanUseV2($user)
            ? self::v2PathFor($path)
            : self::legacyPathFor($path);
    }

    public static function v2PathFor($path)
    {
        $path = self::normalizePath($path);
        $map = [
            'dashboard' => 'dashboard-v2',
            'dashboard-v2' => 'dashboard-v2',
            'profile' => 'profile-v2',
            'profile-V2' => 'profile-v2',
            'profile-v2' => 'profile-v2',
            'payments' => 'payments-v2',
            'payments-v2' => 'payments-v2',
            'bus' => 'bus',
            'flix-bus' => 'bus',
            'bus-v2' => 'bus',
            'tama-topup' => 'tama-topup-v2',
            'tama-topup-v2' => 'tama-topup-v2',
            'calling-cards' => 'calling-cards-v2',
            'calling-cards-v2' => 'calling-cards-v2',
            'cc-price-lists' => 'cc-price-lists-v2',
            'cc-price-lists-v2' => 'cc-price-lists-v2',
            'my/cc-price-lists' => 'my/cc-price-lists-v2',
            'my/cc-price-lists-v2' => 'my/cc-price-lists-v2',
            'cc-pin-history' => 'cc-pin-history-v2',
            'cc-pin-history-v2' => 'cc-pin-history-v2',
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

        return isset($map[$path]) ? $map[$path] : $path;
    }

    public static function legacyPathFor($path)
    {
        $path = self::normalizePath($path);
        $exact = [
            'dashboard-v2' => 'dashboard',
            'dashboard' => 'dashboard',
            'profile-v2' => 'profile',
            'profile-V2' => 'profile',
            'profile' => 'profile',
            'payments-v2' => 'payments',
            'payments-v2/fetch' => 'fetch/payments',
            'payments' => 'payments',
            'orders-v2' => 'orders',
            'orders-v2/fetch' => 'fetch/orders',
            'transactions-v2' => 'transactions',
            'transactions-v2/fetch' => 'fetch/transactions',
            'failed-transactions-v2' => 'failed_transaction',
            'failed-transactions-v2/fetch' => 'fetch/failed_transaction',
            'failed_transaction' => 'failed_transaction',
            'bus-v2' => 'flix-bus',
            'bus' => 'flix-bus',
            'tama-topup' => 'tama-topup-v1',
            'tama-topup-v2' => 'tama-topup-v1',
            'calling-cards-v2' => 'calling-cards',
            'cc-price-lists-v2' => 'cc-price-lists',
            'cc-price-lists-v2/fetch' => 'cc-price-lists/fetch',
            'cc-price-lists-v2/update' => 'cc-price-lists/update',
            'my/cc-price-lists-v2' => 'my/cc-price-lists',
            'my/cc-price-lists-v2/fetch' => 'my/cc-price-lists',
            'cc-pin-history-v2' => 'cc-pin-history',
            'cc-pin-history-v2/fetch' => 'cc-pin-history/fetch',
            'menus-v2' => 'menus',
            'app-settings-v2' => 'app-settings',
            'app-settings-v2/save' => 'app-settings/save',
        ];

        if (isset($exact[$path])) {
            return $exact[$path];
        }

        foreach (self::legacyPrefixMap() as $from => $to) {
            if (strpos($path, $from) === 0) {
                return $to . substr($path, strlen($from));
            }
        }

        return $path;
    }

    public static function legacyUrlForRequest(Request $request)
    {
        $path = self::legacyPathFor($request->path());
        $query = $request->getQueryString();
        $url = url($path);

        return $query ? $url . '?' . $query : $url;
    }

    public static function normalizePath($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            return trim($path, '/');
        }

        return trim($value, '/');
    }

    private static function legacyPrefixMap()
    {
        return [
            'orders-v2/' => 'orders/',
            'payments-v2/' => 'payments/',
            'transactions-v2/' => 'transactions/',
            'failed-transactions-v2/' => 'failed_transaction/',
            'bus-v2/' => 'flix-bus/',
            'bus/' => 'flix-bus/',
            'tama-topup-v2/' => 'tama-topup/',
            'calling-cards-v2/' => 'calling-cards/',
            'cc-price-lists-v2/' => 'cc-price-lists/',
            'my/cc-price-lists-v2/' => 'my/cc-price-lists/',
            'cc-pin-history-v2/' => 'cc-pin-history/',
            'menus-v2/' => 'menus/',
            'app-settings-v2/' => 'app-settings/',
            'profile-v2/' => 'profile/',
        ];
    }
}
