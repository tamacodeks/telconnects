<?php

namespace App\Http\Controllers\MyService\CallingCard;

use App\Events\CallingCardPinUpload;
use app\Library\AppHelper;
use app\Library\DBHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\Models\CallingCard;
use App\Models\CallingCardAccess;
use App\Models\CallingCardPin;
use App\Models\CallingCardUpload;
use App\Models\Country;
use App\Models\RateTable;
use App\Models\RateTableGroup;
use App\Models\TelecomProvider;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class CallingCardController extends Controller
{
    private $log_title;
    private $service_id;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Calling Cards";
        $this->service_id = 7;
    }

    private function loadCallingCardPinRows($path)
    {
        $oldErrorReporting = error_reporting();
        error_reporting($oldErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

        try {
            require_once base_path('vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php');
            $reader = \PHPExcel_IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($path)->getActiveSheet();
            $rows = $sheet->toArray(null, true, false, true);
        } finally {
            error_reporting($oldErrorReporting);
        }

        $headers = [];
        $pins = [];
        foreach ($rows as $row) {
            if (empty(array_filter($row, function ($value) {
                return strlen((string) $value);
            }))) {
                continue;
            }
            if (empty($headers)) {
                foreach ($row as $column => $value) {
                    $headers[$column] = trim(strtolower(str_replace(' ', '_', (string) $value)));
                }
                continue;
            }
            $pin = new \stdClass();
            foreach ($headers as $column => $header) {
                if ($header !== '') {
                    $pin->{$header} = isset($row[$column]) ? trim((string) $row[$column]) : '';
                }
            }
            if (!empty($pin->face_value) && !empty($pin->serial) && !empty($pin->pin)) {
                $pins[] = $pin;
            }
        }

        return $pins;
    }

    function index()
    {
        $this->data['page_title'] = "Manage Calling Cards";
        return view('myservice.calling-cards.index', $this->data);
    }

    function fetch_data(Request $request)
    {
        $query = CallingCard::join('telecom_providers', 'telecom_providers.id', 'calling_cards.telecom_provider_id')
            ->join('telecom_providers_config', 'telecom_providers_config.id', 'telecom_providers.tp_config_id')
            ->join('countries', 'countries.id', 'telecom_providers_config.country_id')
            ->select(
                'countries.nice_name as country_name',
                'calling_cards.id as id',
                'calling_cards.telecom_provider_id as telecom_provider_id',
                'calling_cards.name as name',
                'calling_cards.description as desc_note',
                'calling_cards.access_number as access_note',
                'calling_cards.face_value as face_value',
                'calling_cards.buying_price as buying_price',
                'calling_cards.buying_price1 as buying_price1',
                'calling_cards.status as card_status',
                'calling_cards.created_at as created_at',
                'calling_cards.updated_at as updated_at',
                'calling_cards.aleda_product_code as aleda',
                'calling_cards.bimedia_product_code as bimedia'
            );
        return Datatables::of($query)
            ->addColumn('status', function ($query) {
                return $query->card_status == 1 ? "<span class='label label-success'>" . trans('common.lbl_enabled') . "</span>" : "<span class='label label-danger'>" . trans('common.lbl_disabled') . "</span>";
            })
            ->addColumn('description', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="' . $query->desc_note . '" data-original-title="' . $query->name . '" title="">' . AppHelper::doTrim_text($query->desc_note, 30, true) . '</span>';
            })
            ->addColumn('access_number', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="' . $query->access_note . '" data-original-title="' . $query->name . '" title="">' . AppHelper::doTrim_text($query->access_note, 30, true) . '</span>';
            })
            ->addColumn('action', function ($query) {
                return '<a data-toggle="tooltip" data-placement="top" title="' . trans('common.menu_manage_resellers') . '" onclick="AppModal(this.href,\'' . trans('common.menu_manage_resellers') . ' - ' . $query->name . '\');return false;"  href="' . secure_url('cc/manage-resellers/' . $query->id) . '" class="btn btn-xs btn-info"><i class="fa fa-users"></i> </a>&nbsp;&nbsp;<a data-toggle="tooltip" data-placement="top" title="' . trans('common.lbl_edit') . '" href="' . secure_url('cc/update/' . $query->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a data-toggle="tooltip" data-placement="top" title="' . trans('myservice.btn_upload_pins') . '" onclick="AppModal(this.href,\'' . trans('myservice.btn_upload_pins') . ' - ' . $query->name . '\');return false;"  href="' . secure_url('cc/upload-pins/' . $query->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-upload"></i></a>';
            })
            ->addColumn('image', function ($query) {
                $cc = TelecomProvider::find($query->telecom_provider_id);
                $src_img = $cc->getMedia('telecom_providers_cards')->first();
                $img = !empty($src_img) ? optional($src_img)->getUrl('thumb') : asset('images/no_image.png');
                return $img;
            })
            ->rawColumns(['action', 'status', 'image', 'description', 'access_number'])
            ->make(true);
    }

    /**
     * View - Update reseller Access
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function manage_access($id)
    {
        $this->data['row'] = CallingCard::join('calling_cards_access', 'calling_cards_access.cc_id', 'calling_cards.id')
            ->join('users', 'users.id', 'calling_cards_access.user_id')
            ->select('calling_cards.id', 'users.id as user_id', 'users.username')
            ->where('calling_cards.id', $id)
            ->get();
        $this->data['cc_id'] = $id;
        $this->data['users'] = User::where('parent_id', auth()->user()->id)->select('id', 'username')->get();
        return view('myservice.calling-cards.update-resellers', $this->data);
    }

    /**
     * POST - update retailer acces for the calling card
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function update_retailer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            AppHelper::logger('warning', $this->log_title . " Validate Fail", 'Unable to update retailer access', $validator);
            return redirect()->back()
                ->with('message', AppHelper::create_error_bag($validator))
                ->with('message_type', 'warning');
        }
        CallingCardAccess::join('users', 'users.id', 'calling_cards_access.user_id')->where('calling_cards_access.cc_id', $request->id)->where('users.parent_id', auth()->user()->id)->delete();
        if (!empty($request->retailers)) {
            foreach ($request->retailers as $retailer) {
                $data_to_update = [
                    'cc_id' => $request->id,
                    'user_id' => $retailer,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => auth()->user()->id
                ];
                CallingCardAccess::insert($data_to_update);
                $childs = User::where('parent_id',$retailer)->select('id')->get();
                if($childs){
                    foreach ($childs as $child) {
                        CallingCardAccess::where('calling_cards_access.cc_id', $request->id)->where('calling_cards_access.user_id', $child->id)->delete();
                        $data_to_update['user_id'] = $child->id;
                        CallingCardAccess::insert($data_to_update);
                    }
                }
            }
        }
        AppHelper::logger('success', $this->log_title . " Retailer Update", "Retailer access for the service " . $request->id . ' has been updated', $request->all());
        return redirect()->back()
            ->with('message', trans('common.msg_update_success'))
            ->with('message_type', 'success');
    }

    function edit($id = '')
    {
        if ($id != '') {
            $this->data['row'] = CallingCard::join('telecom_providers', 'telecom_providers.id', 'calling_cards.telecom_provider_id')
                ->join('telecom_providers_config', 'telecom_providers_config.id', 'telecom_providers.tp_config_id')
                ->join('countries', 'countries.id', 'telecom_providers_config.country_id')
                ->select(
                    'countries.id as country_id',
                    'telecom_providers.id as telecom_provider_id',
                    'calling_cards.id',
                    'calling_cards.name',
                    'calling_cards.description',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.buying_price',
                    'calling_cards.buying_price1',
                    'calling_cards.face_value',
                    'calling_cards.status',
                    'calling_cards.activate',
                    'calling_cards.aleda_product_code',
                    'calling_cards.bimedia_product_code'
                )
                ->where('calling_cards.id', $id)
                ->first()->toArray();
        } else {
            $this->data['row'] = [
                'id' => "",
                'country_id' => "",
                'telecom_provider_id' => "",
                'name' => "",
                'description' => "",
                'comment_1' => "",
                'comment_2' => "",
                'validity' => "",
                'access_number' => "",
                'buying_price' => "",
                'buying_price1' => "",
                'face_value' => "",
                'status' => "",
                'activate' => "",
                'aleda_product_code' => "",
                'bimedia_product_code' => ""
            ];
        }
        $this->data['page_title'] = "Update Calling Card " . $this->data['row']['name'];
        $this->data['telecom_providers'] = TelecomProvider::join('telecom_providers_config', 'telecom_providers_config.id', 'telecom_providers.tp_config_id')->join('countries', 'countries.id', 'telecom_providers_config.country_id')->select('telecom_providers.id', 'telecom_providers.name', 'telecom_providers.description', 'telecom_providers.face_value', 'countries.id as country_id', 'countries.nice_name')->where('telecom_providers.status', 1)->get();
        $this->data['countries'] = Country::join('telecom_countries', 'telecom_countries.country_id', 'countries.id')->select('countries.id', 'countries.nice_name')->get();
        $test = Storage::disk('public')->get('catalogue/catalogue.xml');
        $ob= simplexml_load_string($test);
        $json  = json_encode($ob);
        $configData = json_decode($json, true);
        $this->data['product_codes'] =$configData['product'];

        $bimedia_file = Storage::disk('public')->get('catalogue/bimedia_catalogue.xml');
        $convert_xml= simplexml_load_string($bimedia_file);
        $json_xml  = json_encode($convert_xml);
        $config_data = json_decode($json_xml, true);
        $this->data['bimedia_code'] = $config_data['products'];

        return view('myservice.calling-cards.update', $this->data);
    }

    function update(Request $request)
    {
//        dd($request->all());
        $rules = [
            'country_id' => 'required',
            'name' => 'required',
            'face_value' => 'required',
            'buying_price' => 'required',
        ];
        if ($request->id == '') {
            $rules['telecom_provider_id'] = 'required|unique:calling_cards,telecom_provider_id';
            $rules['excelFile'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            AppHelper::logger('warning', $this->log_title . " Validation", "Validation failed", $validator);
            $failedRules = $validator->failed();
            if (isset($failedRules['telecom_provider_id']['Unique'])) {
                $html = trans('myservice.err_card_exists');
            } else {
                $html = AppHelper::create_error_bag($validator);
            }
            return redirect()->back()
                ->with('message', $html)
                ->with('message_type', 'warning');
        }
        try {
            \DB::beginTransaction();
            $up_data = [
                'telecom_provider_id' => $request->telecom_provider_id,
                'service_id' => $this->service_id,
                'name' => $request->name,
                'description' => $request->description,
                'comment_1' => $request->comment_1,
                'comment_2' => $request->comment_2,
                'validity' => $request->validity,
                'access_number' => $request->access_number,
                'face_value' => $request->face_value,
                'buying_price' => $request->buying_price,
                'buying_price1' => $request->buying_price1,
                'status' => !empty($request->status) ? $request->status : 0,
                'activate' => !empty($request->activate) ? $request->activate : 0,
                'aleda_product_code' => !empty($request->aleda_product_code) ? $request->aleda_product_code : "",
                'bimedia_product_code' => !empty($request->bimedia_product_code) ? $request->bimedia_product_code : ""
            ];
            if ($request->id != '') {
                //update the calling info
                $up_data['updated_at'] = date('Y-m-d H:i:s');
                $up_data['updated_by'] = auth()->user()->id;
                CallingCard::where('id', $request->input('id'))->update($up_data);
                $cc_id = $request->id;
                AppHelper::logger('success', $this->log_title . "Updated Successfully", "Calling Card updated Successfully by " . auth()->user()->username . ' has been updated', $up_data);
            }
            else {
                //insert new calling info
                $up_data['created_at'] = date('Y-m-d H:i:s');
                $up_data['created_by'] = auth()->user()->id;
                $cc_id = CallingCard::insertGetId($up_data);
                //check for excel pin upload
                if ($request->hasFile('excelFile')) {
                    \Config::set('excel.csv.delimiter', ';'); // un comment only on production
                    $path = $request->file('excelFile')->getRealPath();
                    $pins = $this->loadCallingCardPinRows($path);
                    $total_pins = 0;
                    $up_trans_id = ServiceHelper::genUpTransID(7);
                    foreach ($pins as $res) {
                        $now = date("Y-m-d");
                        if (number_format($res->face_value, 2) != $request->input('face_value')) {
                            throw new \Exception(trans('myservice.face_value_not_match'));
                        } elseif (ServiceHelper::checkPinExists($request->input('telecom_provider_id'), $res->serial, $res->face_value)) {
                            throw new \Exception(trans('myservice.serial_exists'));
                        } else {
                            $public_key = str_random(15);
                            $secret_key = SecurityHelper::decipherEncryption($public_key . "CJJbW7SaznW7cZhVzwLo");
                            $enc_pin = SecurityHelper::tamaCipher(str_replace("*", "", $res->pin), "e", $secret_key);
                            $row_data = array(
                                'cc_id' => $cc_id,
                                'name' => $request->name,
                                'value' => $res->face_value,
                                'pin' => $enc_pin,
                                'serial' => str_replace("*", "", $res->serial),
                                'buying_price' => $request->input('buying_price'),//added 17-1-2018
                                'face_value' => $request->input('face_value'),//added 17-1-2018
                                'public_key' => $public_key,
                                'up_trans_id' => $up_trans_id,
                                'usage_note' => '-',
                                'is_used' => 0,
                                'is_blocked' => 0,
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => auth()->user()->id
                            );
                            CallingCardPin::insert($row_data);
                            $total_pins++;
                        }
                    }
                    //lets insert into calling_card_uploads
                    $cc_upload_id = CallingCardUpload::insertGetId([
                        'cc_id' => $cc_id,
                        'date' => date('Y-m-d H:i:s'),
                        'buying_price' => $request->input('buying_price'),
                        'no_of_pins' => $total_pins,
                        'total_amount' => $request->input('buying_price') * $total_pins,
                        'up_trans_id' => $up_trans_id,
                        'uploaded_by' => auth()->user()->id,
                        'uploaded_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth()->user()->id
                    ]);
                    //lets make myservice->calling_cards_transaction
                    $oldCCServiceBalance = AppHelper::getMyServiceBalance(auth()->user()->id, auth()->user()->currency, false);
                    $newCCBalance = $oldCCServiceBalance + $request->input('buying_price') * $total_pins;
                    $trans_desc = "New Calling Card pins($total_pins) " . $request->name . " added with the buying price of " . $request->buying_price." in total $total_pins x $request->buying_price = ".$total_pins * $request->buying_price;
                    ServiceHelper::sync_myservice_transaction(auth()->user()->id, $cc_id, date('Y-m-d H:i:s'), 'credit', $request->input('buying_price') * $total_pins, $oldCCServiceBalance, $newCCBalance, $trans_desc);
                    AppHelper::logger('success', $this->log_title . " New Pins Uploaded", $trans_desc);
                    //add this calling_card into all rate tables using rate_groups
                    $rate_table_groups = RateTableGroup::join('users','users.id','rate_table_groups.user_id')->select('rate_table_groups.id','rate_table_groups.user_id')->get();
                    foreach ($rate_table_groups as $rate_table_group) {
                        $rate_table_data = array(
                            'user_id' => $rate_table_group->user_id,
                            'rate_group_id' => $rate_table_group->id,
                            'cc_id' => $cc_id,
                            'buying_price' => $request->buying_price,
                            'sale_price' => "0.00",
                            'sale_margin' => "0.00",
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => auth()->user()->id
                        );
                        RateTable::insert($rate_table_data);
                    }
                    $cc_upload = CallingCardUpload::find($cc_upload_id);
                    event(new CallingCardPinUpload($cc_upload));
                }
            }
            \DB::commit();
            return redirect('cc/manage')->with('message', trans('common.msg_update_success'))
                ->with('message_type', 'success');
        } catch (\Exception $e) {
            \DB::rollBack();
            AppHelper::logger('warning', $this->log_title , " Exception" . $e->getMessage(), $e);
            return redirect('cc/manage')->with('message', trans('common.msg_update_error'))
                ->with('message_type', 'warning');
        }
    }

    function upload_pins(Request $request,$id)
    {
        $this->data['row'] = CallingCard::find($id)->toArray();
        //check any pins for this card still available
        $cc_pins = CallingCardPin::where('cc_id',$id)->where('is_used',0)->count();
        $this->data['show_bp'] = $cc_pins > 0 ? "NO" : "YES";
//        dd($cc_pins);
        return view('myservice.calling-cards.upload-pins',$this->data);
    }

    function pin_upload(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'id' => 'required|exists:calling_cards,id',
            'face_value' => 'required',
            'buying_price' => 'required',
            'excelFile' => 'required',
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title,'Validation failed',$validator);
            return redirect()
                ->back()
                ->with('message',AppHelper::create_error_bag($validator))
                ->with('message_type','warning');
        }
        try{
            \DB::beginTransaction();
            $cc_data = CallingCard::find($request->input('id'));
            if(!empty($request->face_value)){
                $face_value = $request->face_value;
            }else{
                $face_value = $cc_data->face_value;
            }
            if(!empty($request->buying_price)){
                $buying_price = $request->buying_price;
            }else{
                $buying_price = $cc_data->buying_price;
            }
            $card_name = $cc_data->name;
            $telecom_provider_id = $cc_data->telecom_provider_id;
            $up_data = [
                'comment_1' => $request->comment_1,
                'comment_2' => $request->comment_2,
                'validity' => $request->validity,
                'access_number' => $request->access_number,
                'face_value' => $face_value,
                'buying_price' => $buying_price
            ];
            //update the calling info
            $up_data['updated_at'] = date('Y-m-d H:i:s');
            $up_data['updated_by'] = auth()->user()->id;
            CallingCard::where('id', $request->input('id'))->update($up_data);
            $cc_id = $request->input('id');
            if ($request->hasFile('excelFile')) {
                \Config::set('excel.csv.delimiter', ';'); // un comment only on production
                $path = $request->file('excelFile')->getRealPath();
                $pins = $this->loadCallingCardPinRows($path);
                $total_pins = 0;
                $up_trans_id = ServiceHelper::genUpTransID(7);
                foreach ($pins as $res) {
                    $now = date("Y-m-d");
                    if (number_format($res->face_value, 2) != $face_value) {
                        throw new \Exception(trans('myservice.face_value_not_match'));
                    } elseif (ServiceHelper::checkPinExists($telecom_provider_id, $res->serial, $res->face_value)) {
                        throw new \Exception(trans('myservice.serial_exists'));
                    } else {
                        $public_key = str_random(15);
                        $secret_key = SecurityHelper::decipherEncryption($public_key . "CJJbW7SaznW7cZhVzwLo");
                        $enc_pin = SecurityHelper::tamaCipher(str_replace("*", "", $res->pin), "e", $secret_key);
                        $row_data = array(
                            'cc_id' => $cc_id,
                            'name' => $card_name,
                            'value' => $res->face_value,
                            'pin' => $enc_pin,
                            'serial' => str_replace("*", "", $res->serial),
                            'buying_price' => $buying_price,
                            'face_value' => $face_value,
                            'public_key' => $public_key,
                            'up_trans_id' => $up_trans_id,
                            'usage_note' => '-',
                            'is_used' => 0,
                            'is_blocked' => 0,
                            'created_at' => date("Y-m-d H:i:s"),
                            'created_by' => auth()->user()->id
                        );
                        CallingCardPin::insert($row_data);
                        $total_pins++;
                    }
                }
                //lets insert into calling_card_uploads
                $cc_upload_id = CallingCardUpload::insertGetId([
                    'cc_id' => $cc_id,
                    'date' => date('Y-m-d H:i:s'),
                    'buying_price' => $buying_price,
                    'no_of_pins' => $total_pins,
                    'total_amount' => $buying_price * $total_pins,
                    'up_trans_id' => $up_trans_id,
                    'uploaded_by' => auth()->user()->id,
                    'uploaded_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => auth()->user()->id
                ]);
                //lets make myservice->calling_cards_transaction
                $oldCCServiceBalance = AppHelper::getMyServiceBalance(auth()->user()->id, auth()->user()->currency, false);
                $newCCBalance = $oldCCServiceBalance + $buying_price * $total_pins;
                $trans_desc = "New Calling Card pins($total_pins) " . $request->name . " added with the buying price of " . $buying_price." in total $total_pins x $buying_price = ".$total_pins * $buying_price;
                ServiceHelper::sync_myservice_transaction(auth()->user()->id, $cc_id, date('Y-m-d H:i:s'), 'credit', $buying_price * $total_pins, $oldCCServiceBalance, $newCCBalance, $trans_desc);
                AppHelper::logger('success', $this->log_title . " New Pins Uploaded", $trans_desc);
                //add this calling_card into all rate tables using rate_groups
                $rate_table_groups = RateTableGroup::join('users','users.id','rate_table_groups.user_id')->select('rate_table_groups.id','rate_table_groups.user_id')->get();
                foreach ($rate_table_groups as $rate_table_group) {
                    $check_rt = RateTable::where('user_id',$rate_table_group->user_id)
                        ->where('rate_group_id',$rate_table_group->id)
                        ->where('cc_id',$cc_id)
                        ->first();
                    if(!$check_rt){
                        $buying_price_tmp = auth()->user()->id == $rate_table_group->user_id ? $request->input('buying_price') : "0.00";
                        RateTable::insert(
                            [
                                'user_id' => $rate_table_group->user_id,
                                'rate_group_id' => $rate_table_group->id,
                                'cc_id' => $cc_id,
                                'buying_price' => $buying_price_tmp,
                                'sale_price' => "0.00",
                                'sale_margin' => "0.00",
                                'updated_at' => date('Y-m-d H:i:s'),
                                'updated_by' => auth()->user()->id
                            ]
                        );
                    }
                }
                $cc_upload = CallingCardUpload::find($cc_upload_id);
                event(new CallingCardPinUpload($cc_upload));
            }
            \DB::commit();
            return redirect()->back()
                ->with('message',trans('service.ms_pin_update_success_msg'))
                ->with('message_type','success');
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception => ". $e->getMessage(),$e);
            return redirect()->back()
                ->with('message',trans('service.ms_pin_update_error_msg'))
                ->with('message_type','success');
        }
    }

    function callWebHookForMasterRetailer(Request $request,$user_id)
    {
        if(!$request->ajax())
        {
            return response()->json([
                'status' => 405,
                'message' => "Method not allowed!"
            ],405);
        }
        $user = User::join('user_rate_tables','user_rate_tables.user_id','users.id')
            ->where('users.id',$user_id)
            ->select('users.group_id','users.parent_id','user_rate_tables.rate_group_id')
            ->first();
        if(!$user)
        {
            return response()->json([
                'status' => 200,
                'message' => "Unable to sync rate tables!"
            ],200);
        }
        $rate_table_parent = RateTable::where('user_id', $user->parent_id)
            ->where('rate_group_id', $user->rate_group_id)
            ->get();
        if ($user->group_id == 5){
            //web hook the change in rate tables
            if (isset($rate_table_parent)) {
                foreach ($rate_table_parent as $item) {
                    event(new \App\Events\RateTablePush($item,$user_id));
                }
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Synchorinizing price lists started"
        ],200);
    }

}
