<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($this->shouldRedirectExpiredGuest($request)) {
            return $this->unauthenticatedRedirect($request);
        }

        if (!$request->expectsJson()) {
            $statusCode = null;
            $headers = [];

            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
                $headers = $exception->getHeaders();
            } elseif ($exception instanceof TokenMismatchException) {
                $statusCode = 419;
            } elseif (!config('app.debug')) {
                $statusCode = 500;
            }

            if ($statusCode) {
                $view = $statusCode >= 500 ? 'errors.5xx' : 'errors.4xx';

                return response()->view($view, [
                    'status_code' => $statusCode,
                    'exception' => $exception,
                ], $statusCode, $headers);
            }
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->unauthenticatedRedirect($request);
    }

    protected function unauthenticatedRedirect($request)
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
                'redirect_url' => '/',
            ], 401);
        }

        return redirect()->away('/')
            ->with('message', 'Please login again.')
            ->with('message_type', 'warning');
    }

    protected function shouldRedirectExpiredGuest($request)
    {
        if ($this->requestHasAuthenticatedUser()) {
            return false;
        }

        return $this->routeRequiresAuthentication($request);
    }

    protected function requestHasAuthenticatedUser()
    {
        try {
            return auth()->check();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function routeRequiresAuthentication($request)
    {
        $route = $request->route();
        if (!$route || !method_exists($route, 'gatherMiddleware')) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            if ($middleware === 'auth' || strpos($middleware, 'auth:') === 0) {
                return true;
            }

            if ($middleware === \Illuminate\Auth\Middleware\Authenticate::class) {
                return true;
            }
        }

        return false;
    }
}
