<?php

namespace App\Jobs;

use App\Services\QueuedTopupProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessQueuedTopup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 180;
    protected $queuedTopupId;

    public function __construct($queuedTopupId)
    {
        $this->queuedTopupId = (int) $queuedTopupId;
        $this->onConnection('topups');
        $this->onQueue('topups');
    }

    public function handle(QueuedTopupProcessor $processor)
    {
        $processor->process($this->queuedTopupId);
    }

    public function failed($exception)
    {
        app(QueuedTopupProcessor::class)->markUnknown($this->queuedTopupId, $exception->getMessage());

        Log::critical('Queued top-up job failed unexpectedly; refund withheld for reconciliation.', [
            'queued_topup_id' => $this->queuedTopupId,
            'error' => $exception->getMessage(),
        ]);
    }
}
