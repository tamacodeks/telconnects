@php
    $errorCode = isset($status_code) ? (string) $status_code : null;

    if (!$errorCode && isset($statusCode)) {
        $errorCode = (string) $statusCode;
    }

    if (!$errorCode && isset($exception) && method_exists($exception, 'getStatusCode')) {
        $errorCode = (string) $exception->getStatusCode();
    }

    $errorCode = $errorCode ?: '404';
@endphp

@include('errors.partials.v2')
