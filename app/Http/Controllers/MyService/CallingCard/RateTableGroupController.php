<?php

namespace App\Http\Controllers\MyService\CallingCard;

use app\Library\AppHelper;
use App\Models\CallingCard;
use App\Models\RateTable;
use App\Models\RateTableGroup;
use App\Models\UserRateTable;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class RateTableGroupController extends Controller
{
    private $log_title;
    function __construct()
    {
        parent::__construct();
        $this->log_title= "Rate Table Group";
    }

    /**
     * View
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $this->data['page_title'] = "Manage Rate Table Groups";
        return view('myservice.calling-cards.rate-table-groups.index',$this->data);
    }

    /**
     * Ajax - Fetch Rate Table Groups
     * @param Request $request
     * @return mixed
     */
    function fetch_data(Request $request)
    {
        $query = RateTableGroup::where('user_id',auth()->user()->id)
            ->select(
                'id','name','status','created_at','updated_at'
            );
        return Datatables::of($query)
            ->addColumn('users', function ($query) {
                $users_to_grab = User::join('user_rate_tables','user_rate_tables.user_id','users.id')
                    ->join('rate_table_groups','rate_table_groups.id','user_rate_tables.rate_group_id')
                    ->where('users.parent_id',auth()->user()->id)
                    ->where('user_rate_tables.rate_group_id',$query->id)
                    ->select('users.username')
                    ->get()->toArray();
                $collection = collect($users_to_grab)->flatten();
                return implode(', ',$collection->all());
            })
            ->addColumn('action', function ($query) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$query->name.'\');return false;"  href="'.secure_url('cc-price-list/groups/edit/'.$query->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\''.trans('common.lbl_remove').' '.$query->name.'\',\''.trans('common.ask_remove').'\' );return false;" href="'.secure_url('cc-price-list/groups/remove/'.$query->id).'" class="btn btn-xs btn-danger"><i class="fa fa-times-circle"></i> '.trans('common.btn_delete').'</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * View - Update Rate Table Group
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function edit($id='')
    {
        if($id !='') {
            $this->data['row'] = RateTableGroup::find($id)->toArray();
        }else{
            $this->data['row'] = AppHelper::renderColumns('rate_table_groups');
        }
        $this->data['rate_groups'] = RateTableGroup::where('user_id',auth()->user()->id)->select('id','name')->get();
        return view('myservice.calling-cards.rate-table-groups.update',$this->data);
    }

    /**
     * POST - Add or Update Rate Table Group
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function update(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(),[
            'name' => 'required'
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title." Validation",'Validation failed',$request->all());
            return redirect()
                ->back()
                ->with('message',AppHelper::create_error_bag($validator))
                ->with('message_type','warning');
        }
        $up_data =[
            'name' => $request->name,
            'user_id' => auth()->user()->id,
        ];
        try{
            $check_this_user_rtg = UserRateTable::where('user_id',auth()->user()->id)->first();
            if(!$check_this_user_rtg){
                Log::warning("Unable to find rategroup for this user, please contact your administrator!");
                AppHelper::logger('warning','RateGroup Update','System cannot find the rate group for this user in order copy default rate group!');
                return back()->with('message',trans('api.contact_admin'))->with('message_type','warning');
            }
            \DB::beginTransaction();
            if(!empty($request->id)){
                $up_data['updated_at'] = date("Y-m-d H:i:s");
                $up_data['updated_by'] = auth()->user()->id;
                RateTableGroup::where('id',$request->id)->update($up_data);
                $ins_rate_group_id = $request->id;
            }else{
                $up_data['created_at'] = date("Y-m-d H:i:s");
                $up_data['created_by'] = auth()->user()->id;
                $ins_rate_group_id = RateTableGroup::insertGetId($up_data);
            }
            //copying exist tables for this group
            if($request->rate_group_id != ''){
                if(!empty($request->id)){
                    //delete the rate tables under updated rate group
                    RateTable::where('user_id',auth()->user()->id)
                        ->where('rate_group_id',$ins_rate_group_id)
                        ->delete();
                }
                //lets copy the rate tables of src rate_table_group
                $src_rate_group_id = $request->rate_group_id;
                $getCopied = RateTable::where('rate_tables.user_id',auth()->user()->id)
                    ->where('rate_tables.rate_group_id',$src_rate_group_id)
                    ->get();
                foreach ($getCopied as $item) {
                    $rt_check = RateTable::join('calling_cards','calling_cards.id','rate_tables.cc_id')
                        ->where('rate_tables.id',$item->id)
                        ->where('calling_cards.id',$item->cc_id)
                        ->first();
                    if(isset($rt_check)){
                        $rt = RateTable::find($item->id);
                        $newRT = $rt->replicate();
                        $newRT->rate_group_id = $ins_rate_group_id;
                        $newRT->cc_id = $item->cc_id;
                        $newRT->created_at = date("Y-m-d H:i:s");
                        $newRT->created_by = auth()->user()->id;
                        $newRT->save();
                    }
                }
                AppHelper::logger('success',$this->log_title." RTG Copy",'Rate Table group ID '.$ins_rate_group_id." was copied from rate table group ID ".$src_rate_group_id);
            }
            else{
                if(!empty($request->id)){
                    //delete the rate tables under updated rate group
                    RateTable::where('user_id',auth()->user()->id)
                        ->where('rate_group_id',$ins_rate_group_id)
                        ->delete();
                }
                //lets copy the rate tables of src rate_table_group
                $src_rate_group_id = $check_this_user_rtg->rate_group_id;
                $getCopied = RateTable::where('rate_tables.user_id',auth()->user()->parent_id)
                    ->where('rate_tables.rate_group_id',$src_rate_group_id)
                    ->get();
                foreach ($getCopied as $item) {
                    $rt_check = RateTable::join('calling_cards','calling_cards.id','rate_tables.cc_id')
                        ->where('rate_tables.id',$item->id)
                        ->where('calling_cards.id',$item->cc_id)
                        ->first();
                    if(isset($rt_check)){
                        $rt = RateTable::find($item->id);
                        $newRT = $rt->replicate();
                        $newRT->user_id = auth()->user()->id;
                        $newRT->rate_group_id = $ins_rate_group_id;
                        $newRT->cc_id = $item->cc_id;
                        $newRT->buying_price = $item->sale_price;
                        $newRT->sale_price = "0.00";
                        $newRT->sale_margin = "0.00";
                        $newRT->created_at = date("Y-m-d H:i:s");
                        $newRT->created_by = auth()->user()->id;
                        $newRT->save();
                    }
                }
                AppHelper::logger('success',$this->log_title." RTG Copy",'Rate Table group ID '.$ins_rate_group_id." was copied from rate table group ID ".$src_rate_group_id);
            }
            AppHelper::logger('success',$this->log_title." Update",'updated successfully',$request->all());
            \DB::commit();
            return redirect()->back()->with('message',trans('common.msg_update_success'))->with('message_type','success');
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title." Exception", $e->getMessage(),$request->all());
            return redirect()->back()->with('message',trans('common.msg_update_error'))->with('message_type','warning');
        }
    }

    /**
     * GET - Remove Rate table group and rate tables having $id(rate_group_id)
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    function delete($id)
    {
        $rg = RateTableGroup::find($id);
        try{
            \DB::beginTransaction();
            if($rg){
                RateTable::where('rate_group_id',$id)->where('user_id',auth()->user()->id)->delete();
                $rg->delete();
            }
            \DB::commit();
            AppHelper::logger('success',$this->log_title." Delete","ID $id deleted successfully!");
            return redirect()->back()
                ->with('message',trans('common.msg_remove_success'))
                ->with('message_type','success');
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title." Exception", $e->getMessage());
            return redirect()->back()->with('message',trans('common.msg_remove_remove'))->with('message_type','warning');
        }
    }
}
