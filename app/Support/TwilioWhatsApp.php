<?php

namespace App\Support;

class TwilioWhatsApp
{
    public static function sendText(string $to, string $body): array
    {
        $sid   = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from  = (string) config('services.twilio.whatsapp_from'); // e.g. whatsapp:+14155238886

        if (!$sid || !$token || !$from) {
            return ['ok' => false, 'status' => null, 'error' => 'Missing Twilio config'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $payload = http_build_query([
            'From' => $from,
            'To'   => $to,
            'Body' => $body,
        ], '', '&');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $sid . ':' . $token,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = $errno ? curl_error($ch) : null;
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: null;
        curl_close($ch);

        if ($errno) {
            return ['ok' => false, 'status' => $status, 'error' => "cURL error: {$error}"];
        }
        if ($status < 200 || $status >= 300) {
            return ['ok' => false, 'status' => $status, 'error' => $response ?: 'HTTP error'];
        }

        return ['ok' => true, 'status' => $status, 'response' => $response];
    }
    public static function sendTemplate(string $to, string $templateSid, array $variables = []): array
    {
        $sid   = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from  = (string) config('services.twilio.whatsapp_from');

        if (!$sid || !$token || !$from) {
            return ['ok' => false, 'status' => null, 'error' => 'Missing Twilio config'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $payload = http_build_query([
            'From'             => $from,
            'To'               => $to,
            'ContentSid'       => $templateSid,
            'ContentVariables' => json_encode($variables),
        ], '', '&');
       
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $sid . ':' . $token,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = $errno ? curl_error($ch) : null;
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: null;
        curl_close($ch);

        if ($errno) {
            return ['ok' => false, 'status' => $status, 'error' => "cURL error: {$error}"];
        }
        if ($status < 200 || $status >= 300) {
            return ['ok' => false, 'status' => $status, 'error' => $response ?: 'HTTP error'];
        }

        return ['ok' => true, 'status' => $status, 'response' => $response];
    }
}
