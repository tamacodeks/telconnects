<?php

namespace App\Console\Commands;

use App\Services\TopupCallbackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TellusCallback extends Command
{
    protected $signature = 'calback:Telluscallback';
    protected $description = 'Fetch failed tellus top-ups and refund each transaction once.';

    public function handle()
    {
        try {
            app(TopupCallbackService::class)->sync('tellus');
            return 0;
        } catch (\Throwable $exception) {
            Log::warning('Unable to process tellus callbacks.', [
                'error' => $exception->getMessage(),
            ]);
            return 1;
        }
    }
}
