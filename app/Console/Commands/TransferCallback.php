<?php

namespace App\Console\Commands;

use App\Services\TopupCallbackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TransferCallback extends Command
{
    protected $signature = 'refund:TransferCallback';
    protected $description = 'Fetch failed transfer top-ups and refund each transaction once.';

    public function handle()
    {
        try {
            app(TopupCallbackService::class)->sync('transfer');
            return 0;
        } catch (\Throwable $exception) {
            Log::warning('Unable to process transfer callbacks.', [
                'error' => $exception->getMessage(),
            ]);
            return 1;
        }
    }
}
