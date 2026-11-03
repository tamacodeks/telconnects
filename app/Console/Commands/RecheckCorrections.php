<?php

namespace App\Console\Commands;

use app\Library\AppHelper;
use Illuminate\Console\Command;
use App\Models\Transaction;

class RecheckCorrections extends Command
{
    protected $signature = 'transactions:recheck-corrections';
    protected $description = 'Reset is_corection daily or hourly and re-run Correction_balance for each user. Automatically uses previous day between midnight and 6AM.';

    public function handle()
    {
        $currentHour = (int) date('H'); // Server hour (0–23)

        // ✅ If time is between 00:00 and 05:59 → use previous day
        if ($currentHour < 6) {
            $targetDate = date('Y-m-d', strtotime('-1 day'));
            $this->info("⏪ Night-time run detected ({$currentHour}h). Using previous day: {$targetDate}");
        } else {
            $targetDate = date('Y-m-d');
            $this->info("📅 Daytime run detected ({$currentHour}h). Using current day: {$targetDate}");
        }

        // Step 1: Reset correction flag for target date
        $count = Transaction::whereBetween('date', [$targetDate . ' 00:00:00', $targetDate . ' 23:59:59'])
            ->update(['is_corection' => 0]);

        $this->info("✅ Reset {$count} transactions for {$targetDate}");

        // Step 2: Get all users who had transactions on that date
        $userIds = Transaction::whereBetween('date', [$targetDate . ' 00:00:00', $targetDate . ' 23:59:59'])
            ->distinct()
            ->pluck('user_id');

        // Step 3: Re-run correction per user
        foreach ($userIds as $userId) {
            try {
                AppHelper::Correction_balance($userId, $targetDate);
                $this->info("✔ Correction recalculated for user #{$userId}");
            } catch (\Throwable $e) {
                $this->error("❌ Failed for user #{$userId}: " . $e->getMessage());
            }
        }

        $this->info('🔁 Correction recheck completed successfully at ' . now());
    }
}
