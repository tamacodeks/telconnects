<?php

namespace App\Listeners;

use App\Events\CallingCardPinPrint;
use App\Models\CallingCard;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Facades\Slack;

class CCPrintSlackNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CallingCardPinPrint  $event
     * @return void
     */
    public function handle(CallingCardPinPrint $event)
    {
        if(ENABLE_SLACK == 1){
            $pin = $event->pin;
            $user_info = User::find($pin->used_by);
            $slack_data = [
                [
                    'title' => 'Retailer Name',
                    'value' => $user_info->username
                ],
                [
                    'title' => 'Card Name',
                    'value' => $pin->name,
                ],
                [
                    'title' => 'Serial',
                    'value' => $pin->serial
                ],
                [
                    'title' => 'Printed at',
                    'value' => $pin->updated_at
                ]
            ];
            Slack::attach([
                'fallback' => "DEMAT PRO Pin Usage",
                'text' => "DEMAT PRO Pin Usage",
                'color' => '#1764a8',
                'fields' => $slack_data
            ])->send('DEMAT PRO');
            Log::info('Slack notification for pin print has been sent');
        }
    }
}
