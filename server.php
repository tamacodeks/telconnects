<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$publicPath = __DIR__.'/public'.$uri;

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && is_file($publicPath)) {
    $extension = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'map' => 'application/json',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'ttf' => 'font/ttf',
        'txt' => 'text/plain',
        'wav' => 'audio/wav',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    $mimeType = function_exists('mime_content_type') ? mime_content_type($publicPath) : false;

    if (! $mimeType || $mimeType === 'text/plain') {
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    header('Content-Type: '.$mimeType);
    header('Content-Length: '.filesize($publicPath));
    readfile($publicPath);

    return true;
}

require_once __DIR__.'/public/index.php';
