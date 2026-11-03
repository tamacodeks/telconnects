<?php

namespace App\Services;

use App\Models\QueuedTopup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TopupRefundService
{
    public function callback($provider, $reference, array $response)
    {
        if (!is_scalar($reference) || trim((string) $reference) === '') {
            return false;
        }

        return DB::transaction(function () use ($provider, $reference, $response) {
            $queued = null;
            if (Schema::hasTable('queued_topups')) {
                $queued = QueuedTopup::where('provider', $provider)
                    ->where('provider_reference', $reference)->lockForUpdate()->first();
            }

            if ($queued && $queued->refunded_at) {
                return true;
            }

            $orders = DB::table('orders')->where('service_id', 2)
                ->where('txn_ref', $reference)->orderBy('id')->lockForUpdate()->get();
            if ($orders->isEmpty()) {
                return false;
            }

            $this->refundOrders($orders, $reference);
            if ($queued) {
                $this->finish($queued, 'Provider failure confirmed by callback.', $response);
            }

            return true;
        });
    }

    public function queued($id, $message, array $response)
    {
        return DB::transaction(function () use ($id, $message, $response) {
            $row = QueuedTopup::whereKey($id)->lockForUpdate()->first();
            if (!$row || $row->refunded_at || $row->status === 'succeeded') {
                return false;
            }

            $primary = DB::table('orders')->where('id', $row->order_id)->first();
            if (!$primary || (int) $primary->service_id !== 2 || (int) $primary->user_id !== (int) $row->user_id) {
                throw new \RuntimeException('Queued top-up order could not be verified for refund.');
            }

            $orders = DB::table('orders')->where('service_id', 2)
                ->where('order_item_id', $primary->order_item_id)
                ->orderBy('id')->lockForUpdate()->get();
            $this->refundOrders($orders, $primary->txn_ref);
            $this->finish($row, $message, $response);

            return true;
        });
    }

    private function refundOrders($orders, $reference)
    {
        // Lock order rows until both the refund and status change have committed.
        // Concurrent worker/callback runs then observe status 9 and do nothing.
        foreach ($orders as $order) {
            if ((int) $order->order_status_id !== 7) {
                continue;
            }

            if ((int) $order->is_parent_order === 1) {
                DB::table('payments')->insert([
                    'user_id' => $order->user_id,
                    'transaction_id' => null,
                    'date' => date('Y-m-d H:i:s'),
                    'amount' => $order->order_amount,
                    'description' => 'Refunded Amount of this Transaction' . $reference,
                    'received_by' => 1,
                ]);
            }

            DB::table('orders')->where('id', $order->id)->update([
                'order_status_id' => 9,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function finish(QueuedTopup $row, $message, array $response)
    {
        $row->status = 'refunded';
        $row->provider_response = $response;
        $row->error_message = mb_substr((string) $message, 0, 2000);
        $row->failed_at = date('Y-m-d H:i:s');
        $row->refunded_at = date('Y-m-d H:i:s');
        $row->save();

        if ($row->track_order_id) {
            DB::table('track_orders')->where('id', $row->track_order_id)->update([
                'status' => 0,
                'remarks' => 'Provider failure confirmed. Refund created.',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $row->user_id,
            ]);
        }

        if (Schema::hasTable('topup_confirm_attempts')) {
            DB::table('topup_confirm_attempts')->where('request_id', $row->request_id)->update([
                'status' => 'REFUNDED',
                'error_message' => mb_substr((string) $message, 0, 1000),
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
