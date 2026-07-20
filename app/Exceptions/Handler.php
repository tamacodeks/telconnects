<?php

namespace App\Exceptions;

use Exception;
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
}
