<?php

namespace App\Http\Controllers\Chat;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InboxController extends Controller
{
    private $log_title;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Inbox";
    }

    function index()
    {
        $this->data['page_title'] = "Inbox";
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
        $this->data['users'] = $query->select('id','username')->get();
        $this->data['active_user'] = '';
        return view('app.inbox.index',$this->data);
    }
}
