<?php

namespace App\Support;

class ClientInfo
{
    /** Get device/browser/OS info from User-Agent (lightweight parser) */
    public static function parseUserAgent(?string $ua): array
    {
        $ua = $ua ?? '';

        // Device type
        $device = 'Desktop';
        if (stripos($ua, 'mobile') !== false || stripos($ua, 'iphone') !== false || stripos($ua, 'android') !== false) {
            $device = 'Mobile';
        }
        if (stripos($ua, 'tablet') !== false || stripos($ua, 'ipad') !== false) {
            $device = 'Tablet';
        }
        if (stripos($ua, 'bot') !== false || stripos($ua, 'crawl') !== false || stripos($ua, 'spider') !== false) {
            $device = 'Bot';
        }

        // OS
        $os = 'Unknown';
        $mapOs = [
            '/windows nt 10\.0/i' => 'Windows 10/11',
            '/windows nt 6\.3/i'  => 'Windows 8.1',
            '/windows nt 6\.2/i'  => 'Windows 8',
            '/windows nt 6\.1/i'  => 'Windows 7',
            '/mac os x/i'         => 'macOS',
            '/android/i'          => 'Android',
            '/iphone|ipad|ipod/i' => 'iOS',
            '/linux/i'            => 'Linux',
        ];
        foreach ($mapOs as $regex => $name) {
            if (preg_match($regex, $ua)) { $os = $name; break; }
        }

        // Browser (very rough)
        $browser = 'Unknown';
        $mapBrowser = [
            '/edg\//i'        => 'Edge',
            '/chrome\//i'     => 'Chrome',
            '/safari\//i'     => 'Safari',   // appears after Chrome too; Edge case handled below
            '/firefox\//i'    => 'Firefox',
            '/msie|trident/i' => 'IE',
        ];
        foreach ($mapBrowser as $regex => $name) {
            if (preg_match($regex, $ua)) { $browser = $name; break; }
        }
        // If both Chrome and Safari matched, prefer Chrome
        if ($browser === 'Safari' && stripos($ua, 'chrome/') !== false) {
            $browser = 'Chrome';
        }

        return compact('device', 'os', 'browser');
    }

    /** Get GeoIP info via ip-api.com (no key). Returns country/city/region/isp. */
    public static function geoByIp(string $ip): array
    {
        // private/local IPs → skip lookup
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return ['country'=>'Local', 'region'=>'', 'city'=>'', 'isp'=>''];
        }

        $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,country,regionName,city,isp,query';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return ['country'=>'', 'region'=>'', 'city'=>'', 'isp'=>''];

        $j = json_decode($res, true) ?: [];
        if (($j['status'] ?? '') !== 'success') {
            return ['country'=>'', 'region'=>'', 'city'=>'', 'isp'=>''];
        }

        return [
            'country' => (string) ($j['country'] ?? ''),
            'region'  => (string) ($j['regionName'] ?? ''),
            'city'    => (string) ($j['city'] ?? ''),
            'isp'     => (string) ($j['isp'] ?? ''),
        ];
    }

    /** Extract client IP considering proxies/CDN (best effort). */
    public static function clientIp(): string
    {
        $server = $_SERVER;

        // Cloudflare
        if (!empty($server['HTTP_CF_CONNECTING_IP'])) {
            return $server['HTTP_CF_CONNECTING_IP'];
        }
        // Standard X-Forwarded-For
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $parts = array_map('trim', explode(',', $server['HTTP_X_FORWARDED_FOR']));
            foreach ($parts as $p) {
                if (filter_var($p, FILTER_VALIDATE_IP)) return $p;
            }
        }
        // Fallback
        return request()->ip() ?? ($server['REMOTE_ADDR'] ?? 'unknown');
    }
}
