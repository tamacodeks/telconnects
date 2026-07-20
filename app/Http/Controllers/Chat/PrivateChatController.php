<?php

namespace App\Http\Controllers\Chat;

use App\Events\NotifyUser;
use App\Models\Notification;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\Receiver;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class PrivateChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get(ChatRoom $chatroom)
    {
        return $chatroom->messages;
    }

    public function index($receiverId,Request $request)
    {
        $receiver = User::find($receiverId);
        $senderUserId = auth()->user()->id;
        $roomMembers = [$receiverId, $senderUserId];
        sort($roomMembers);
        $roomMembers = implode($roomMembers, ',');
        $chatRoom = ChatRoom::where('user_ids', $roomMembers)->first();
        if(is_null($chatRoom)) {
            $chatRoom = new ChatRoom;
            $chatRoom->room_type = 'private';
            $chatRoom->user_ids = $roomMembers;
            $chatRoom->save();
        }
        $query = User::where('status',1);
        if(auth()->user()->group_id == 2){
            $query->where('parent_id',auth()->user()->id);
        }elseif(auth()->user()->group_id == 3){
            $query->where('parent_id',auth()->user()->id)
                ->OrWhere('id',auth()->user()->parent_id);
        }elseif(auth()->user()->group_id == 4){
            $query->where('id',auth()->user()->parent_id);
        }else{
            dd('access denied');
        }
        //mark as read if notification read=true and notification=id
        if(!empty($request->read) && $request->read == 'true' && !empty($request->notification))
        {
            Notification::where('user_id',auth()->user()->id)
                ->where('id',$request->notification)
                ->where('type','message')->update([
                    'is_read' => 1,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
        }
        $this->data['users'] = $query->select('id','username')->get();
        $this->data['active_user'] = $receiverId;
        $this->data['chatRoom'] = $chatRoom;
        $this->data['receiver'] = $receiver;
        return view('app.inbox.chat', $this->data);
    }

    public function store(ChatRoom $chatroom)
    {
        $senderId = auth()->user()->id;
        $roomMembers = collect(explode(',', $chatroom->user_ids));
        $roomMembers->forget($roomMembers->search($senderId));
        $receiverId = $roomMembers->first();

        $message = new Message;
        $message->chat_room_id = $chatroom->id;
        $message->sender_id = $senderId;
        $message->message = request('message');
        $message->save();

        $receiver = new Receiver;
        $receiver->message_id = $message->id;
        $receiver->receiver_id = $receiverId;

        $sender_info = User::find($senderId);
        $noty_id = Notification::insertGetId([
            'date' => date('Y-m-d H:i:s'),
            'user_id' => $receiverId,
            'type' => "message",
            'title' => optional($sender_info)->username." ".trans('common.send_a_message'),
            'message' => request('message'),
            'url' => route('private.chat.index', $senderId),
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => 85
        ]);
        $notification = Notification::find($noty_id);
        event(new NotifyUser($notification));

        if($receiver->save()) {
            $message = Message::with('sender')->find($message->id);
            broadcast(new PrivateMessageEvent($message))->toOthers();
            return $message;
        } else {
            return 'Something went wrong!!';
        }
    }
}
