<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use app\Library\DBHelper;
use App\Models\AppCommission;
use App\Models\Service;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Validator;
class ServiceCommissionsController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * View Manage App Commissions
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $this->data['page_title'] = "Manage App Service Commissions";
        return view('app.service-commissions.index',$this->data);
    }

    /**
     * Ajax datatable
     * @param Request $request
     * @return mixed
     */
    function fetch_app_service_commissions(Request $request)
    {
        $query = AppCommission::join('services','services.id','app_commissions.service_id')
            ->select([
                'app_commissions.id as id',
                'services.name as name',
                'app_commissions.prev_com as prev_com',
                'app_commissions.commission as commission',
                'app_commissions.user_def_commission as default_commission',
                'app_commissions.mgr_def_com',
                'app_commissions.retailer_def_com',
                'app_commissions.updated_at as updated_at',
                'app_commissions.created_by as updated_by',
            ]);
        $services = $query;
        return Datatables::of($services)
            ->addColumn('action', function ($services) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$services->name.'\');return false;"  href="'.secure_url('service-commission/update/'.$services->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\''.trans('common.lbl_remove').' '.$services->name.'\',\''.trans('common.ask_remove').'\' );return false;" href="'.secure_url('service-commission/remove/'.$services->id).'" class="btn btn-xs btn-danger"><i class="fa fa-times-circle"></i> '.trans('common.btn_delete').'</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    function edit($id=''){
        if(!empty($id)){
            $row = AppCommission::find($id)->toArray();
        }else{
            $row = AppHelper::renderColumns('app_commissions');
        }
        $this->data['row'] = $row;
        $this->data['services'] = Service::select('id','name')->get();
        return view('app.service-commissions.update',$this->data);
    }

    function update(Request $request){
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'service_id' => "required",
            'commission' => "required",
        ]);
        if($validator->fails()){
            AppHelper::logger('warning','App Service Commission','Validation failed',$request->all);
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message_type','warning')
                ->with('message',$html);
        }
        //fetch all users and change the commission
        $users = User::whereIn('group_id',[3,4])->select('id','group_id')->get();
        $collections = collect($users);
        $commission = 0;
        if($request->id != ''){
            //just update it
            $old_com = AppCommission::find($request->id);
            $prev_comm = $old_com->commission;
            AppCommission::where('id',$request->id)->update([
                'service_id' => $request->service_id,
                'prev_com' => $prev_comm,
                'commission' => $request->commission,
                'mgr_def_com' => $request->mgr_def_com,
                'retailer_def_com' => $request->retailer_def_com,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            if($collections->count() > 0){
                foreach ($users as $user) {
                    //fetch the default commission for this user group
                    $get_app_def_commission = AppCommission::where('service_id',$request->service_id)->first();
                    if($user->group_id == 3){
                        $commission = optional($get_app_def_commission)->mgr_def_com;
                    }elseif ($user->group_id == 4){
                        $commission = optional($get_app_def_commission)->retailer_def_com;
                    }
                    DBHelper::update_user_commission($user->id, $request->service_id, $commission);
                }
            }
        }else{
            //check commission for the service already exists
            $check = AppCommission::where('service_id',$request->service_id)->first();
            if($check){
                AppHelper::logger('warning',"App Service Commission",'App Service Commission with service already exists!',$request->all());
                return redirect()->back()
                    ->with('message_type','warning')
                    ->with('message',trans('service.commission_exists'));
            }
            AppCommission::insert([
                'service_id' => $request->service_id,
                'commission' => $request->commission,
                'mgr_def_com' => $request->mgr_def_com,
                'retailer_def_com' => $request->retailer_def_com,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            if($collections->count() > 0){
                foreach ($users as $user) {
                    //fetch the default commission for this user group
                    $get_app_def_commission = AppCommission::where('service_id',$request->service_id)->first();
                    if($user->group_id == 3){
                        $commission = optional($get_app_def_commission)->mgr_def_com;
                    }elseif ($user->group_id == 4){
                        $commission = optional($get_app_def_commission)->retailer_def_com;
                    }
                    DBHelper::update_user_commission($user->id, $request->service_id, $commission);
                }
            }
        }
        //remove cache for app commissions
        $accessCacheKey = md5(vsprintf("%s,%s", [
            "appcommission",
            $request->service_id
        ]));
        \Cache::forget($accessCacheKey);
        AppHelper::logger('success','App Service Commission',"Service Commission updated",$request->all());
        return redirect()->back()->with('message_type','success')
            ->with('message',trans('common.msg_update_success'));
    }


    function delete($id){
        $service = AppCommission::find($id);
        $service->delete();
        AppHelper::logger('success',"App Service Commission","ID $id has been deleted",[]);
        return redirect()->back()->with('message_type','success')
            ->with('message',trans('common.msg_remove_success'));
    }
}
