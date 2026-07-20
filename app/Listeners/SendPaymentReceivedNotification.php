<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use app\Library\AppHelper;
use App\Models\Transaction;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceivedNotification implements ShouldQueue
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
     * @param  PaymentReceived  $event
     * @return void
     */
    public function handle(PaymentReceived $event)
    {
        if(ENABLE_EMAIL == 1){
            $emails = explode(',',PAYMENT_EMAILS);
            $payment = $event->payment;
            $transaction = Transaction::find($payment->transaction_id);
            $iso_code = optional(User::find($payment->user_id))->currency;
            $send_email_order_data = array(
                'reseller_name' => optional(User::find($payment->user_id))->username,
                'updater' => optional(User::find($payment->received_by))->username,
                'oldBalance' => AppHelper::formatAmount($iso_code,$transaction->prev_bal),
                'newBalance' => AppHelper::getBalance($payment->user_id,$iso_code,true),
                'amount' =>  AppHelper::formatAmount($iso_code,$payment->amount),
                'desc' => $payment->description . " by Credit Balance",
                'checkup' => "false"
            );
            try{
                Mail::send('emails.payment_update', $send_email_order_data, function($message) use ($emails,$payment,$iso_code)
                {
                    $message->to($emails)->from('noreply@tamaexpress.com', 'TamaExpress Reseller System')->subject(trans('users.lbl_user')." ".optional(User::find($payment->received_by))->username." ". AppHelper::formatAmount($iso_code,$payment->amount) . " ".trans('users.updated_by')." " .optional(User::find($payment->received_by))->username);
                });
                AppHelper::logger('success','Payment Email Sent',"Sent Payment Email has been successfully!");
            }catch (\Exception $e){
                AppHelper::logger('warning','Payment Email Exception',$e->getMessage());
            }
        }
    }
}
