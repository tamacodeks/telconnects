<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use App\Models\Log;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ActivityController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index(){
        $this->data = [
            'page_title' => "Manage Application activities"
        ];
        return view('app.activities.index');
    }

    function fetch_logs(Request $request){
        $query = Log::join('users','users.id','logs.user_id')
            ->select([
                'users.username',
                'logs.type as type',
                'logs.title as title',
                'logs.description as description',
                'logs.uri as uri',
                'logs.ip as ip',
                'logs.is_api as is_api',
                'logs.request_info as requested',
                'logs.created_at as date'
            ]);
        $logs = $query;
        return Datatables::of($logs)
            ->addColumn('uri', function ($logs){
                $log_title = secure_url('/');
                $log_desc = str_replace($log_title."/", " ", $logs->uri);
                return $log_desc;
            })
            ->addColumn('log_type', function ($logs){
                return "<span class='label label-".$logs->type."'>$logs->type</span>";
            })
            ->addColumn('channel', function ($logs) {
                return $logs->is_api == 1 ? "<span class='fa fa-link' data-toggle='tooltip' title='API'></span>" : "<span class='fa fa-globe' data-toggle='tooltip' title='WEB'></span>" ;
            })
            ->addColumn('request_info', function ($logs) {
                return AppHelper::_format_json($logs->requested);
            })
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('query'))) {
                    $qry = $request->input('query');
                    $query->Where(function ($q) use ($qry) {
                        $q->Where('users.username', "like", "%{$qry}%");
                        $q->orWhere('logs.title', "like", "%{$qry}%");
						$q->orWhere('logs.ip', "like", "%{$qry}%");
                    });
                }
                if (!empty($request->input('type'))) {
                    $type = $request->input('type');
                    $query->Where('type',$type);
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date').' 00:00:00';
                    $to_date = $request->input('to_date').' 23:59:59';
                    $query->whereBetween('logs.created_at',[$from_date,$to_date]);
                }
            })
            ->rawColumns(['log_type','channel','description','request_info'])
            ->make(true);
    }

    function clear()
    {
        Log::truncate();
        AppHelper::logger('success','Logs clear',"All logs were cleared");
        return redirect()->back()
            ->with('message','All logs were cleared!')
            ->with('message_type','success');
    }
}
