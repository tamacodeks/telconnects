<?php

namespace App\Services;

use App\Models\QueuedTopup;
use GuzzleHttp\Client;

class TopupCallbackService
{
    public function sync($provider, $queuedOnly = false)
    {
        if (!in_array($provider, ['reloadly', 'transfer', 'tellus'], true)) {
            throw new \InvalidArgumentException('Unsupported top-up callback provider.');
        }

        $client = new Client(['base_uri' => API_END_POINT, 'timeout' => 10, 'connect_timeout' => 3]);
        $response = $client->request('GET', 'topup/callback/' . $provider, [
            'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . API_TOKEN],
            'allow_redirects' => false,
        ]);
        $decoded = json_decode((string) $response->getBody(), true);
        if ((int) $response->getStatusCode() !== 200 || !isset($decoded['failed']) || !is_array($decoded['failed'])) {
            throw new \RuntimeException('Callback API returned an invalid result for ' . $provider);
        }

        $handled = 0;
        foreach ($decoded['failed'] as $failure) {
            if (!is_array($failure) || empty($failure['external_id']) || !is_scalar($failure['external_id'])) {
                continue;
            }

            if ($queuedOnly && !QueuedTopup::where('provider', $provider)
                ->where('provider_reference', $failure['external_id'])->whereNull('refunded_at')->exists()) {
                continue;
            }

            if ((new TopupRefundService)->callback($provider, $failure['external_id'], $failure)) {
                $handled++;
            }
        }

        return $handled;
    }
}
