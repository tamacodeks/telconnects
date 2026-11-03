<?php

namespace App\Console\Commands;

use App\Services\TopupCallbackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReloadlyCallback extends Command
{
    protected $signature = 'calback:Reloadlycallback';
    protected $description = 'Fetch failed reloadly top-ups and refund each transaction once.';

    public function handle()
    {
        try {
            app(TopupCallbackService::class)->sync('reloadly');
            return 0;
        } catch (\Throwable $exception) {
            Log::warning('Unable to process reloadly callbacks.', [
                'error' => $exception->getMessage(),
            ]);
            return 1;
        }
    }
}
