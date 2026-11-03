<?php

namespace App\Console\Commands;

use App\Models\QueuedTopup;
use App\Services\TopupWorkerHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TopupQueueStatus extends Command
{
    protected $signature = 'topups:status';
    protected $description = 'Show top-up worker heartbeat, backlog, and requests needing reconciliation.';

    public function handle()
    {
        $this->info('Worker heartbeat: ' . ((new TopupWorkerHealth)->isRunning() ? 'ONLINE' : 'OFFLINE'));
        if (!Schema::hasTable('queued_topups')) {
            $this->error('queued_topups is missing. Apply migrations first.');
            return 1;
        }

        $counts = DB::table('queued_topups')->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->get()->map(function ($row) {
                return [$row->status, $row->total];
            })->toArray();
        $this->table(['Status', 'Count'], $counts);
        $this->table(['ID', 'Provider', 'Order', 'Request ID', 'Status', 'Error'],
            QueuedTopup::whereIn('status', ['pending', 'processing', 'unknown'])
                ->orderBy('id')->limit(20)->get()->map(function ($row) {
                    return [$row->id, $row->provider, $row->order_id, $row->request_id, $row->status, $row->error_message];
                })->toArray());

        return 0;
    }
}
