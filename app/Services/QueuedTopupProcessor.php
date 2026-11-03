<?php

namespace App\Services;

use App\Models\QueuedTopup;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QueuedTopupProcessor
{
    private $client;
    private $endpoints = [
        'ding' => 'topup/confirm/ding',
        'reloadly' => 'topup/confirm/reloadly',
        'transfer' => 'topup/confirm/transfer',
        'tellus' => 'topup/confirm/tellus',
    ];

    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client;
    }

    public function process($id)
    {
        $queued = DB::transaction(function () use ($id) {
            $row = QueuedTopup::whereKey($id)->lockForUpdate()->first();
            if (!$row || $row->status !== 'pending') {
                return null;
            }

            $order = DB::table('orders')->where('id', $row->order_id)->first();
            if (!$order || (int) $order->order_status_id !== 7 || (int) $order->service_id !== 2
                || (int) $order->user_id !== (int) $row->user_id || !$order->order_item_id) {
                $this->markUnknown($row->id, 'Local order is missing, refunded, or inconsistent. Operator request withheld.');
                return null;
            }

            $row->status = 'processing';
            $row->attempts = (int) $row->attempts + 1;
            $row->save();
            return $row;
        });

        if (!$queued) {
            return;
        }

        try {
            $response = $this->send($queued);
            $raw = (string) $response->getBody();
            $decoded = json_decode($raw, true);
            $classification = (new QueuedTopupResponse)->classify(
                $queued->provider, (int) $response->getStatusCode(), is_array($decoded) ? $decoded : null
            );

            // Preserve the response before finalizing local orders or refunds.
            $queued->provider_response = [
                'http_status' => (int) $response->getStatusCode(),
                'body' => is_array($decoded) ? $decoded : ['raw' => mb_substr($raw, 0, 10000)],
                'classification' => $classification,
            ];
            $queued->save();

            if ($classification === 'succeeded') {
                $this->complete($queued, $decoded);
            } elseif ($classification === 'rejected') {
                (new TopupRefundService)->queued($queued->id, 'Operator request rejected.', $queued->provider_response);
            } else {
                $this->markUnknown($queued->id, 'Provider result needs reconciliation. HTTP ' . $response->getStatusCode());
            }
        } catch (\Throwable $exception) {
            $this->markUnknown($queued->id, $exception->getMessage());
        }
    }

    private function send(QueuedTopup $queued)
    {
        if (!isset($this->endpoints[$queued->provider])) {
            throw new \RuntimeException('Unsupported queued top-up provider.');
        }

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . API_TOKEN,
            'X-Tamashop-Request-ID' => $queued->request_id,
        ];
        if ($queued->client_ip) {
            $headers['X-Tamashop-Client-IP'] = $queued->client_ip;
        }
        if ($queued->user_agent) {
            $headers['X-Tamashop-Client-User-Agent'] = $queued->user_agent;
        }

        $client = $this->client ?: new Client(['base_uri' => API_END_POINT]);
        return $client->request('POST', $this->endpoints[$queued->provider], [
            'headers' => $headers,
            'form_params' => (array) $queued->payload,
            'timeout' => 120,
            'connect_timeout' => 10,
            'http_errors' => false,
            'allow_redirects' => false,
        ]);
    }

    public function reconcileStoredResponse($id)
    {
        $row = QueuedTopup::whereKey($id)->first();
        if (!$row || $row->status !== 'unknown' || !is_array($row->provider_response)) {
            return false;
        }

        $saved = $row->provider_response;
        if (!isset($saved['body']) || !is_array($saved['body'])) {
            return false;
        }

        $classification = (new QueuedTopupResponse)->classify($row->provider, (int) ($saved['http_status'] ?? 0), $saved['body']);
        if ($classification === 'succeeded') {
            $this->complete($row, $saved['body']);
            return true;
        }
        if ($classification === 'rejected') {
            return (new TopupRefundService)->queued($row->id, 'Operator rejection recovered from saved response.', $saved);
        }

        return false;
    }

    private function complete(QueuedTopup $queued, array $response)
    {
        DB::transaction(function () use ($queued, $response) {
            $row = QueuedTopup::whereKey($queued->id)->lockForUpdate()->first();
            if (!$row || $row->status === 'succeeded' || $row->refunded_at) {
                return;
            }

            $primary = DB::table('orders')->where('id', $row->order_id)->lockForUpdate()->first();
            if (!$primary || (int) $primary->order_status_id !== 7) {
                throw new \RuntimeException('Provider succeeded but local order is no longer successful.');
            }

            $data = $response['data'];
            $reference = $data['txn_ref'] ?? $data['external_id'];
            DB::table('orders')->where('service_id', 2)
                ->where('order_item_id', $primary->order_item_id)->update([
                    'txn_ref' => $reference,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $item = ['updated_at' => date('Y-m-d H:i:s')];
            if (!empty($data['transRef']) || !empty($data['transferRef'])) {
                $item['transfer_ref'] = $data['transRef'] ?? $data['transferRef'];
            }
            if (isset($data['local_amount'])) {
                $item['tt_dest_amount'] = $data['local_amount'];
            }
            if (!empty($data['dest_currency'])) {
                $item['tt_dest_currency'] = $data['dest_currency'];
            }
            DB::table('order_items')->where('id', $primary->order_item_id)->update($item);

            if ($row->track_order_id) {
                DB::table('track_orders')->where('id', $row->track_order_id)->update([
                    'api_order_id' => $data['order_id'] ?? $data['orderId'],
                    'api_trans_id' => $data['transaction_id'] ?? $data['transactionId'],
                    'status' => 7,
                    'remarks' => 'Topup processed successfully by queue.',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $row->user_id,
                ]);
            }

            $row->status = 'succeeded';
            $row->provider_reference = $reference;
            $row->processed_at = date('Y-m-d H:i:s');
            $row->error_message = null;
            $row->save();
            $this->updateConfirmAttempt($row->request_id, [
                'status' => 'SUCCESS',
                'provider_reference' => $reference,
                'order_id' => $row->order_id,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        });
    }

    public function markUnknown($id, $message)
    {
        $row = QueuedTopup::whereKey($id)->first();
        if (!$row || !in_array($row->status, ['pending', 'processing'], true)) {
            return;
        }

        $updated = QueuedTopup::whereKey($id)->whereIn('status', ['pending', 'processing'])->update([
            'status' => 'unknown',
            'error_message' => mb_substr((string) $message, 0, 2000),
            'failed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$updated) {
            return;
        }

        $this->updateConfirmAttempt($row->request_id, [
            'status' => 'UNKNOWN',
            'error_message' => mb_substr((string) $message, 0, 1000),
        ]);
        Log::warning('Queued top-up requires reconciliation; no automatic resend or refund.', [
            'queued_topup_id' => $row->id,
            'request_id' => $row->request_id,
            'provider' => $row->provider,
            'order_id' => $row->order_id,
        ]);
    }

    private function updateConfirmAttempt($requestId, array $values)
    {
        if (Schema::hasTable('topup_confirm_attempts')) {
            DB::table('topup_confirm_attempts')->where('request_id', $requestId)->update(array_merge($values, [
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }
    }
}
