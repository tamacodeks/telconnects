<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WaOtp
{
    /**
     * Send a WhatsApp OTP using Twilio Content Templates.
     *
     * @param  string            $to        whatsapp:+E164 or +E164
     * @param  string|int|null   $otp       Optional OTP (auto-generated if null)
     * @param  string            $lang
     * @param  array             $overrides
     * @return array
     */
    public static function send($to, $otp = null, $lang = 'fr', array $overrides = [])
    {
        // -------------------------------------------------
        // 1) Normalize language
        // -------------------------------------------------
        $lang = strtolower(trim((string) $lang));
        if (!in_array($lang, ['fr', 'en', 'de'], true)) {
            $lang = 'fr';
        }

        // -------------------------------------------------
        // 2) Twilio credentials
        // -------------------------------------------------
        $cfg   = (array) config('services.twilio');
        $sid   = $cfg['sid']   ?? env('TWILIO_ACCOUNT_SID');
        $token = $cfg['token'] ?? env('TWILIO_AUTH_TOKEN');

        if (!$sid || !$token) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Twilio credentials missing',
            ];
        }

        // -------------------------------------------------
        // 3) Sender (Messaging Service preferred)
        // -------------------------------------------------
        $msid = $overrides['msid']
            ?? $cfg['wa_messaging_service']
            ?? env('TWILIO_MESSAGING_SERVICE_SID_WA');

        $from = $overrides['from']
            ?? $cfg['wa_from']
            ?? env('TWILIO_WHATSAPP_FROM');

        // -------------------------------------------------
        // 4) Content SID (fallback to French)
        // -------------------------------------------------
        $contentSid = $overrides['contentSid']
            ?? ($cfg['otp_content'][$lang] ?? null)
            ?? ($cfg['otp_content']['fr'] ?? env('TWILIO_OTP_CONTENT_SID_FR'));

        if (!$contentSid) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'WhatsApp OTP Content SID not configured',
            ];
        }

        // -------------------------------------------------
        // 5) Throttling (Laravel 5.6 safe)
        // -------------------------------------------------
        if (!empty($overrides['throttle'])) {
            $max     = (int) ($overrides['throttle']['max'] ?? 3);
            $minutes = (int) ($overrides['throttle']['minutes'] ?? 10);

            $key  = 'waotp:' . md5(self::asWa($to));
            $hits = (int) Cache::get($key, 0);

            if ($hits >= $max) {
                return [
                    'ok' => false,
                    'status' => 429,
                    'error' => 'Too many OTP requests. Try later.',
                ];
            }

            Cache::put($key, $hits + 1, $minutes * 60);
        }

        // -------------------------------------------------
        // 6) Normalize WhatsApp numbers
        // -------------------------------------------------
        $toWa = self::asWa($to);

        if (!preg_match('/^whatsapp:\+\d{8,15}$/', $toWa)) {
            return [
                'ok' => false,
                'status' => 422,
                'error' => 'Invalid WhatsApp number',
            ];
        }

        $fromWa = $from ? self::asWa($from) : null;

        // -------------------------------------------------
        // 7) OTP generation
        // -------------------------------------------------
        if ($otp === null || $otp === '') {
            $otp = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        $code = (string) $otp;
        if (!preg_match('/^\d{6}$/', $code)) {
            return [
                'ok' => false,
                'status' => 400,
                'error' => 'Invalid OTP format',
            ];
        }

        $varKey = $overrides['varKey'] ?? 'code';

        // -------------------------------------------------
        // 8) Build Twilio payload
        // -------------------------------------------------
        $payload = [
            'To'               => $toWa,
            'ContentSid'       => $contentSid,
            'ContentVariables' => self::makeVars($code, $varKey),
            'ContentLanguage'  => $lang,
        ];

        $preferMessagingService = !empty($overrides['prefer_messaging_service']);

        if ($fromWa && !$preferMessagingService) {
            $payload['From'] = $fromWa;
        } elseif ($msid) {
            $payload['MessagingServiceSid'] = $msid;
        } elseif ($fromWa) {
            $payload['From'] = $fromWa;
        } else {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'No WhatsApp sender configured',
            ];
        }

        Log::debug('WA OTP sending', [
            'to' => $payload['To'],
            'contentSid' => $payload['ContentSid'],
            'varKey' => $varKey,
        ]);

        // -------------------------------------------------
        // 9) Send
        // -------------------------------------------------
        [$http, $err, $json] = self::httpPost($sid, $token, $payload);

        // Retry language issues (Twilio 63027)
        if ($http === 400 && ($json['code'] ?? 0) == 63027) {
            $payload['ContentLanguage'] = self::twilioLanguage($lang);
            [$http, $err, $json] = self::httpPost($sid, $token, $payload);
        }

        // Retry variable mismatch (21656)
        if ($http === 400 && ($json['code'] ?? 0) == 21656 && !isset($overrides['varKey'])) {
            foreach (['code', 'otp', '1'] as $alt) {
                $payload['ContentVariables'] = self::makeVars($code, $alt);
                [$http, $err, $json] = self::httpPost($sid, $token, $payload);
                if ($http >= 200 && $http < 300) break;
            }
        }

        // -------------------------------------------------
        // 10) Result
        // -------------------------------------------------
        if ($http >= 200 && $http < 300) {
            return [
                'ok'   => true,
                'sid'  => $json['sid'] ?? null,
                'to'   => $toWa,
                'lang' => $lang,
                'otp'  => app()->isLocal() ? $code : null,
                'used' => isset($payload['MessagingServiceSid']) ? 'messaging_service' : 'from',
            ];
        }

        Log::warning('WA OTP failed', [
            'http' => $http,
            'error' => $err,
            'twilio' => $json,
        ]);

        return [
            'ok'     => false,
            'status' => $http,
            'error'  => $err ?: ($json['message'] ?? 'Unknown error'),
            'twilio' => $json,
        ];
    }

    // -------------------------------------------------
    // Helpers
    // -------------------------------------------------

    protected static function makeVars($code, $key)
    {
        return json_encode([(string) $key => (string) $code], JSON_UNESCAPED_SLASHES);
    }

    protected static function twilioLanguage($lang)
    {
        $map = [
            'fr' => 'fr_FR',
            'en' => 'en',
            'de' => 'de',
        ];

        return $map[$lang] ?? 'fr_FR';
    }

    protected static function asE164($n)
    {
        $digits = preg_replace('/\D+/', '', (string) $n);
        return '+' . $digits;
    }

    protected static function asWa($n)
    {
        if (Str::startsWith($n, 'whatsapp:')) {
            return $n;
        }
        return 'whatsapp:' . self::asE164($n);
    }

    protected static function httpPost($sid, $token, array $payload)
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $caBundle = self::caBundlePath();
        $options = [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_USERPWD        => "{$sid}:{$token}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($caBundle !== null) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        list($http, $err, $json) = self::executeCurl($url, $options);

        if ($http === 0 && self::shouldRetryWithoutSslVerification($err)) {
            Log::warning('WA OTP retrying without SSL verification in local environment', [
                'error' => $err,
                'ca_bundle' => $caBundle,
            ]);

            unset($options[CURLOPT_CAINFO]);
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;

            list($http, $err, $json) = self::executeCurl($url, $options);
        }

        return [$http, $err, $json];
    }

    public static function fetchMessage($messageSid)
    {
        $messageSid = trim((string) $messageSid);
        if ($messageSid === '') {
            return [
                'ok' => false,
                'http_status' => 0,
                'error' => 'Message SID missing',
            ];
        }

        $cfg   = (array) config('services.twilio');
        $sid   = $cfg['sid']   ?? env('TWILIO_ACCOUNT_SID');
        $token = $cfg['token'] ?? env('TWILIO_AUTH_TOKEN');

        if (!$sid || !$token) {
            return [
                'ok' => false,
                'http_status' => 500,
                'error' => 'Twilio credentials missing',
            ];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages/{$messageSid}.json";
        $caBundle = self::caBundlePath();
        $options = [
            CURLOPT_USERPWD        => "{$sid}:{$token}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($caBundle !== null) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        list($http, $err, $json) = self::executeCurl($url, $options);

        if ($http === 0 && self::shouldRetryWithoutSslVerification($err)) {
            Log::warning('WA OTP status retrying without SSL verification in local environment', [
                'error' => $err,
                'ca_bundle' => $caBundle,
                'sid' => $messageSid,
            ]);

            unset($options[CURLOPT_CAINFO]);
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;

            list($http, $err, $json) = self::executeCurl($url, $options);
        }

        if ($http >= 200 && $http < 300) {
            return [
                'ok' => true,
                'http_status' => $http,
                'sid' => $json['sid'] ?? $messageSid,
                'message_status' => $json['status'] ?? null,
                'error_code' => $json['error_code'] ?? null,
                'error_message' => $json['error_message'] ?? null,
                'twilio' => $json,
            ];
        }

        return [
            'ok' => false,
            'http_status' => $http,
            'error' => $err ?: ($json['message'] ?? 'Unknown error'),
            'twilio' => $json,
        ];
    }

    protected static function caBundlePath()
    {
        $candidates = array_filter([
            trim((string) ini_get('curl.cainfo')),
            trim((string) ini_get('openssl.cafile')),
            base_path('storage/certs/cacert.pem'),
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected static function shouldRetryWithoutSslVerification($error)
    {
        if (!app()->environment('local')) {
            return false;
        }

        $error = strtolower(trim((string) $error));
        if ($error === '') {
            return false;
        }

        foreach ([
            'ssl certificate problem',
            'unable to get local issuer certificate',
            'certificate verify failed',
        ] as $needle) {
            if (strpos($error, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    protected static function executeCurl($url, array $options)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $json = json_decode($raw ?: '{}', true);

        return [$http, $err, $json];
    }
}
