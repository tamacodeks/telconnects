<?php

namespace App\Console\Commands;

use App\Models\QueuedTopup;
use App\Services\QueuedTopupDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DispatchPendingQueuedTopups extends Command
{
    protected $signature = 'topups:dispatch-pending {--limit=100}';
    protected $description = 'Dispatch queued top-ups that were stored but not added to the worker queue.';

    public function handle()
    {
        if (!Schema::hasTable('queued_topups')) {
            $this->error('queued_topups table does not exist. Run migrations first.');
            return 1;
        }

        $limit = max(1, min(500, (int) $this->option('limit')));
        $rows = QueuedTopup::where('status', 'pending')
            ->whereNull('dispatched_at')
            ->where('created_at', '<=', now()->subMinute())
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($rows as $row) {
            try {
                app(QueuedTopupDispatcher::class)->dispatch($row->id);
            } catch (\Throwable $exception) {
                Log::critical('Unable to recover pending queued top-up dispatch.', [
                    'queued_topup_id' => $row->id,
                    'request_id' => $row->request_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info($rows->count() . ' pending queued top-up(s) checked for dispatch.');
        return 0;
    }
}
