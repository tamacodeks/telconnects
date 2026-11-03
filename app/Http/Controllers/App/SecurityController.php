<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use App;
use App\Models\Firewall;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class SecurityController extends Controller
{
    private $app_name;
    function __construct()
    {
        parent::__construct();
        $this->app_name =config('app.name');
    }

    //blacklisted listed
    function blacklisted()
    {
        $page_data = [
            'page_title' => "Blacklisted /Whitelisted Service",
        ];
        return view('app.securities.blacklist',$page_data);
    }

    //blacklist fetch
    function fetch_blacklist(Request $request)
    {
        $query = Firewall::select([
                'id',
                'ip_address',
                'whitelisted',
                'created_at',
            ]);
        $services = $query;
        return Datatables::of($services)
            ->addColumn('status', function ($services) {
                return $services->whitelisted == 1 ? "<span class='label label-success'>Whitelisted</span>" :  "<span class='label label-danger'>Blacklisted</span>";
            })
            ->addColumn('action', function ($services) {
                return '<a onclick="AppModal(this.href,\'Whitelisted/Blacklisted '.$services->ip_address.'\');return false;"  href="'.url('blacklist/update/'.$services->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;';
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }
    //edit blacklist
    function blacklist_edit($id=''){
        if(!empty($id)){
            $row = Firewall::find($id)->toArray();

        }else {
            $row = [
                'id' => '',
                'ip_address' => '',
                'whitelisted' => ''
            ];
        }
        $this->data['row'] = $row;
        return view('app.securities.blacklist_update',$this->data);
    }
    //update or add blacklist
    function update(Request $request){

//        $google2fa = app('pragmarx.google2fa');
//        $valid = $google2fa->verifyKey(auth()->user()->secret, $request->totp);
//        if(!$valid){
//            return response()->json([
//                'success' => false,
//                'message' => 'Incorrect TOPT'
//            ]);
//        }
        $user_data = [
            'ip_address' => $request->ip_address,
            'whitelisted' => !empty($request->whitelisted) ? 1 : 0,
        ];
        if ($request->id != '') {
            $user_data['updated_at'] = date('Y-m-d H:i:s');
            Firewall::where('id', $request->id)->
            where('ip_address', $request->ip_address)
                ->update($user_data);
            return response()->json([
                'success' => true,
                'message' => 'Successfully Updated'
            ]);
        }else{
            $user_data['created_at'] = date('Y-m-d H:i:s');
            Firewall::insertGetId($user_data);
            return response()->json([
                'success' => true,
                'message' => 'Successfully Added'
            ]);
        }
    }
}
