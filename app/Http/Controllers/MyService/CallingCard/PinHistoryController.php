<?php

namespace App\Http\Controllers\MyService\CallingCard;

use App\Events\NotifyUser;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use App\Models\CallingCardPin;
use App\Models\Notification;
use App\Models\PinHistory;
use App\Models\PinPrintRequest;
use App\Models\TelecomProvider;
use App\Models\Ticket;
use App\Models\TicketConversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Validator;

class PinHistoryController extends Controller
{
    private $log_title;
    private $decipher;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Pin History";
        $this->decipher = new SecurityHelper();
    }

    function index()
    {
        $this->data['page_data'] = "Used Pins History";
        $this->data['providers'] = TelecomProvider::select('id', 'name', 'face_value')->get();
        return view('myservice.calling-cards.history.index', $this->data);
    }

    function fetchPinHistories(Request $request)
    {
        $query = PinHistory::join('calling_cards', 'calling_cards.id', 'pin_histories.cc_id')
            ->join('telecom_providers', 'telecom_providers.id', 'calling_cards.telecom_provider_id')
            ->where('used_by', auth()->user()->id)
            ->select([
                'pin_histories.id',
                'pin_histories.name',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date',
                'calling_cards.telecom_provider_id',
                'calling_cards.description as card_desc'
            ]);
        if (empty($request->input('from_date')) && empty($request->input('to_date'))) {
            $today_date = date("Y-m-d");
            switch (DEFAULT_RECORD_METHOD) {
                case 1:
                    $query->whereBetween('pin_histories.date', [$today_date . " 00:00:00", $today_date . " 23:59:59"]);
                    break;
                case 2:
                    $query->whereMonth('pin_histories.date', date('m'));
                    break;
                case 3:
                    $query->whereBetween('pin_histories.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
            }
        } else {
            $from_date = $request->input('from_date') . ' 00:00:00';
            $to_date = $request->input('to_date') . ' 23:59:59';
            $query->whereBetween('pin_histories.date', [$from_date, $to_date]);
        }
        if (!empty($request->input('telecom_provider_id'))) {
            $query->whereIn('calling_cards.telecom_provider_id', $request->input('telecom_provider_id'));
        }
        return Datatables::of($query)
            ->addColumn('status', function ($query) {
                $pin_request = PinPrintRequest::where('pin_id', $query->id)->where('from_user', auth()->user()->id)
                    ->where('to_user', auth()->user()->parent_id)->first();
                return isset($pin_request->status) ? $pin_request->status == "1" ? "<span class='label label-success'>" . trans('myservice.approved') . "</span>" : "<span class='label label-info'>" . trans('myservice.requested') . "</span>" : "<span class='label label-default'>" . trans('myservice.none') . "</span>";
            })
            ->addColumn('description', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="' . $query->card_desc . '" data-original-title="' . $query->name . '" title="">' . AppHelper::doTrim_text($query->card_desc, 30, true) . '</span>';
            })
            ->addColumn('action', function ($query) {
                $html = "";
                $pin_request = PinPrintRequest::where('pin_id', $query->id)->where('from_user', auth()->user()->id)
                    ->where('to_user', auth()->user()->parent_id)->first();
                $enc_pin = $this->decipher->encrypt($query->id);
                if (auth()->user()->pin_print_again == 1) {
                    if ($pin_request) {
                        if ($pin_request->status == 1) {
                            $html .= '<button onClick="print_pin(\'' . $enc_pin . '\')" class="btn btn-success btn-xs"><i class="fa fa-print"></i>&nbsp;' . trans('myservice.btn_print_pin') . '</button>&nbsp;';
                        }
                    } else {
                        $html .= '<button onClick="print_pin_again_req(' . $query->id . ')" class="btn btn-primary btn-xs"><i class="fa fa-paper-plane"></i>&nbsp;' . trans('myservice.send_requests') . '</button>&nbsp;';
                    }
                }
                $url_enquiry = secure_url('cc-pin-history/contact/' . $enc_pin);
                $title = "Enquiry for " . $query->name;
                $html .= '<button onClick="AppModal(\'' . $url_enquiry . '\',\'' . $title . '\');return false;" class="btn btn-primary btn-xs"><i class="fa fa-comments"></i>&nbsp;' . trans('myservice.enquire_now') . '</button>';
                return $html;
            })
            ->rawColumns(['action', 'status','description'])
            ->make(true);
    }

    function createPinPrintRequest(Request $request)
    {
        $checkRequest = PinPrintRequest::where('from_user', auth()->user()->id)->where('pin_id', $request->pin_id)->first();
        if ($checkRequest) {
            $data = (object)array(
                'data' => array(
                    'status' => "403",
                    'message' => trans('service.requests_exists')
                )
            );
            $headers = array(
                'Content-Type: application/json'
            );
            return response()->json($data, 200, $headers);
        }
        PinPrintRequest::insert([
            'from_user' => $request->user()->id,
            'to_user' => $request->user()->parent_id,
            'pin_id' => $request->pin_id,
            'requested_at' => date('Y-m-d H:i:s')
        ]);
        $data = (object)array(
            'data' => array(
                'status' => "200",
                'message' => trans('service.requests_send')
            )
        );
        $headers = array(
            'Content-Type: application/json'
        );
        return response()->json($data, 200, $headers);
    }


    function viewPinRequests()
    {
        $this->data['page_title'] = "View Pin Print Requests";
        return view('myservice.calling-cards.history.print_requests', $this->data);
    }

    function fetchPinPrintRequests(Request $request)
    {
//        $query = CallingCardPin::join('pin_print_requests','pin_print_requests.pin_id','calling_card_pins.id')
//            ->join('users','users.id','pin_print_requests.from_user')
//            ->where('pin_print_requests.to_user',auth()->user()->id)
//            ->select([
//                'calling_card_pins.id',
//                'calling_card_pins.name',
//                'calling_card_pins.pin',
//                'calling_card_pins.serial',
//                'calling_card_pins.updated_at',
//                'calling_card_pins.public_key',
//                'pin_print_requests.id as request_id',
//                'pin_print_requests.status',
//                'users.username',
//            ]);
        $query = PinHistory::join('pin_print_requests', 'pin_print_requests.pin_id', 'pin_histories.id')
            ->join('users', 'users.id', 'pin_print_requests.from_user')
            ->where('pin_print_requests.to_user', auth()->user()->id)
            ->select([
                'pin_histories.id',
                'pin_histories.name',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date as updated_at',
                'pin_print_requests.id as request_id',
                'pin_print_requests.status',
                'users.username',
            ]);
        return Datatables::of($query)
            ->addColumn('status', function ($query) {
                return isset($query->status) ? $query->status == "1" ? "<span class='label label-success'>" . trans('myservice.approved') . "</span>" : "<span class='label label-info'>" . trans('myservice.requested') . "</span>" : "<span class='label label-default'>" . trans('myservice.none') . "</span>";
            })
            ->addColumn('action', function ($query) {
                $html = "";
                if ($query->status == 1) {
                    $html .= '<button onClick="process_pin_requests(' . $query->request_id . ',' . $query->id . ',\'false\')" class="btn btn-danger btn-xs"><i class="fa fa-ban"></i>&nbsp;' . trans('common.btn_cancel') . '</button>&nbsp;';
                } else {
                    $html .= '<button onClick="process_pin_requests(' . $query->request_id . ',' . $query->id . ',\'true\')" class="btn btn-primary btn-xs"><i class="fa fa-check-circle"></i>&nbsp;' . trans('myservice.approve') . '</button>&nbsp;';
                }
                return $html;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }


    function processPinPrintRequests(Request $request)
    {
//        dd($request->all());
        if ($request->approve == "true") {
            $checkRequest = PinPrintRequest::where('to_user', $request->user()->id)->where("status", 1)->where('pin_id', $request->pin_id)->where('id', $request->request_id)->first();
            if ($checkRequest) {
                $data = (object)array(
                    'data' => array(
                        'status' => "403",
                        'message' => "Pin Request already approved!"
                    )
                );
                $headers = array(
                    'Content-Type: application/json'
                );
                return response()->json($data, 200, $headers);
            }
            PinPrintRequest::where('pin_id', $request->pin_id)->where('to_user', $request->user()->id)->where('id', $request->request_id)->update([
                'status' => 1,
                'approved_at' => date("Y-m-d H:i:s")
            ]);
            $data = (object)array(
                'data' => array(
                    'status' => "200",
                    'message' => "Pin request approved!"
                )
            );
            $headers = array(
                'Content-Type: application/json'
            );
            return response()->json($data, 200, $headers);
        } elseif ($request->approve == "false") {
            $checkRequest = PinPrintRequest::where('to_user', $request->user()->id)->where('id', $request->request_id)->where("status", 0)->where('pin_id', $request->pin_id)->first();
            if ($checkRequest) {
                $data = (object)array(
                    'data' => array(
                        'status' => "403",
                        'message' => "Pin Request already disapproved!"
                    )
                );
                $headers = array(
                    'Content-Type: application/json'
                );
                return response()->json($data, 200, $headers);
            }
            PinPrintRequest::where('pin_id', $request->pin_id)->where('id', $request->request_id)->where('to_user', $request->user()->id)->update([
                'status' => 0,
                'approved_at' => null
            ]);
            $data = (object)array(
                'data' => array(
                    'status' => "200",
                    'message' => "Pin request disapproved!"
                )
            );
            $headers = array(
                'Content-Type: application/json'
            );
            return response()->json($data, 200, $headers);
        } else {
            $data = (object)array(
                'data' => array(
                    'status' => "403",
                    'message' => "Unable to process!"
                )
            );
            $headers = array(
                'Content-Type: application/json'
            );
            return response()->json($data, 200, $headers);
        }
    }

    function printPinAgain(Request $request)
    {
        $dec_id = $this->decipher->decrypt($request->pin_id);
        $checkRequest = PinPrintRequest::where('from_user', $request->user()->id)->where("status", 1)->where('pin_id', $dec_id)->first();
        if (collect($checkRequest)->count() <= 0) {
            AppHelper::logger('warning', $this->log_title, 'Unable to print pin id ' . $dec_id, $request->all());
            return redirect('cc-pin-history')
                ->with('message', trans('myservice.unable_to_print'))
                ->with('message_type', 'warning');
        }
        //get pin info
        $pin_info = PinHistory::join('calling_cards', 'calling_cards.id', 'pin_histories.cc_id')
            ->where('pin_histories.id', $dec_id)
            ->where('pin_histories.used_by', auth()->user()->id)
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'pin_histories.id as ccp_id',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date as updated_at',
            ])->first();
//        $pin_info = CallingCardPin::join('calling_cards','calling_cards.id','calling_card_pins.cc_id')
//            ->where('calling_card_pins.id',$dec_id)
//            ->where('calling_card_pins.is_used',1)
//            ->where('calling_card_pins.used_by',auth()->user()->id)
//            ->select([
//                'calling_cards.id as cc_id',
//                'calling_cards.name',
//                'calling_cards.description',
//                'calling_cards.validity',
//                'calling_cards.access_number',
//                'calling_cards.comment_1',
//                'calling_cards.comment_2',
//                'calling_card_pins.id as ccp_id',
//                'calling_card_pins.pin',
//                'calling_card_pins.serial',
//                'calling_card_pins.public_key',
//                'calling_card_pins.updated_at',
//            ])->first();
        $this->data['page_title'] = "Print " . $pin_info->name;
        $this->data['card'] = $pin_info;
        $this->data['card_name'] = $pin_info->name;
        $this->data['card_id'] = $pin_info->id;
        $this->data['provider'] = TelecomProvider::join('calling_cards', 'calling_cards.telecom_provider_id', 'telecom_providers.id')->where('calling_cards.id', $pin_info->cc_id)->select("telecom_providers.*")->first();
        return view("myservice.calling-cards.history.print_page", $this->data);
    }


    function getEnquiryNow(Request $request, $pin_id)
    {
        $dec_id = $this->decipher->decrypt($pin_id);
        //get pin info
        $pin_info = PinHistory::join('calling_cards', 'calling_cards.id', 'pin_histories.cc_id')
            ->where('pin_histories.id', $dec_id)
            ->where('pin_histories.used_by', auth()->user()->id)
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'pin_histories.id as ccp_id',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date as updated_at',
            ])->first();
        $this->data['card'] = $pin_info;
        $this->data['card_name'] = $pin_info->name;
        $this->data['card_id'] = $pin_info->id;
        $this->data['pin_id'] = $pin_id;
        $this->data['provider'] = TelecomProvider::join('calling_cards', 'calling_cards.telecom_provider_id', 'telecom_providers.id')->where('calling_cards.id', $pin_info->cc_id)->select("telecom_providers.*")->first();
        $this->data['contactActionUrl'] = secure_url('cc-pin-history/contact');
        return view('myservice.calling-cards.history.enquiry', $this->data);
    }

    function sendEnquiry(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'pin_id' => "required",
            'type' => "required",
            'message' => "required",
        ]);
        if ($validator->fails()) {
            AppHelper::logger('warning', $this->log_title, "Validation failed", $request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()
                ->back()
                ->with('message', $html)
                ->with('message_type', 'warning');
        }
        //step 1 decrypt the pin
        $dec_pin_id = $this->decipher->decrypt($request->pin_id);
        //step 2 check for ticket existence
        $ticket_exist = Ticket::where('from_user', auth()->user()->id)->where('to_user', auth()->user()->parent_id)
            ->where('pin_id', $dec_pin_id)->first();
        if ($ticket_exist) {
            //redirect the user to conversation
            $enc_ticket_id = $this->decipher->encrypt($ticket_exist->id);
            AppHelper::logger('info', $this->log_title, "Ticket ID " . $ticket_exist->id . " already exists");
            return redirect('ticket/conversation/' . $enc_ticket_id);
        }
        //create the ticket now
        try {
            \DB::beginTransaction();
            if ($request->fwdStatus == 'true') {
                $fwd_id = optional(Ticket::where('to_user', auth()->user()->id)
                    ->where('pin_id', $dec_pin_id)->first())->id;
            } else {
                $fwd_id = null;
            }
            $ticket_id = Ticket::insertGetId([
                'from_user' => auth()->user()->id,
                'to_user' => auth()->user()->parent_id,
                'type' => $request->type,
                'status' => 0,
                'fwd_id' => $fwd_id,
                'pin_id' => $dec_pin_id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            //create conversation
            TicketConversation::insert([
                'ticket_id' => $ticket_id,
                'user_id' => auth()->user()->id,
                'message' => $request->message,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            AppHelper::logger('success', $this->log_title, "Ticket created successfully for user " . auth()->user()->username);
            $noty_id = Notification::insertGetId([
                'date' => date('Y-m-d H:i:s'),
                'user_id' => auth()->user()->parent_id,
                'type' => "enquiry",
                'title' => auth()->user()->username . " " . trans('common.send_enquiry'),
                'message' => $request->message,
                'url' => secure_url('ticket/conversation/' . $this->decipher->encrypt($ticket_id)),
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => auth()->user()->id
            ]);
            $notification = Notification::find($noty_id);
            event(new NotifyUser($notification));
            \DB::commit();
            return redirect()
                ->back()
                ->with('message', trans('myservice.ticket_created'))
                ->with('message_type', 'success');
        } catch (\Exception $exception) {
            \DB::rollBack();
            AppHelper::logger('warning', $this->log_title, "Exception while create ticket => " . $exception->getMessage());
            Log::warning("$this->log_title Exception => " . $exception->getMessage());
            return redirect()
                ->back()
                ->with('message', trans('myservice.ticket_exception'))
                ->with('message_type', 'warning');
        }
    }
}
