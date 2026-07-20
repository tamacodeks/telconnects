<?php

namespace App\Http\Controllers\ServiceConfig;

use app\Library\AppHelper;
use App\Models\Country;
use App\Models\TelecomCountry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Validator;

class TelecomCountriesController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * View Index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $this->data['page_title'] = "Manage Telecom Countries";
        return view('service-config.telecom-countries.index',$this->data);
    }

    /**
     * Ajax Datatables source
     * @param Request $request
     * @return mixed
     */
    function fetch_data(Request $request)
    {
        $query = TelecomCountry::join('countries','countries.id','telecom_countries.country_id')->select([
            'telecom_countries.id as id','countries.nice_name as name','telecom_countries.created_at as created_at','telecom_countries.updated_at as updated_at','telecom_countries.status as country_status'
        ]);
        $telecom_countries = $query;
        return Datatables::of($telecom_countries)
            ->addColumn('status', function ($telecom_countries) {
                return $telecom_countries->country_status == 1 ? "<span class='label label-success'>".trans('common.lbl_enabled')."</span>" :  "<span class='label label-danger'>".trans('common.lbl_disabled')."</span>";
            })
            ->addColumn('action', function ($telecom_countries) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$telecom_countries->name.'\');return false;"  href="'.secure_url('telecom-country/update/'.$telecom_countries->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\''.trans('common.lbl_remove').' '.$telecom_countries->name.'\',\''.trans('common.ask_remove').'\' );return false;" href="'.secure_url('telecom-country/remove/'.$telecom_countries->id).'" class="btn btn-xs btn-danger"><i class="fa fa-times-circle"></i> '.trans('common.btn_delete').'</a>';
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    /**
     * View Update
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function edit($id='')
    {
        if(!empty($id)){
            $row = TelecomCountry::find($id)->toArray();
        }else{
            $row = AppHelper::renderColumns('telecom_countries');
        }
        $this->data['row'] = $row;
        $this->data['countries'] = Country::select('id','nice_name')->get();
        return view('service-config.telecom-countries.update',$this->data);
    }

    /**
     * Delete
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    function delete($id)
    {
        $telecom_country = TelecomCountry::find($id);
        $telecom_country->delete();
        AppHelper::logger('success','Telecom Countries',"Configuration for the ID ".$id.' has been removed');
        return redirect()->back()
            ->with('message',trans('common.msg_remove_success'))
            ->with('message_type','success');
    }

    /**
     * POST Update
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'country_id' => 'required'
        ]);
        if($validator->fails()){
            AppHelper::logger('warning','Telecom Countries','Validation failed',$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        if($request->id != ''){
            //update
            TelecomCountry::where('id',$request->id)
                ->update([
                    'country_id' => $request->country_id,
                    'status' => !empty($request->status) ? 1 : 0,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
        }else{
            //insert
            TelecomCountry::insert([
                'country_id' => $request->country_id,
                'status' => !empty($request->status) ? 1 : 0,
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => auth()->user()->id
            ]);
        }
        AppHelper::logger('success','Telecom Countries','updated successfully',$request->all());
        return redirect('telecom-countries')->with('message',trans('common.msg_update_success'))
            ->with('message_type','success');
    }

}
