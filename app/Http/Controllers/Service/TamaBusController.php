<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\TamaBus\ApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\User;
use App\DingDenomination;
use App\Models\AppCommission;
use App\Models\Order;
use GuzzleHttp\Client;

class TamaBusController extends Controller
{
    private $service_id;
    private $decipher;
    private $client;
    function __construct()
    {
        parent::__construct();
        $this->service_id = 9;
        $this->decipher = new SecurityHelper();
        $this->middleware(function ($request, $next) {
            if(API_TOKEN == '' || API_END_POINT == ''){
                AppHelper::logger('warning','API SETTINGS ERROR',"Missing API Token or API end point url",request()->all(),true);
                return redirect()->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            if(AppHelper::user_access($this->service_id,auth()->user()->id) == 0){
                AppHelper::logger('warning','Access Violation',auth()->user()->username. " trying to access Flixbus service",request()->all(),true);
                return redirect()->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            //lets check with this user parent has access
            if(\app\Library\AppHelper::skip_service_as_menu('flix-bus') == false){
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . " trying to access Flixbus service but parent of this user does not have a access", request()->all());
                return redirect('dashboard')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            $this->client = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 120,
            ]);
            return $next($request);
        });
    }
//    fetch cites and stations
    function index()
    {
        try {
            $response = $this->client->request('GET', 'flix-bus', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $citiesAndStations = json_decode((string)$response->getBody(), true);
            }
            return view('service.tama-bus.index', [
                'page_title' => "Flix Bus",
                'cities' => $citiesAndStations['data']
            ]);

        }catch (\Exception $e){
            AppHelper::logger('warning',"Flix bus API HTTP Exception",$e->getMessage(),$e,true);
            return redirect('dashboard')
                ->with('message', trans('common.access_violation'))
                ->with('message_type', 'warning');
        }
    }
    //search buses and details
    function search(Request $request, $operation)
    {
        $result = [];
        if($request->cityFrom == "" || $request->from == "" || $request->cityTo == "" ||$request->to == "" ||$request->passengers == "" || $request->departure_date == "")
        {
            Log::warning("Search Flix Bus Validation Failed");
            return responder()->error("BAD_REQUEST", "Missing Field")->respond(400);
        }
        $params = [
            "search_by" => 'cities',
            "from"=>$request->cityFrom,
            "to"=>$request->cityTo,
            "departure_date"=>$request->departure_date,
            "d_date"=>$request->departure_date,
            "adult"=> ($request->adult) ? $request->adult : '0',
            "children"=>($request->children) ? $request->children : '0',
            "bikes"=>($request->bikes) ? $request->bikes : '0',
            "currency"=>'EUR',
            "cityFrom"=> $request->from,
            "cityTo"=> $request->to,
            "passengers"=> $request->passengers,
            "sort_by" => $request->sort_by
        ];
        try {
            $response = $this->client->request('GET', 'flix-bus/search?'. http_build_query($params), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'verify' => false, // Disable SSL verification temporarily
            ]);
            if ($response->getStatusCode() == 200) {
                $results = json_decode((string)$response->getBody(), true);
                return [
                    'status' => $response->getStatusCode(),
                    'message' => "Buses Details fetched",
                    'data' => $results
                ];
            }
        } catch (\Exception $e) {
            AppHelper::logger('warning',"Flix bus API HTTP Exception",$e->getMessage(),$e,true);
            return responder()->error("BAD_REQUEST", "Missing Field")->respond(400);
        }
    }

    //create reservations
    function create_reservations(Request $request)
    {
        $result = [];
        if($request->trip_uid == "" || $request->currency == "" )
        {
            Log::warning("Flix Bus Validation Failed");
            return responder()->error("BAD_REQUEST", "Missing Field")->respond(400);
        }
        $params["trip_uid"] =$request->trip_uid;
        $params["adult"] = ($request->adult) ? $request->adult : '0';
        $params["children"] =($request->children) ? $request->children : '0';
        $params["bikes"] = ($request->bikes) ? $request->bikes : '0';
        $params["currency"] ='EUR';
        try {

            $response = $this->client->request('POST', 'flix-bus/create_reservations', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);

            if ($response->getStatusCode() == 200) {
                $results = json_decode((string)$response->getBody(), true);
                return [
                    'status' => $response->getStatusCode(),
                    'message' => "Buses Details fetched successfully",
                    'data' => $results
                ];
            }
        } catch (\Exception $e) {
            return responder()->error("EXCEPTION", "Please try again later!")->respond(500);
        }
    }


    //order succesfully
    function add_passenger_details(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "firstname.*" => "required",
            "lastname.*" => "required",
            "birthdate.*" => "required",
            "reservation_token.*" => "required",
            "reservation_id.*" => "required",
            "email" => "required",
            "phone_number" => "required",
        ]);

        if($validator->fails())
        {
            Log::warning("FlixBus validation failed",[$request->except('_token')]);
            AppHelper::logger('warning', 'FlixBus API', "Validation Error Missing Field" ,$request->except('_token'));
            return responder()->error(201, 'Please fill neccessary fields, validation failed!');
        }
        $params["reservation_token"] =$request->reservation_token[0];
        $params["reservation_id"] =$request->reservation_id[0];
        $params["email"] =$request->email;
        $params["phone_number"] =$request->phone_number;
        $params["total_price"] =$request->total_price;

        for($i=0; $i < count($request->type); $i++ ){
            $params["passengers[$i][type]"] = $request->type[$i];
            $params["passengers[$i][product_type]"] = $request->product_type[$i];
            $params["passengers[$i][reference_id]"] = $request->reference_id[$i];
            $params["passengers[$i][passenger_no]"] = $request->passenger_no[$i];
            $params["passengers[$i][firstname]"] = $request->firstname[$i];
            $params["passengers[$i][lastname]"] = $request->lastname[$i];
            $params["passengers[$i][birthdate]"] = $request->birthdate[$i];
        }
        $user_info = User::find(auth()->user()->id);
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id,auth()->user()->currency,true);
        $euro_amount = str_replace(',', '', $request->input('total_price'));
        $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
        $created_at = date("Y-m-d H:i:s");
        $mobile_operator ="flixbus";
        $mobile_number ="917904721979";
        if($request->phone_number){
            $mobile_number = $request->phone_number;
        }
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                //Daily Limit for this user
                //order will be failed
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                $getBalance = (\app\Library\AppHelper::sendMail($r_bal,$daily_limit,$getBalance,$blink_limit,$manager_id,auth()->user()->username));
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm Flix bus order', $request->all());
                Log::warning('Flix Bus Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return responder()->error(400, trans('common.contact_manager'));
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm Flix bus order', $request->all());
            Log::warning('Flix Bus Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return responder()->error(400, trans('common.parent_rule_failed'));
        }

        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'Flix Bus Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm Flix Bus order', $request->all());
                return responder()->error(400, trans('common.msg_order_failed_due_bal'));
            }
        }

        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $response = $this->client->request('POST', 'flix-bus/add_passengers_details', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);

            //response from Flix demat
            if($response->getStatusCode() == 200){
                //Data From Api response
                $print_ticket1 = json_decode((string)$response->getBody(), true);
                Log::info("FlixBus", [$print_ticket1]);
                $txn_ref = isset($print_ticket1['txn_ref']) ? $print_ticket1['txn_ref'] : $tt_txn_id;
                $print_ticket = 'flix-bus/download/'.$print_ticket1['print_ticket'][0].','.$print_ticket1['print_ticket'][1];
                $order_comment = $user_info->username . " Flix bus for " . $euro_amount . " Print Ticket Link is " . $print_ticket1['print_ticket'][0];
                $order_desc = $order_comment;
                $ins = $print_ticket1['print_ticket'][0];
                $link = $print_ticket1['print_ticket'][1];
            }

            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //Insert order
            $order_id = ServiceHelper::save_order('',$created_at, $user_info->id, $this->service_id, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$sale_margin,$order_amount,$order_amount,$order_amount,NULL,0,0);


            //insert order items
            $order_item_id = ServiceHelper::save_orders_items($order_id, $mobile_number, $euro_amount, $mobile_operator, $ins, $link, $created_at,$user_info->id);

            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {

                $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');

                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);

                //parent order insertion
                $parent_order_id = ServiceHelper::save_order($order_desc,$created_at, $user_info->id, $this->service_id, '7', $parent_trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$parent_sale_margin,$order_amount,$buying_price_parent,$order_amount,$order_item_id,1,0);


                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, $this->service_id, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$app_sale_margin,$buying_price_parent,$buying_price_app,$buying_price_parent,$order_item_id,1,0);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, $this->service_id, '7', $trans_id, $txn_ref, '',$user_info->currency,$euro_amount,$app_sale_margin, $order_amount,$buying_price_app, $order_amount,$order_item_id,1,0);

            }
            \DB::commit();
            AppHelper::logger('success', 'Flix Bus Order #' . $order_id, $order_desc);
            return $print_ticket;
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " Flix Bus API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'Flix Bus Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return responder()->error(500, 'Flix Bus Exception ');
        }
    }
    function download(Request $request, $link = null)
    {
        $instruction = trim((string) $request->query('instruction', ''));
        $ticketLink = trim((string) $request->query('link', ''));

        if ($link !== null && ($instruction === '' || $ticketLink === '')) {
            $decodedLink = rawurldecode((string) $link);
            $parts = explode(',', $decodedLink, 2);
            $instruction = $instruction !== '' ? $instruction : trim($parts[0] ?? '');
            $ticketLink = $ticketLink !== '' ? $ticketLink : trim($parts[1] ?? '');
        }

        if ($instruction === '' && $ticketLink === '') {
            abort(404);
        }

        if ($ticketLink === '' && filter_var($instruction, FILTER_VALIDATE_URL)) {
            return redirect()->away($instruction);
        }

        if ($ticketLink === '') {
            return redirect('transactions')
                ->with('message', __('bus.messages.ticket_issue_failed', ['id' => $instruction ?: 'download']))
                ->with('message_type', 'warning');
        }

        $downloadKey = rawurlencode($instruction) . ',' . rawurlencode($ticketLink);

        try {
            $response = $this->client->request('GET', 'flix-bus/download/' . $downloadKey, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
            ]);

            $res = json_decode((string) $response->getBody(), true);

            if (!empty($res['reminder_link'])) {
                return redirect()->away($res['reminder_link']);
            }
        } catch (\Exception $e) {
            AppHelper::logger('warning', 'Flix Bus Ticket Download Failed', $e->getMessage(), [
                'instruction' => $instruction,
                'link' => $ticketLink,
            ]);
        }

        if (filter_var($ticketLink, FILTER_VALIDATE_URL)) {
            return redirect()->away($ticketLink);
        }

        return redirect()->back()
            ->with('message', __('bus.messages.ticket_issue_failed', ['id' => $instruction ?: 'download']))
            ->with('message_type', 'warning');
    }
    function both()
    {
        try {
            $response = $this->client->request('GET', 'flix-bus', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $citiesAndStations = json_decode((string)$response->getBody(), true);
            }
            return view('service.tama-bus.flix', [
                'page_title' => "Flix Bus",
                'cities' => $citiesAndStations['data']
            ]);

        }catch (\Exception $e){
            AppHelper::logger('warning',"Flix bus API HTTP Exception",$e->getMessage(),$e,true);
            return redirect('dashboard')
                ->with('message', trans('common.access_violation'))
                ->with('message_type', 'warning');
        }
    }
    public function search_bus(Request $request)
    {
        // Validate required fields
        if (empty($request->cityFromHid) || empty($request->cityFrom) || empty($request->cityToHid) || empty($request->cityTo) || empty($request->passengers) || empty($request->departureDate)) {
            Log::warning("Search Flix Bus Validation Failed: Missing Fields");
            return redirect()->back()->withErrors(['error' => 'Missing Required Fields'])->withInput();
        }

        // Prepare the request parameters
        $params = [
            "search_by" => 'cities',
            "from" => $request->cityFromHid,
            "to" => $request->cityToHid,
            "geolatfrom" => $request->geolatfrom ?? '',
            "geolonfrom" => $request->geolonfrom ?? '',
            "geolatto" => $request->geolatto ?? '',
            "geolonto" => $request->geolonto ?? '',
            "departure_date" => $request->departureDate,
            "d_date" => $request->departureDate,
            "adult" => $request->adult ?? '0',
            "children" => $request->child ?? '0',
            "bikes" => $request->bikes ?? '0',
            "currency" => 'EUR',
            "cityFrom" => $request->cityFrom,
            "cityTo" => $request->cityTo,
            "passengers" => $request->passengers,
            "sort_by" => $request->sort_by ?? '',
            "sort_by_bus" => $request->sort_by_bus ?? '',
        ];
        // Log the parameters
        Log::info("Flix Bus search parameters", $params);

//        try {
        // Send GET request to Flix Bus API
        $response = $this->client->request('GET', 'flix-bus/bla/search?' . http_build_query($params), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . API_TOKEN
            ]
        ]);

        // Check if the response status is 200 OK
        if ($response->getStatusCode() === 200) {
            $results = json_decode($response->getBody()->getContents(), true);

            // Prepare data for session
            $busData = [
                'from_id' => $request->cityFromHid,
                'from_name' => $request->cityFrom,
                'geolatfrom' => $request->geolatfrom ?? '',
                'geolonfrom' => $request->geolonfrom ?? '',
                'geolatto' => $request->geolatto ?? '',
                'geolonto' => $request->geolonto ?? '',
                'to_id' => $request->cityToHid,
                'to_name' => $request->cityTo,
                'departure' => $request->departureDate,
                'passengers' => $request->passengers,
                'adult' => $request->adult ?? '0',
                'children' => $request->child ?? '0',
                "sort_by" => $request->sort_by ?? '',
                "sort_by_bus" => $request->sort_by_bus ?? ''
            ];

            // Check if there are no results
            if (empty($results)) {
                Log::error("Flix Bus API returned No Bus", ['status_code' => $response->getStatusCode()]);
                return redirect()->back()->with('bus_data', $busData)->withErrors(['error' => 'No Bus Found'])->withInput();
            }
            $sortedResults = $this->sortResults($results, $request->sort_by, $request->sort_by_bus);
            // Return back with bus results
            return redirect()->back()
                ->with('bus_results', $sortedResults)
                ->with('bus_data', $busData)
                ->with('total_bus', count($sortedResults));
        } else {
            Log::error("Flix Bus API returned a non-200 status code", ['status_code' => $response->getStatusCode()]);
            return redirect()->back()->withErrors(['error' => 'Failed to fetch bus details'])->withInput();
        }
