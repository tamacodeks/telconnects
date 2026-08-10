<?php

namespace App\Console\Commands;

use App\Models\TopupConfirmAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReconcileTopupConfirmAttempts extends Command
{
    protected $signature = 'topup:confirm-attempts:reconcile {--minutes=10} {--mark-unknown}';
    protected $description = 'Report stale TamaTopup confirmation attempts and optionally mark them UNKNOWN.';

    public function handle()
    {
        if (!Schema::hasTable('topup_confirm_attempts')) {
            $this->error('topup_confirm_attempts table does not exist. Run migrations first.');
            return 1;
        }

        $minutes = max(1, (int) $this->option('minutes'));
        $cutoff = now()->subMinutes($minutes);
        $statuses = ['PROCESSING', 'API_REQUEST_STARTED', 'API_RESPONSE_RECEIVED'];

        $attempts = TopupConfirmAttempt::whereIn('status', $statuses)
            ->where('updated_at', '<=', $cutoff)
            ->orderBy('updated_at')
            ->get();

        if ($attempts->isEmpty()) {
            $this->info('No stale topup confirmation attempts found.');
            return 0;
        }

        $this->table(
            ['id', 'request_id', 'provider', 'mobile', 'user_id', 'status', 'updated_at'],
            $attempts->map(function ($attempt) {
                return [
                    $attempt->id,
                    $attempt->request_id,
                    $attempt->provider,
                    $attempt->mobile,
                    $attempt->user_id,
                    $attempt->status,
                    (string) $attempt->updated_at,
                ];
            })->toArray()
        );

        if ($this->option('mark-unknown')) {
            TopupConfirmAttempt::whereIn('id', $attempts->pluck('id')->all())->update([
                'status' => 'UNKNOWN',
                'error_message' => 'Marked UNKNOWN by topup confirm reconciliation command after stale processing window.',
                'updated_at' => now(),
            ]);

            $this->warn($attempts->count() . ' stale attempts marked UNKNOWN.');
        }

        return 0;
    }
}
