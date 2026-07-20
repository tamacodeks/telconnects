<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ResetTransactionCorrectionsToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:reset-corrections-today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset transaction is_corection to 0 for today (admin/manager/retailer users).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $end = Carbon::now();
        $start = $end->copy()->subHours(3);

        if (!app()->runningInConsole()) {
            $this->error('Command can only run in console.');
            return 1;
        }

        $batchSize = 5000;
        $totalUpdated = 0;
        do {
            $ids = DB::table('transactions')
                ->whereBetween('date', [$start, $end])
                ->where('is_corection', 1)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $updated = DB::table('transactions')
                ->whereIn('id', $ids)
                ->update(['is_corection' => 0]);

            $totalUpdated += $updated;
        } while ($ids->count() === $batchSize);

        $this->info('Updated rows: ' . $totalUpdated);
        return 0;
    }
}
