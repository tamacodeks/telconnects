<?php

namespace App\Support;

class RateLimiterCompat
{
    protected static function limiter()
    {
        // This service exists in both Laravel 5.8 and 10
        return app(\Illuminate\Cache\RateLimiter::class);
    }

    public static function tooManyAttempts($key, $maxAttempts)
    {
        return static::limiter()->tooManyAttempts($key, $maxAttempts);
    }

    public static function hit($key, $decaySeconds = 60)
    {
        return static::limiter()->hit($key, $decaySeconds);
    }

    public static function clear($key)
    {
        $limiter = static::limiter();

        // Newer Laravel
        if (method_exists($limiter, 'clear')) {
            return $limiter->clear($key);
        }
        // Laravel 5.8
        if (method_exists($limiter, 'resetAttempts')) {
            return $limiter->resetAttempts($key);
        }

        return null;
    }

    public static function availableIn($key)
    {
        $limiter = static::limiter();
        return method_exists($limiter, 'availableIn') ? (int) $limiter->availableIn($key) : 0;
    }
}
