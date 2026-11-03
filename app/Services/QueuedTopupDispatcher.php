<?php

namespace App\Services;

use App\Jobs\ProcessQueuedTopup;
use App\Models\QueuedTopup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class QueuedTopupDispatcher
{
    public function dispatch($id)
    {
        return DB::transaction(function () use ($id) {
            $row = QueuedTopup::whereKey($id)->lockForUpdate()->first();
            if (!$row || $row->status !== 'pending' || $row->dispatched_at) {
                return false;
            }

            // Use the same database transaction as the local debit and order.
            // Explicit push also avoids Laravel 5.6 PendingDispatch destructor errors.
            Queue::connection('topups')->push(new ProcessQueuedTopup($row->id), '', 'topups');
            $row->dispatched_at = date('Y-m-d H:i:s');
            $row->save();

            return true;
        });
    }
}
