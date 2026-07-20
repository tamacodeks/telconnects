<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        'wa_from' => env('TWILIO_WHATSAPP_FROM'),
        'wa_messaging_service' => env('TWILIO_MESSAGING_SERVICE_SID_WA'),
        'otp_content' => [
            'fr' => env('TWILIO_OTP_CONTENT_SID_FR'),
            'en' => env('TWILIO_OTP_CONTENT_SID_EN'),
            'de' => env('TWILIO_OTP_CONTENT_SID_DE'),
        ],
    ],

    'whatsapp_otp' => [
        'endpoint' => env('WHATSAPP_OTP_ENDPOINT'),
        'token' => env('WHATSAPP_OTP_TOKEN'),
        'from' => env('WHATSAPP_OTP_FROM', env('APP_NAME', 'TamaExpress')),
    ],

];
