<?php

namespace App\Http\Controllers\MyService\CallingCard;

use App\Events\NotifyUser;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use App\Models\CallingCardPin;
use App\Models\Notification;
use App\Models\PinHistory;
use App\Models\TelecomProvider;
use App\Models\Ticket;
use App\Models\TicketConversation;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Validator;

class TicketController extends Controller
{
    private $log_title;
    private $decipher;

    function __construct()
    {
        $this->log_title = "Ticket";
        $this->decipher = new SecurityHelper();
        parent::__construct();
    }


    function index()
    {
        $this->data['page_title'] = "View My Tickets";
        return view('myservice.calling-cards.tickets.index',$this->data);
    }

    function indexV2()
    {
        $this->data['page_title'] = trans('myservice.my_tickets');
        return view('v2.app.tickets.index', $this->data);
    }

    private function myTicketsQuery(Request $request)
    {
        $query = Ticket::join('pin_histories','pin_histories.id','tickets.pin_id')
            ->where('tickets.from_user',auth()->user()->id)
            ->select([
                'pin_histories.name',
                'pin_histories.serial',
                'pin_histories.pin',
                'tickets.id as ticket_id',
                'tickets.to_user',
                'tickets.type',
                'tickets.status',
                'tickets.created_at',
            ]);

        if ($request->filled('type')) {
            if ($request->type === 'open') {
                $query->where('tickets.status', 0);
            } elseif ($request->type === 'closed') {
                $query->where('tickets.status', 1);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('tickets.created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('tickets.created_at', '<=', $request->to_date);
        }

        if ($request->filled('query')) {
            $search = trim((string) $request->query);
            $query->where(function ($q) use ($search) {
                $q->where('pin_histories.name', 'like', "%{$search}%")
                    ->orWhere('pin_histories.serial', 'like', "%{$search}%")
                    ->orWhere('pin_histories.pin', 'like', "%{$search}%")
                    ->orWhere('tickets.type', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    function fetchMyTickets(Request $request)
    {
        $query = $this->myTicketsQuery($request);

        return Datatables::of($query)
            ->addColumn('to_user', function ($query) {
                return  optional(User::find($query->to_user))->username;
            })
            ->addColumn('issue_type', function ($query) {
                return ucwords(str_replace('_', ' ', $query->type)); ;
            })
            ->addColumn('status', function ($query) {
                return  $query->status == "1" ? "<span class='label label-success'>".trans('myservice.closed')."</span>" : "<span class='label label-warning'>".trans('myservice.open')."</span>";
            })
            ->addColumn('action', function ($query) {
                $enc_ticket_id = $this->decipher->encrypt($query->ticket_id);
                return  '<a href="'.secure_url('ticket/conversation/'.$enc_ticket_id).'" class="btn btn-primary btn-xs"><i class="fa fa-comments"></i>&nbsp;'.trans('myservice.view_conv').'</a>';
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    function fetchMyTicketsV2(Request $request)
    {
        $query = $this->myTicketsQuery($request);

        return Datatables::of($query)
            ->addColumn('to_user', function ($query) {
                return  optional(User::find($query->to_user))->username;
            })
            ->addColumn('issue_type', function ($query) {
                return ucwords(str_replace('_', ' ', $query->type)); ;
            })
            ->addColumn('status', function ($query) {
                return $query->status == "1"
                    ? '<span class="v2-history-status v2-history-status-success">'.e(trans('myservice.closed')).'</span>'
                    : '<span class="v2-history-status v2-history-status-info">'.e(trans('myservice.open')).'</span>';
            })
            ->addColumn('action', function ($query) {
                $enc_ticket_id = $this->decipher->encrypt($query->ticket_id);
                return '<div class="v2-history-action-cell"><a href="'.e(url('ticket/conversation/'.$enc_ticket_id)).'" class="v2-history-action-btn v2-history-action-soft"><i class="fa fa-comments" aria-hidden="true"></i><span>'.e(trans('myservice.view_conv')).'</span></a></div>';
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    function showConversation(Request $request,$ticket_id)
    {
        $dec_ticket_id = $this->decipher->decrypt($ticket_id);
        $ticket_info = Ticket::join('pin_histories','pin_histories.id','tickets.pin_id')//
        ->where(function ($q)  {
            $q->where('tickets.from_user',auth()->user()->id)
                ->orWhere('tickets.to_user',auth()->user()->id);
        })
            ->where('tickets.id',$dec_ticket_id)
            ->select([
                'pin_histories.name',
//                'calling_card_pins.face_value',
                'pin_histories.serial',
                'pin_histories.pin',
//                'calling_card_pins.public_key',
                'tickets.id as ticket_id',
                'tickets.from_user',
                'tickets.pin_id',
                'tickets.to_user',
                'tickets.type',
                'tickets.status',
                'tickets.created_at',
            ])->first();
        if(!$ticket_info){
            AppHelper::logger('warning',$this->log_title,"Ticket ID ".$dec_ticket_id." not found!");
            return redirect()
                ->back()
                ->with('message',trans('myservice.ticket_not_found'))
                ->with('message_type','warning');
        }
        $ticket_info->pin = AppHelper::decryptPin($ticket_info->pin,$ticket_info->public_key);
        $this->data['ticket'] = $ticket_info;
        $this->data['ticket_id'] = $ticket_id;
        $this->data['ticket_conversations'] = TicketConversation::join('users','users.id','ticket_conversations.user_id')
            ->where('ticket_id',$dec_ticket_id)->select([
                'ticket_conversations.ticket_id',
                'users.username',
                'ticket_conversations.message',
                'ticket_conversations.created_at'
            ])->get();
//        $this->data['pin_id'] = $this->decipher->encrypt($ticket_info->pin_id);
        //mark as read if notification read=true and notification=id
        if(!empty($request->read) && $request->read == 'true' && !empty($request->notification))
        {
            Notification::where('user_id',auth()->user()->id)->where('id',$request->notification)
                ->where('type','enquiry')->update([
                    'is_read' => 1,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
        }
        $this->data['from_user'] = optional(\App\User::find($ticket_info->from_user))->username;
        $this->data['to_user'] = optional(\App\User::find($ticket_info->to_user))->username;
        $this->data['page_title'] = trans('common.lbl_view')." $ticket_info->name";
        return view('myservice.calling-cards.tickets.conversations',$this->data);
    }


    function manageTickets()
    {
        $this->data['page_title'] = "Manage Tickets";
        return view('myservice.calling-cards.tickets.manage',$this->data);
    }

    function fetchIncomingTickets(Request $request)
    {
        $query = Ticket::join('pin_histories','pin_histories.id','tickets.pin_id')
            ->where('tickets.to_user',auth()->user()->id)
            ->select([
                'pin_histories.name',
//                'calling_card_pins.face_value',
                'pin_histories.serial',
                'pin_histories.pin',
                'tickets.id as ticket_id',
                'tickets.from_user',
                'tickets.type',
                'tickets.status',
                'tickets.created_at',
            ]);
        return Datatables::of($query)
            ->addColumn('from_user', function ($query) {
                return  optional(User::find($query->from_user))->username;
            })
            ->addColumn('issue_type', function ($query) {
                return ucwords(str_replace('_', ' ', $query->type)); ;
            })
            ->addColumn('status', function ($query) {
                return  $query->status == "1" ? "<span class='label label-success'>".trans('myservice.closed')."</span>" : "<span class='label label-warning'>".trans('myservice.open')."</span>";
            })
            ->addColumn('action', function ($query) {
                $enc_ticket_id = $this->decipher->encrypt($query->ticket_id);
                return  '<a href="'.secure_url('ticket/conversation/'.$enc_ticket_id).'" class="btn btn-primary btn-xs"><i class="fa fa-comments"></i>&nbsp;'.trans('myservice.view_conv').'</a>';
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    function saveComment(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'ticket_id' => "required|exists:tickets,id",
            'message' => "required",
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title,"Validation failed",$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()
                ->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        //create the ticket now
        try{
            \DB::beginTransaction();
            //create conversation
            TicketConversation::insert([
                'ticket_id' => $request->ticket_id,
                'user_id' => auth()->user()->id,
                'message' => $request->message,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            AppHelper::logger('success',$this->log_title,"Comment added for $request->ticket_id by ".auth()->user()->username);
            $ticket_info = Ticket::find($request->ticket_id);
            if($ticket_info->from_user == auth()->user()->id){
                $message_to_sent = $ticket_info->to_user;
            }elseif ($ticket_info->to_user == auth()->user()->id){
                $message_to_sent = $ticket_info->from_user;
            }else{
                $message_to_sent = 0;
            }
            $notify_id = Notification::insertGetId([
                'date' => date('Y-m-d H:i:s'),
                'user_id' => $message_to_sent,
                'type' => "enquiry",
                'title' => auth()->user()->username." ".trans('common.send_enquiry'),
                'message' => $request->message,
                'url' => secure_url('ticket/conversation/'.$this->decipher->encrypt($request->ticket_id)),
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => auth()->user()->id
            ]);
            $notification = Notification::find($notify_id);
            event(new NotifyUser($notification));
            \DB::commit();
            return redirect()
                ->back();
        }catch (\Exception $exception)
        {
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception while adding comment to the ticket $request->ticket_id=> ".$exception->getMessage());
            Log::warning("$this->log_title Exception => ".$exception->getMessage());
            return redirect()
                ->back()
                ->with('message',trans('myservice.ticket_exception'))
                ->with('message_type','warning');
        }
    }


    function forwardTicket($ticket_id)
    {
        $ticket_id= $this->decipher->decrypt($ticket_id);
        $ticket = Ticket::find($ticket_id);
        $dec_id = $ticket->pin_id;
        //get pin info
        $pin_info = PinHistory::join('calling_cards','calling_cards.id','pin_histories.cc_id')
            ->where('pin_histories.id',$dec_id)
            ->where('pin_histories.used_by',$ticket->from_user)
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
//                'calling_card_pins.face_value',
            ])->first();
//        $pin_info->pin = AppHelper::decryptPin($pin_info->pin,$pin_info->public_key);
        $this->data['card'] = $pin_info;
        $this->data['card_name'] = $pin_info->name;
        $this->data['card_id'] = $pin_info->id;
        $this->data['pin_id'] = $this->decipher->encrypt($dec_id);
        $this->data['ticket_fwd'] = "true";
        $this->data['provider'] = TelecomProvider::join('calling_cards','calling_cards.telecom_provider_id','telecom_providers.id')->where('calling_cards.id',$pin_info->cc_id)->select("telecom_providers.*")->first();
        return view('myservice.calling-cards.history.enquiry',$this->data);
    }


    function closeTicket(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'ticket_id' => "required"
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title,"Validation failed",$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()
                ->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $check_ticket = Ticket::where('id',$request->ticket_id)->where('status',1)->first();
        if($check_ticket){
            AppHelper::logger('warning',$this->log_title,"Ticket ID $request->ticket_id already closed",$request->all());
            return redirect()
                ->back()
                ->with('message',"Ticket already closed")
                ->with('message_type','warning');
        }
        //mark the ticket as closed
        Ticket::where('id',$request->ticket_id)->update([
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id
        ]);
        return redirect('tickets/manage')->with('message','Ticket was closed successfully')
            ->with('message_type','success');
    }

}