//        } catch (\Exception $e) {
//            // Log the exception and return error response
//            Log::error("Flix Bus API HTTP Exception", ['message' => $e->getMessage()]);
//            return redirect()->back()->withErrors(['error' => 'An error occurred while fetching bus details'])->withInput();
//        }
    }
    function sortResults($result, $sort_by, $sort_by_bus) {
        // Initialize the collection
        $collection = collect($result); // Collect the array into a Collection object
        // Filter by bus type if applicable
        if ($sort_by_bus == 4) {
            // Filter by 'direct' bus type
            $collection = $collection->where('bus_type', 'direct');
        } elseif ($sort_by_bus == 5) {
            // Filter by 'Comuto Pro (BlaBlaCar Bus)' bus type
            $collection = $collection->where('bus_type', 'Comuto Pro (BlaBlaCar Bus)');
        }
        // Sorting logic
        if ($sort_by == 1) {
            // Sort by departure (raw_departure) ascending
            $sorted = $collection->sortBy('departure');
        } elseif ($sort_by == 2) {
            // Sort by total_price ascending
            $sorted = $collection->sortBy('total_price');
        } elseif ($sort_by == 3) {
            // Sort by departure (raw_departure) descending
            $sorted = $collection->sortBy('duration_hour');
        } else {
            // Default sort by departure ascending
            $sorted = $collection->sortBy('departure');
        }

        // Return sorted collection as array
        return $sorted->values()->toArray();
    }


    public function create_reservations_bus(Request $request)
    {
        // Validation for required fields
        if (empty($request->trip_uid) || empty($request->currency)) {
            Log::warning("Flix Bus Validation Failed");

            // Redirect back with an error message
            return redirect()->back()->withErrors(['message' => 'Missing required fields.'])->withInput();
        }

        // Prepare the params for the API request
        $params["trip_uid"] = $request->trip_uid;
        $params["adult"] = $request->input('adult', '0'); // Default to '0' if not provided
        $params["children"] = $request->input('children', '0'); // Default to '0' if not provided
        $params["bikes"] = $request->input('bikes', '0'); // Default to '0' if not provided
        $params["currency"] = $request->currency;

        try {
            $response = $this->client->request('POST', 'flix-bus/create_reservations_flix', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN,
                ],
                'form_params' => $params,
            ]);

            if ($response->getStatusCode() == 200) {
                $results = json_decode((string)$response->getBody(), true);
                // Store the entire $results array in the session under 'booking'
                return redirect()->back()->with([
                    'booking' => $results,
                    'price' => $request->total_price
                ]);
            } else {
                return redirect()->back()->withErrors(['message' => 'Failed to create reservation. Please try again.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error("Exception during reservation: " . $e->getMessage());

            // Redirect back with an exception message
            return redirect()->back()->withErrors(['message' => 'An error occurred. Please try again later.'])->withInput();
        }
    }

    function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "firstname.*" => "required",
            "lastname.*" => "required",
            "birthdate.*" => "required",
            "reservation_token.*" => "required",
            "reservation_id.*" => "required",
            "email" => "required",
            "phone_number" => "required",
        ]);

        if($validator->fails())
        {
            Log::warning("FlixBus validation failed",[$request->except('_token')]);
            AppHelper::logger('warning', 'FlixBus API', "Validation Error Missing Field" ,$request->except('_token'));
            return redirect('bus-v2')
                ->with('message', 'Validation Error Missing Field')
                ->with('message_type', 'warning');
        }
        $params["reservation_token"] =$request->reservation_token;
        $params["with_donation"] = true;
        $params["donation_partner"] = 'atmosfair';
        $params["reservation_id"] =$request->reservation_id;
        for($i=0; $i < count($request->firstname); $i++ ){
            $params["passengers[$i][firstname]"] = $request->firstname[$i];
            $params["passengers[$i][passenger_no]"] = $i;
            $params["passengers[$i][lastname]"] = $request->lastname[$i];
            $params["passengers[$i][phone]"] = $request->phone_number[$i];
            $params["passengers[$i][birthdate]"] = $request->birthdate[$i];
            $params["passengers[$i][type]"] = $request->product_type[$i];
            $params["passengers[$i][product_type]"] = $request->product_type[$i];
            $params["passengers[$i][reference_id]"] = $request->reference_id[$i];
            $params["passengers[$i][identification_type]"] = $request->identification_type[$i];
            $params["passengers[$i][identification_number]"] = $request->identification_number[$i];
            $params["passengers[$i][gender]"] = $request->gender[$i];
            $params["passengers[$i][citizenship]"] = $request->citizenship[$i];
            $params["passengers[$i][identification_issuing_country]"] = $request->identification_issuing_country[$i];
            $params["passengers[$i][identification_expiry_date]"] = $request->identification_expiry_date[$i];
            $params["passengers[$i][visa_permit_type]"] = $request->visa_permit_type[$i];
        }
        $params["email"] =$request->email[0];
        $params["departure_time"] = $request->departure_time;
        $params["from_name"] = $request->from_name;
        $params["to_name"] = $request->to_name;
        $params["total_price"] =$request->total_price;
        $params["price"] =$request->price;
        $user_info = User::find(auth()->user()->id);
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id,auth()->user()->currency,true);
        $euro_amount = str_replace(',', '', $request->input('price'));
        $user_service_commission = ServiceHelper::get_service_commission($user_info->id, $this->service_id);//service_id may change
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
        $created_at = date("Y-m-d H:i:s");
        $mobile_operator ="flixbus";
        $mobile_number ="+917904721979";
        if($request->phone_number){
            $mobile_number = preg_replace('/\D/', '', $request->phone_number[0]);
        }

        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                //Daily Limit for this user
                //order will be failed
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                $getBalance = (\app\Library\AppHelper::sendMail($r_bal,$daily_limit,$getBalance,$blink_limit,$manager_id,auth()->user()->username));
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm Flix bus order', $request->all());
                Log::warning('Flix Bus Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('bus-v2')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount,$this->service_id)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm Flix bus order', $request->all());
            Log::warning('Flix Bus Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            Log::warning('Flix Bus Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
            return redirect('bus-v2')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }

        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'Flix Bus Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm Flix Bus order', $request->all());
                return redirect('bus-v2')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }

        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $response = $this->client->request('POST', 'flix-bus/add_passenger_details_flix', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $params
            ]);
