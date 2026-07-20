<?php

namespace App\Http\Controllers\Myservice\CallingCard;

use app\Library\AppHelper;
use App\Models\Country;
use App\Models\TelecomProvider;
use App\Models\TelecomProviderConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class TelecomProvidersController extends Controller
{
    private $log_title;
    function __construct()
    {
        parent::__construct();
        $this->log_title = "Telecom Providers";
    }

    function index()
    {
        $this->data['page_title'] = "Manage telecom providers";
        return view('myservice.calling-cards.telecom-providers.index',$this->data);
    }

    function fetch_data(Request $request){
        $query = TelecomProvider::join('telecom_providers_config','telecom_providers_config.id','telecom_providers.tp_config_id')->join('countries','countries.id','telecom_providers_config.country_id')
            ->select(
                'telecom_providers.id as id',
                'countries.nice_name as country_name',
                'telecom_providers.name as name',
                'telecom_providers.description as note',
                'telecom_providers.face_value as face_value',
                'telecom_providers.status as provider_status',
                'telecom_providers.created_at as created_at',
                'telecom_providers.updated_at as updated_at'

            );
        return Datatables::of($query)
            ->addColumn('status', function ($query) {
                return $query->provider_status == 1 ? "<span class='label label-success'>".trans('common.lbl_enabled')."</span>" :  "<span class='label label-danger'>".trans('common.lbl_disabled')."</span>";
            })
            ->addColumn('description', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="'.$query->note.'" data-original-title="'.$query->name.' '.AppHelper::formatAmount('EUR',$query->face_value).'" title="">'.AppHelper::doTrim_text($query->note,30,true).'</span>';
            })
            ->addColumn('action', function ($query) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$query->name.' '.AppHelper::formatAmount('EUR',$query->face_value).'\');return false;"  href="'.secure_url('telecom-provider/update/'.$query->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\''.trans('common.lbl_remove').' '.$query->name.'\',\''.trans('common.ask_remove').'\' );return false;" href="'.secure_url('telecom-provider/remove/'.$query->id).'" class="btn btn-xs btn-danger"><i class="fa fa-times-circle"></i> '.trans('common.btn_delete').'</a>';
            })
            ->addColumn('image',function ($query){
                $tp_config = TelecomProvider::find($query->id);
                $src_img = $tp_config->getMedia('telecom_providers_cards')->first();
                $img = !empty($src_img) ? optional($src_img)->getUrl('thumb') : asset('images/no_image.png');
                return $img;
            })
            ->rawColumns(['action','status','image','description'])
            ->make(true);
    }

    function edit($id='')
    {
        if(!empty($id)){
            $row = TelecomProvider::join('telecom_providers_config','telecom_providers_config.id','telecom_providers.tp_config_id')->select('telecom_providers_config.country_id','telecom_providers.id','telecom_providers.name','telecom_providers.description','telecom_providers.status','telecom_providers.face_value','telecom_providers.tp_config_id','telecom_providers.bimedia_card')->where('telecom_providers.id',$id)->first()->toArray();
        }else{
            $row = [
                'country_id' => '','id'=> '','name'=> '','description'=> '','status'=> '','face_value'=> '','tp_config_id'=> '','bimedia_card'=> ''
            ];
        }
        $this->data['row'] = $row;
        $this->data['countries'] = Country::join('telecom_countries','telecom_countries.country_id','countries.id')->select('countries.id','countries.nice_name')->get();
        $this->data['telecom_providers_config'] = TelecomProviderConfig::select('id','country_id','name')->where('status',1)->get();
        return view('myservice.calling-cards.telecom-providers.update',$this->data);
    }


    function update(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'country_id' => 'required',
            'tp_config_id' => 'required',
            'face_value' => 'required'
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
            if($request->input('id') != ''){
                TelecomProvider::where('id',$request->id)->update([
                    'tp_config_id' => $request->input('tp_config_id'),
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'face_value' => $request->input('face_value'),
                    'status' => !empty($request->input('status')) ? $request->input('status') : 0,
                    'bimedia_card' => !empty($request->input('bimedia_card')) ? $request->input('bimedia_card') : 0,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
                $tp_id = $request->input('id');
            }else{
                $tp_id = TelecomProvider::insertGetId([
                    'tp_config_id' => $request->input('tp_config_id'),
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'face_value' => $request->input('face_value'),
                    'status' => !empty($request->input('status')) ? $request->input('status') : 0,
                    'bimedia_card' => !empty($request->input('bimedia_card')) ? $request->input('bimedia_card') : 0,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => auth()->user()->id
                ]);
            }
            if($request->hasFile('image')){
                $tp_config = TelecomProvider::find($tp_id);
                $fileTmp = $request->file('image');
                $fileName = str_slug($request->name,'_').'.'.$fileTmp->getClientOriginalExtension();
                $tp_config->addMedia($request->file('image'))->usingFileName($fileName)->toMediaCollection('telecom_providers_cards');
            }
            \DB::commit();
            AppHelper::logger('success',$this->log_title,'Telecom provider information updated',$request->all());
            return redirect('telecom-providers')
                ->with('message',trans('common.msg_update_success'))
                ->with('message_type','success');
        }catch (\Exception $e){
            \DB::rollback();
            AppHelper::logger('warning',$this->log_title." Exception",$e->getMessage(),$e);
            return redirect('telecom-providers')
                ->with('message',trans('common.msg_update_error'))
                ->with('message_type','warning');
        }
    }

    function delete($id)
    {
        $telecom_provider = TelecomProvider::find($id);
        $telecom_provider->delete();
        AppHelper::logger('success',$this->log_title,"Telecom Provider for the ID ".$id.' has been removed');
        return redirect()->back()
            ->with('message',trans('common.msg_remove_success'))
            ->with('message_type','success');
    }
}
