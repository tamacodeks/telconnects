<?php

namespace App\Console\Commands;

use App\Services\TopupQueueMaintenance;
use App\Services\TopupWorkerHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

class WorkTopupQueue extends Command
{
    protected $signature = 'topups:work';
    protected $description = 'Process top-ups continuously and check recovery and queued refunds every minute.';

    public function handle()
    {
        foreach (['topup_processing_settings', 'queued_topups', 'jobs'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->error($table . ' is missing. Apply the top-up migrations first.');
                return 1;
            }
        }

        if (config('queue.connections.topups.driver') !== 'database') {
            $this->error('The topups queue must use the database driver.');
            return 1;
        }

        if (!Schema::hasColumn('queued_topups', 'mobile')) {
            $this->error('queued_topups.mobile is missing. Apply the latest top-up migration first.');
            return 1;
        }

        $lock = fopen(storage_path('framework/topups-worker.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            $this->error('A top-up worker is already running for this application.');
            return 1;
        }

        $health = new TopupWorkerHealth;
        $lastMaintenance = 0;
        register_shutdown_function(function () use ($health, $lock) {
            $health->clear();
            flock($lock, LOCK_UN);
            fclose($lock);
        });

        Queue::looping(function ($event) use ($health, &$lastMaintenance) {
            if ($event->connectionName !== 'topups') {
                return;
            }

            $health->heartbeat();
            if (time() - $lastMaintenance >= 60) {
                $lastMaintenance = time();
                try {
                    (new TopupQueueMaintenance)->run();
                } catch (\Throwable $exception) {
                    Log::error('Top-up queue maintenance failed.', ['error' => $exception->getMessage()]);
                }
                $health->heartbeat();
            }
        });

        $this->info('Top-up worker started. Recovery and queued callback refunds run automatically.');
        return $this->call('queue:work', [
            'connection' => 'topups', '--queue' => 'topups', '--sleep' => 1,
            '--tries' => 1, '--timeout' => 180,
        ]);
    }
}
