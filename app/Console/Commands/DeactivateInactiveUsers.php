<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeactivateInactiveUsers extends Command
{
    protected $signature = 'users:deactivate-inactive {--days=30 : Number of inactive days before deactivation} {--dry-run : Show matching users without updating status}';

    protected $description = 'Deactivate active manager and retailer users after configured inactivity days.';

    public function handle()
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = Carbon::now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        $query = User::query()
            ->where('status', 1)
            ->whereIn('group_id', [3, 4])
            ->where(function ($query) use ($cutoff) {
                $query->where('last_activity', '<=', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query->whereNull('last_activity')
                            ->where('created_at', '<=', $cutoff);
                    });
            });

        $matchedUsers = (clone $query)
            ->select('id', 'username', 'group_id', 'last_activity', 'created_at')
            ->orderBy('group_id')
            ->orderBy('id')
            ->get();

        if ($dryRun) {
            $this->info('Inactive users matched: '.$matchedUsers->count());
            foreach ($matchedUsers as $user) {
                $this->line(sprintf(
                    '#%d %s group:%d last_activity:%s created_at:%s',
                    $user->id,
                    $user->username,
                    $user->group_id,
                    $user->last_activity ?: '-',
                    $user->created_at ?: '-'
                ));
            }

            return 0;
        }

        $updated = $query->update([
            'status' => 0,
            'updated_at' => Carbon::now(),
        ]);

        Log::info('Inactive manager/retailer users deactivated', [
            'days' => $days,
            'cutoff' => $cutoff->format('Y-m-d H:i:s'),
            'matched_ids' => $matchedUsers->pluck('id')->all(),
            'updated' => $updated,
        ]);

        $this->info('Deactivated users: '.$updated);

        return 0;
    }
}
