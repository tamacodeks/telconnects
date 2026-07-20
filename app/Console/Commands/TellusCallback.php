<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TellusCallback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calback:Telluscallback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch failed tellus callbacks from tama demat';

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
        try{
            //call ding topup api
            $dingTTClient = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            $response = $dingTTClient->request("GET", "topup/callback/tellus", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                $response_api = json_decode((string)$response->getBody(), true);
                $response_data = $response_api['failed'];
                foreach($response_data as $res){
                    $getOrders = Order::where('txn_ref', $res['external_id'])->where('order_status_id',7)->where('service_id',2)->get();

                    foreach($getOrders as $order)
                    {
                        Order::where('id', $order['id'])->update([
                            'order_status_id' => 9
                        ]);
                        if($order['is_parent_order'] == 1){
                            $payment_id = Payment::insertGetId([
                                'user_id' => $order['user_id'],
                                'transaction_id' => NULL,
                                'date' => date('Y-m-d H:i:s'),
                                'amount' => $order['order_amount'],
                                'description' => 'Refunded Amount of this Transaction'.$order['txn_ref'],
                                'received_by' => 1
                            ]);
                        }

                    }
                }

            }
        }catch (\Exception $exception){
            Log::warning('Unable to Failed Callbacks report! => '.$exception->getMessage());
        }
    }
}
