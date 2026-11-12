<?php

namespace App\Services;

class QueuedTopupResponse
{
    public function classify($provider, $httpStatus, array $response = null)
    {
        if (!$response || $httpStatus >= 500 || in_array($httpStatus, [408, 409, 429], true)) {
            return 'unknown';
        }

        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
        // This backend emits its 400/403 preflight rejections as an error envelope,
        // sometimes with HTTP 200. These paths return before calling TransferTo.
        if ($provider === 'transfer' && in_array($httpStatus, [200, 400, 403], true) && !$data
            && isset($response['error']) && is_array($response['error'])
            && in_array((string) ($response['error']['code'] ?? ''), ['400', '403'], true)
            && !empty($response['error']['message']) && is_string($response['error']['message'])) {
            return 'rejected';
        }

        if (($response['success'] ?? null) === false || ($data['success'] ?? null) === false) {
            return 'unknown';
        }
        $transaction = $data['transaction_id'] ?? ($data['transactionId'] ?? null);
        $order = $data['order_id'] ?? ($data['orderId'] ?? null);
        $reference = $data['txn_ref'] ?? ($data['external_id'] ?? null);

        // ConfirmTopup rolls back its internal work and returns this sentinel
        // when the provider confirmation fails or an internal exception occurs.
        // The queue has already debited and created its local order, so this is
        // a definite local rejection and must be refunded.
        if ($provider === 'transfer' && (string) $transaction === '000'
            && ($order === '' || $order === null)) {
            return 'rejected';
        }

        if ($httpStatus === 200 && is_scalar($transaction) && is_numeric($transaction)
            && (float) $transaction > 0 && is_scalar($order) && is_numeric($order)
            && (float) $order > 0 && is_scalar($reference) && trim((string) $reference) !== '') {
            return 'succeeded';
        }

        $serialized = json_encode($response);
        if (preg_match('/timeout|timed out|pending|in progress|unknown|duplicate|Tellus API Error \[4003\]/i', $serialized)) {
            return 'unknown';
        }

        if (in_array($httpStatus, [401, 404, 405, 422], true)) {
            return 'rejected';
        }

        if ($provider === 'tellus' && $httpStatus === 400
            && preg_match('/Tellus API Error \[(\d+)\]/', $serialized, $matches)
            && !in_array($matches[1], ['0000', '4003'], true)) {
            return 'rejected';
        }

        // Upstream also returns transaction_id=000 after internal exceptions.
        // It does not prove rejection; never refund or re-send that result.
        return 'unknown';
    }
}
