<?php

namespace App\Http\Controllers\Myservice\CallingCard;

use app\Library\AppHelper;
use App\Models\Country;
use App\Models\SeriveProvider;
use App\Models\TelecomProviderConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class SeriveProvidersController extends Controller
{
    private $log_title;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Service Providers";
    }
    function index()
    {
        $this->data['page_title'] = "Manage service providers";
        return view('myservice.calling-cards.routing.index', $this->data);
    }

    function fetch_data(Request $request){
        $query = SeriveProvider::select(
            'id',
            'primary as primary',
            'secondary as secondary',
            'created_at as created_at',
            'updated_at as updated_at'
        );
        return Datatables::of($query)
            ->addColumn('action', function ($query) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$query->name.' '.AppHelper::formatAmount('EUR',$query->face_value).'\');return false;"  href="'.secure_url('service_provider/update/'.$query->id).'" class="btn btn-xs btn-primary" value='.$query->id.'><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    function edit($id)
    {
        $row = SeriveProvider::select('id','primary','secondary')->where('id',$id)->first();
        $this->data['row'] = $row;
        return view('myservice.calling-cards.routing.update', $this->data);
    }
    function update(Request $request)
    {
//        dd($request->all());exit;
        $validator = Validator::make($request->all(),[
            'service_provider' => 'required',
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title." Validation","Validation failed",$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()
                ->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        try{
            \DB::beginTransaction();
            if($request->input('service_provider') == 'Aleda'){
                SeriveProvider::where('id',$request->id)->update([
                    'primary' => 'Aleda',
                    'secondary' => 'Bimedia',
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
            }
            else{
                SeriveProvider::where('id',$request->id)->update([
                    'primary' => 'Bimedia',
                    'secondary' => 'Aleda',
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
            }
            \DB::commit();
            AppHelper::logger('success',$this->log_title,'Serivce provider information updated',$request->all());
            return redirect('service_provider')
                ->with('message',trans('common.msg_update_success'))
                ->with('message_type','success');
        }catch (\Exception $e){
            \DB::rollback();
            AppHelper::logger('warning',$this->log_title." Exception",$e->getMessage(),$e);
            return redirect('service_provider')
                ->with('message',trans('common.msg_update_error'))
                ->with('message_type','warning');
        }
    }
}