//            //response from Flix demat
            if($response->getStatusCode() == 200){
//                //Data From Api response
                $print_ticket = json_decode((string)$response->getBody(), true);

                // Log the ticket information
                Log::info("FlixBus", $print_ticket);
                // Assuming you want to work with the first element of the array
                $print_ticket1 = $print_ticket['print_ticket']; // Access the first (and only) element of the array


                // Check and set the transaction reference
                $txn_ref = isset($print_ticket['txn_ref']) ? $print_ticket['txn_ref'] : $tt_txn_id;

                // Construct the print ticket link (using the values from the print_ticket array)
                $print_ticket_link = 'flix-bus/download/'.$print_ticket1[0].','.$print_ticket1[1];

                // Create order comment and description
                $order_comment = $user_info->username . " Flix bus for " . $euro_amount . ". Print Ticket Link is " . $print_ticket1[0];
                $order_desc = $order_comment;

                // Separate instruction and link from the print_ticket array
                $ins = $print_ticket1[0]; // Instruction (ticket number)
                $link = $print_ticket1[1]; // Link (ticket reference)
            }
            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //Insert order
            $order_id = ServiceHelper::save_order('',$created_at, $user_info->id, $this->service_id, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$sale_margin,$order_amount,$order_amount,$order_amount,NULL,0,0);


            //insert order items
            $order_item_id = ServiceHelper::save_orders_items($order_id, $mobile_number, $euro_amount, $mobile_operator, $ins, $link, $created_at,$user_info->id);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {

                $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, $this->service_id);
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');

                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);

                //parent order insertion
                $parent_order_id = ServiceHelper::save_order($order_desc,$created_at, $user_info->id, $this->service_id, '7', $parent_trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$parent_sale_margin,$order_amount,$buying_price_parent,$order_amount,$order_item_id,1,0);


                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, $this->service_id, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$app_sale_margin,$buying_price_parent,$buying_price_app,$buying_price_parent,$order_item_id,1,0);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, $this->service_id, '7', $trans_id, $txn_ref, '',$user_info->currency,$euro_amount,$app_sale_margin, $order_amount,$buying_price_app, $order_amount,$order_item_id,1,0);

            }
            \DB::commit();
            AppHelper::logger('success', 'Flix Bus Order #' . $order_id, $order_desc);
            return redirect('transactions')
                ->with('message', 'download now')
                ->with('message_type', 'success');
        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " Flix Bus API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'Flix Bus Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('bus-v2')
                ->with('message', auth()->user()->username . " Flix Bus API Exception => " . $e->getMessage())
                ->with('message_type', 'warning');
        }
    }

    public function create_reservation_blabus(Request $request)
    {
//        dd($request->all());
        // Prepare the segments and passengers data from the request
        $segments = [];
        $client_ref = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        // Loop through each leg to construct segments
        foreach ($request->legs as $index => $leg) {
            $segments[] = [
                "client_ref" => $client_ref,
                "origin" => $leg['from_id'],
                "destination" => $leg['to_id'],
                "direction" => "outbound",
                "service_name" => $leg['service_name'],
                "service_identifier" => $leg['bus_id'],
                "start_validity_date" => $request->validity_end_date,
                "items" => [] // Will be populated with passenger data
            ];
        }

        $passengerCounter = 1;
        $passengers = [];

        // Add adults to segments and passengers
        if ($request->adult > 0) {
            for ($i = 0; $i < $request->adult; $i++) {
                $passengerId = "passenger_" . $passengerCounter++;

                // Add items to each segment with the appropriate tariff_code
                foreach ($segments as $index => &$segment) {
                    $segment['items'][] = [
                        "passenger_id" => $passengerId,
                        "tariff_code" => $request->legs[$index]['tariff_code'] // Use tariff_code from the corresponding leg
                    ];
                }

                $passengers[] = [
                    "type" => "A",
                    "disability_type" => "NH",
                    "id" => $passengerId
                ];
            }
        }

        // Add children to segments and passengers
        if ($request->children > 0) {
            for ($i = 0; $i < $request->children; $i++) {
                $passengerId = "passenger_" . $passengerCounter++;

                // Add items to each segment with the appropriate traffic_code
                foreach ($segments as $index => &$segment) {
                    $segment['items'][] = [
                        "passenger_id" => $passengerId,
                        "tariff_code" => $request->legs[$index]['tariff_code'] // Use tariff_code from the corresponding leg
                    ];
                }

                $passengers[] = [
                    "type" => "Y",
                    "disability_type" => "NH",
                    "id" => $passengerId
                ];
            }
        }

        $payload = [
            "segments" => $segments,
            "passengers" => $passengers
        ];
        try {
            $response = $this->client->post('flix-bus/create_reservations_bla', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() == 200) {
                $results = json_decode((string) $response->getBody(), true);
                return redirect()->back()->with([
                    'bla_booking' => $results,
                    'total_price_with_margin' => $request->total_price
                ]);
            }

            return redirect()->back()->withErrors(['message' => 'Failed to create reservation. Please try again.'])->withInput();
        } catch (\Exception $e) {
            Log::error("Exception during reservation: " . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'An error occurred. Please try again later.'])->withInput();
        }
    }

    public function bla_bus_confirm(Request $request)
    {
        $this->service_id = 9;
        $validator = Validator::make($request->all(), [
            "firstname.*" => "required",
            "lastname.*" => "required",
            "birthdate.*" => "required",
            "reservation_token.*" => "required",
            "reservation_id.*" => "required",
            "email" => "required",
            "phone_number" => "required",
        ]);

        if($validator->fails())
        {
            Log::warning("FlixBus validation failed",[$request->except('_token')]);
            AppHelper::logger('warning', 'FlixBus API', "Validation Error Missing Field" ,$request->except('_token'));
            return redirect('bus-v2')
                ->with('message', 'Validation Error Missing Field')
                ->with('message_type', 'warning');
        }

        $params = [
            'booking_number' => $request->booking_number,
            'booking_id' => $request->booking_id,
            'sales_channel_code' => $request->sales_channel_code,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'from_name' => $request->from_name,
            'to_name' => $request->to_name,
            'price' => $request->price,
            'currency' => $request->currency,
            'total_vat' => $request->total_vat,
            'total_price_paid' => $request->total_price_paid,
            'total_price_to_be_paid' => $request->total_price_to_be_paid,
        ];
        // Process passenger details
        $passengers = [];
        $passengerCount = count($request->passenger_id);

        for ($i = 0; $i < $passengerCount; $i++) {
            $birthdate = \DateTime::createFromFormat('d.m.Y', $request->birthdate[$i]);

            if ($birthdate) {
                $formatted_birthdate = $birthdate->format('Y-m-d');
            } else {
                // Handle the error, like logging or setting a default value
                // Example: Set it to null or log an error
                $formatted_birthdate =  $request->birthdate[$i]; // or handle error accordingly
            }
            $passengers[] = [
                'id' => $request->passenger_id[$i],
                'type' => $request->passenger_type[$i],
                'disability_type' => $request->passenger_disability_type[$i],
                'ref_id' => $request->passenger_ref_id[$i],
                'uuid' => $request->passenger_uuid[$i],
                'first_name' => $request->firstname[$i],
                'last_name' => $request->lastname[$i],
                'birthdate' => $formatted_birthdate,
                'gender' => $request->gender[$i],
                'email' => $request->email[$i],
                'phone' => $request->phone_number[$i],
                'citizenship' => $request->citizenship[$i],
                'identification_number' => $request->identification_number[$i],
                'identification_expiry_date' => $request->identification_expiry_date[$i],
                'visa_permit_type' => $request->visa_permit_type[$i],
                'identification_issuing_country' => $request->identification_issuing_country[$i],
                'identification_type' => $request->identification_type[$i],
            ];
        }

        $params['passengers'] = $passengers;

        // Process segment details
        $segments = [];
        $segmentCount = count($request->segment_id);

        for ($i = 0; $i < $segmentCount; $i++) {
            $segments[] = [
                'id' => $request->segment_id[$i],
                'departure_station' => $request->segment_departure_station[$i],
                'arrival_station' => $request->segment_arrival_station[$i],
                'service_name' => $request->segment_service_name[$i],
                'departure_time' => $request->segment_departure_time[$i],
                'arrival_time' => $request->segment_arrival_time[$i],
            ];
        }

        $params['segments'] = $segments;
        // Log the parameters for debugging purposes
        Log::info("Sending passenger details to FlixBus API", $params);
        $user_info = User::find(auth()->user()->id);
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id,auth()->user()->currency,true);
        $euro_amount = str_replace(',', '', number_format($request->input('total_price'),2));
        $user_service_commission = ServiceHelper::get_service_commission($user_info->id, 9);//service_id may change
        $current_balance = AppHelper::getBalance($user_info->id, $user_info->currency, false);
        $order_amount = ServiceHelper::calculate_commission($euro_amount, $user_service_commission);
        $user_credit_limit = AppHelper::get_credit_limit($user_info->id);
        $sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount);
        $after_order_balance = number_format((float)$current_balance - $order_amount, 2, '.', '');
        $created_at = date("Y-m-d H:i:s");
        $mobile_operator ="blabla";
        $mobile_number ="+917904721979";
        if($request->phone_number){
            $mobile_number = preg_replace('/\D/', '', $request->phone_number[0]);
        }

        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check($user_info->id, $euro_amount)) {
                //Daily Limit for this user
                //order will be failed
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                $getBalance = (\app\Library\AppHelper::sendMail($r_bal,$daily_limit,$getBalance,$blink_limit,$manager_id,auth()->user()->username));
                AppHelper::logger('warning', 'Daily Limit Exceed', $user_info->username . 'Daily limit exceed to confirm Flix bus order', $request->all());
                Log::warning('Flix Bus Daily Limit Exceed => ' . $user_info->username . ' => ' . $user_info->id);
                return redirect('bus-v2')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }

        if (ServiceHelper::parent_rule_check($user_info->parent_id, $euro_amount, 9)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', $user_info->username . ' parent does not have enough balance or credit limit to confirm Flix bus order', $request->all());
            Log::warning('Flix Bus Parent Rule Failed => ' . $user_info->username . ' => ' . $user_info->parent_id);
            return redirect('bus-v2')
                ->with('message', trans('common.parent_rule_failed'))
                ->with('message_type', 'warning');
        }

        if ($current_balance < $order_amount) {
            //check with credit limit
            if (ServiceHelper::check_with_credit_limit($order_amount, $current_balance, $user_credit_limit) == false) {
                AppHelper::logger('warning', 'Flix Bus Balance Error', $user_info->username . ' does not have enough balance or credit limit to confirm Flix Bus order', $request->all());
                return redirect('bus-v2')
                    ->with('message', trans('common.msg_order_failed_due_bal'))
                    ->with('message_type', 'warning');
            }
        }

        $transID = "TT".date("y") . strtoupper(date('M')) . date('d') . date('His').Rand(111,999);
        try {
            \DB::beginTransaction();
            $tt_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);

            // Send the POST request to the API
            $response = $this->client->request('POST', 'flix-bus/add_passenger_details_bla', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN,
                    'Content-Type' => 'application/json',
                ],
                'json' => $params, // Use 'json' to send data as JSON
            ]);
            // Check if the response is successful
            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody(), true);
                // Redirect to the ticket URL or confirmation page
                if (isset($result['data']['ticket_url'])) {

                    //Data From Api response
                    Log::info("FlixBus", [$result]);
                    $txn_ref = isset($result['data']['booking_number']) ? $result['data']['booking_number'] : $tt_txn_id;
                    $print_ticket = $result['data']['ticket_url'];
                    $ticketInstruction = $result['data']['booking_id'] ?? ($result['data']['booking_number'] ?? '');
                    $order_comment = $user_info->username . " Flix bus for " . $euro_amount . " Print Ticket Link is " . $ticketInstruction;
                    $order_desc = $order_comment;
                    $ins = $ticketInstruction;
                    $link = $result['data']['ticket_url'];
                }
            } else {
                // Handle non-200 responses
                Log::error("Failed to add passenger details", [
                    'status_code' => $response->getStatusCode(),
                    'response_body' => $response->getBody()->getContents(),
                ]);
                return redirect('bus-v2')
                    ->with('message', 'Failed to add passenger details')
                    ->with('message_type', 'warning');
            }
