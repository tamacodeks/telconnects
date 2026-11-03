<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TopupWorkerHealth
{
    public function heartbeat()
    {
        Cache::store('file')->put('topups:worker:heartbeat', time(), 5);
    }

    public function isRunning()
    {
        $lastSeen = (int) Cache::store('file')->get('topups:worker:heartbeat', 0);
        return $lastSeen > 0 && time() - $lastSeen < 300;
    }

    public function clear()
    {
        Cache::store('file')->forget('topups:worker:heartbeat');
    }
}
