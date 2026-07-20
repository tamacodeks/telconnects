<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PaymentNoti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:noti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the database in a period';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = date("Y-m-d");
        try{
            $transaction = \App\Models\Transaction::join('users', 'users.id', 'transactions.user_id')
                ->where('type', 'credit')
                ->whereBetween('transactions.created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->select('transactions.*', 'users.username', 'users.parent_id')
                ->get();

//        Log::info("$transaction");
//        dd($transaction);
            //send an email to configure email
            $emails_tmp = "sydkhalid7@gmail.com";
            $emails = explode(',', $emails_tmp);
            $send_email_data = array(
                'transactions' => $transaction,
            );

            \Mail::send('emails.payment_daily_cron', $send_email_data, function ($message) use ($emails) {
                $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                $message->to($emails)->subject('DEMAT PRO Daily Payments Report');
            });
            Log::info("DEMAT PRO Daily Payments Report sent to $emails_tmp successfully!");
        }catch (\Exception $exception)
        {
            Log::warning('Unable to send sent DEMAT PRO orders report! => '.$exception->getMessage());
        }
    }
}
