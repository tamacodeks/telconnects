<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ReleaseCard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:card';

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
            $get_cards = \App\Models\CallingCardPin::where('is_locked','1')->get();

             foreach ($get_cards as $getcard){

                 \App\Models\CallingCardPin::where('id', $getcard['id'])
                     ->update([
                         'is_locked' => '0',
                         'locked_by' => NULL,
                         'locked_at' => Null,
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);
             }
            Log::info("DEMAT PRO Calling Card Released successfully!");
        }catch (\Exception $exception)
        {
            Log::warning('Unable to Released Calling Card! => '.$exception->getMessage());
        }
    }
}