//        } catch (\Exception $e) {
//            // Handle exceptions
//            Log::error("Exception in bla_bus_confirm", [
//                'message' => $e->getMessage(),
//                'trace' => $e->getTraceAsString(),
//            ]);
//            return redirect('bus-v2')
//                ->with('message', 'An error occurred while processing your request')
//                ->with('message_type', 'warning');
//        }

            //make transaction
            $trans_id = ServiceHelper::sync_transaction($user_info->id, $created_at, 'debit', $order_amount, $current_balance, $after_order_balance, $order_desc);
            //Insert order
            $order_id = ServiceHelper::save_order('',$created_at, $user_info->id, 9, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$sale_margin,$order_amount,$order_amount,$order_amount,NULL,0,0);


            //insert order items
            $order_item_id = ServiceHelper::save_orders_items($order_id, $mobile_number, $euro_amount, $mobile_operator, $ins, $link, $created_at,$user_info->id);

            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find($user_info->parent_id);
            if (!empty($user_info->parent_id) && $parent_user && $parent_user->group_id != 2) {

                $parent_user_commission = ServiceHelper::get_service_commission($parent_user->id, 9);
                $parent_current_balance = AppHelper::getBalance($parent_user->id, $parent_user->currency, false);
                $parent_actual_commission = $parent_user_commission - $user_service_commission;
                $buying_price_parent = ServiceHelper::calculate_commission($euro_amount, $parent_user_commission);

                $order_amount_parent = ServiceHelper::calculate_commission($euro_amount, $parent_actual_commission);
                $parent_sale_margin = ServiceHelper::calculate_sale_margin($order_amount, $buying_price_parent);
                $parent_after_order_balance = number_format((float)$parent_current_balance - $buying_price_parent, 2, '.', '');

                //make transaction for parent
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $created_at, 'debit', $buying_price_parent, $parent_current_balance, $parent_after_order_balance, $order_desc);

                //parent order insertion
                $parent_order_id = ServiceHelper::save_order($order_desc,$created_at, $user_info->id, 9, '7', $parent_trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$parent_sale_margin,$order_amount,$buying_price_parent,$order_amount,$order_item_id,1,0);


                $app_commission = optional(AppCommission::where('service_id', 9)->first())->commission;
                $app_actual_commission = $app_commission - $parent_user_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($buying_price_parent, $buying_price_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, 9, '7', $trans_id, $txn_ref, $order_desc,$user_info->currency,$euro_amount,$app_sale_margin,$buying_price_parent,$buying_price_app,$buying_price_parent,$order_item_id,1,0);
            } else {
                //use the app commission to update order buying_price
                $app_commission = optional(AppCommission::where('service_id', 9)->first())->commission;
                $app_actual_commission = $app_commission - $user_service_commission;
                $buying_price_app = ServiceHelper::calculate_commission($euro_amount, $app_commission);
                $order_amount_app = ServiceHelper::calculate_commission($euro_amount, $app_actual_commission);
                $app_sale_margin = ServiceHelper::calculate_sale_margin($euro_amount, $order_amount_app);
                Log::info("commissions", [
                    'app commission '=> $app_commission,
                    'user service commission' => $user_service_commission,
                    'buying_price_app' => $buying_price_app,
                    'order_amount_app' => $order_amount_app,
                    'app_sale_margin' => $app_sale_margin,
                ]);
                ServiceHelper::save_order($order_desc,$created_at, $parent_user->id, 9, '7', $trans_id, $txn_ref, '',$user_info->currency,$euro_amount,$app_sale_margin, $order_amount,$buying_price_app, $order_amount,$order_item_id,1,0);

            }
            \DB::commit();
            AppHelper::logger('success', 'Flix Bus Order #' . $order_id, $order_desc);
            return redirect('transactions')
                ->with('message', 'download now')
                ->with('message_type', 'success');

        }
        catch (\Exception $e) {
            \DB::rollback();
            $exception_id = 'TTEX' . AppHelper::Numeric(5);//to know more about exception
            $exceptions = [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode()
            ];
            Log::emergency(auth()->user()->username . " Flix Bus API Exception => " . $e->getMessage());
            AppHelper::logger('warning', 'Flix Bus Exception ' . $exception_id, $e->getMessage(),$exceptions);
            return redirect('bus-v2')
                ->with('message', 'Flix Bus Exception ')
                ->with('message_type', 'warning');

        }
    }

}
