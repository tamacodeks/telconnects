<?php

namespace App\Console;

use App\Console\Commands\DumpDB;
use App\Console\Commands\DeactivateInactiveUsers;
use App\Console\Commands\PaymentNoti;
use App\Console\Commands\ReleaseCard;
use App\Console\Commands\ReloadlyCallback;
use App\Console\Commands\ResetTransactionCorrectionsToday;
use App\Console\Commands\TellusCallback;
use App\Console\Commands\TransferCallback;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DumpDB::class,
        DeactivateInactiveUsers::class,
        PaymentNoti::class,
        ReleaseCard::class,
        ReloadlyCallback::class,
        ResetTransactionCorrectionsToday::class,
        TellusCallback::class,
        TransferCallback::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('dump:db')->everyMinute();
//        $schedule->command('payment:noti')->everyMinute();
        $schedule->command('release:card')->dailyAt('11:00');
        $schedule->command('report:TamaMasterRetailer')->dailyAt('11:00');
        $schedule->command('users:deactivate-inactive --days=30')->dailyAt('00:15');
        $schedule->command('calback:Reloadlycallback')->everyMinute();
        $schedule->command('calback:Telluscallback')->everyMinute();
        $schedule->command('transactions:reset-corrections-today')->everyMinute();
//        $schedule->command('refund:Transfercallback')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
