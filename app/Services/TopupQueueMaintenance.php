<?php

namespace App\Services;

use App\Models\QueuedTopup;
use Illuminate\Support\Facades\Log;

class TopupQueueMaintenance
{
    public function run()
    {
        $pending = QueuedTopup::where('status', 'pending')->whereNull('dispatched_at')
            ->where('created_at', '<=', date('Y-m-d H:i:s', time() - 60))->orderBy('id')->limit(100)->get();
        foreach ($pending as $row) {
            (new QueuedTopupDispatcher)->dispatch($row->id);
        }

        $stale = QueuedTopup::where('status', 'processing')
            ->where('updated_at', '<=', date('Y-m-d H:i:s', time() - 600))->limit(100)->get();
        foreach ($stale as $row) {
            (new QueuedTopupProcessor)->markUnknown($row->id, 'Worker stopped before finalizing this request. Reconcile before any refund or resend.');
        }

        $recoverable = QueuedTopup::where('status', 'unknown')->whereNotNull('provider_response')
            ->where(function ($query) {
                $query->where('provider_response', 'like', '%"classification":"succeeded"%')
                    ->orWhere('provider_response', 'like', '%"classification":"rejected"%');
            })
            ->orderBy('id')->limit(100)->get();
        foreach ($recoverable as $row) {
            try {
                (new QueuedTopupProcessor)->reconcileStoredResponse($row->id);
            } catch (\Throwable $exception) {
                Log::warning('Stored top-up response could not be finalized yet.', [
                    'queued_topup_id' => $row->id, 'error' => $exception->getMessage(),
                ]);
            }
        }

        $providers = QueuedTopup::where('status', 'succeeded')->whereNull('refunded_at')
            ->where('processed_at', '>=', date('Y-m-d H:i:s', time() - 10 * 86400))
            ->distinct()->pluck('provider');
        foreach ($providers as $provider) {
            if (!in_array($provider, ['reloadly', 'transfer', 'tellus'], true)) {
                continue;
            }

            try {
                (new TopupCallbackService)->sync($provider, true);
            } catch (\Throwable $exception) {
                Log::warning('Queued top-up callback check failed; it will be checked on the next cycle.', [
                    'provider' => $provider,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
