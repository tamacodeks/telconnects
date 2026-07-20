<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use app\Library\ServiceHelper;
use App\Models\CallingCardPin;
use App\Models\CallingCardUpload;
use App\Models\TelecomProvider;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class CallingCardUploadController extends Controller
{
    private $log_title;

    function __construct()
    {
        parent::__construct();
        $this->log_title = "Reverse CC Transaction";
    }

    function index()
    {
        $this->data['page_title'] = "Manage CallingCard Pin Uploads and Transactions";
        $this->data['telecom_providers'] = TelecomProvider::select('id', 'name', 'face_value')->get();
        return view('app.cc-uploads.index', $this->data);
    }

    function fetch_data(Request $request)
    {
        $query = CallingCardUpload::join('calling_cards', 'calling_cards.id', 'calling_card_uploads.cc_id')
            ->select(
                'calling_card_uploads.id',
                'calling_card_uploads.date',
                'calling_card_uploads.buying_price',
                'calling_card_uploads.no_of_pins',
                'calling_card_uploads.total_amount',
                'calling_card_uploads.uploaded_by',
                'calling_card_uploads.rollback_status',
                'calling_card_uploads.rollback_at',
                'calling_card_uploads.rollback_by',
                'calling_card_uploads.rollback_note',
                'calling_cards.name',
                'calling_cards.telecom_provider_id'
            );
        return Datatables::of($query)
            ->addColumn('uploaded_by', function ($query) {
                return optional(User::find($query->uploaded_by))->username;
            })
            ->addColumn('rollback_by', function ($query) {
                return optional(User::find($query->rollback_by))->username;
            })
            ->addColumn('status', function ($query) {
                return $query->rollback_status == 1 ? "<span class='label label-success'>" . trans('myservice.rollback_yes') . "</span>" : "<span class='label label-primary'>" . trans('myservice.rollback_no') . "</span>";
            })
            ->addColumn('action', function ($query) {
                if ($query->rollback_status == 1) {
                    return "-";
                }
                return '<a onclick="AppModal(this.href,\'' . trans('common.rollback_trans') . ' - ' . $query->name . '\');return false;"  href="' . url('cc/reverse-transaction/rollback/' . $query->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-undo-alt"></i> ' . trans('common.rollback_trans') . '</a>';
            })
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('provider_id'))) {
                    $query->where('calling_cards.telecom_provider_id', $request->input('provider_id'));
                }
                if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
                    $from_date = $request->input('from_date') . ' 00:00:00';
                    $to_date = $request->input('to_date') . ' 23:59:59';
                    $query->whereBetween('calling_card_uploads.date', [$from_date, $to_date]);
                }
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }


    function rollback($id)
    {
        $this->data['row'] = CallingCardUpload::find($id)->toArray();
        return view('app.cc-uploads.update', $this->data);
    }


    function reverse_trans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:calling_card_uploads,id',
            'cc_id' => 'required|exists:calling_cards,id',
            'rollback_note' => 'required'
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title,"Validation failed",$validator);
            return redirect()->back()
                ->with('message',AppHelper::create_error_bag($validator))
                ->with('message_type','warning');
        }
        try{
            $cc_upload_data = CallingCardUpload::find($request->id);
            \DB::beginTransaction();
            //first need to remove pins from calling_card_pins using cc_id and up_trans_id
            CallingCardPin::where('cc_id',$request->cc_id)->where('up_trans_id',$cc_upload_data->up_trans_id)->delete();
            //now debit the amount from calling_card_transactions
            $oldCCServiceBalance = AppHelper::getMyServiceBalance($cc_upload_data->uploaded_by, 'EUR', false);
            $newCCBalance = $oldCCServiceBalance - $cc_upload_data->total_amount;
            $trans_desc = $request->rollback_note;
            ServiceHelper::sync_myservice_transaction($cc_upload_data->uploaded_by, $request->cc_id, date('Y-m-d H:i:s'), 'debit',  $cc_upload_data->total_amount, $oldCCServiceBalance, $newCCBalance, $trans_desc);
            //mark this rollback_status=1
            CallingCardUpload::where('id',$request->id)->update([
                'rollback_status' => 1,
                'rollback_at' => date("Y-m-d H:i:s"),
                'rollback_by' => auth()->user()->id,
                'rollback_note' => $trans_desc,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success',$this->log_title,"Rollback success",$request->all());
            return redirect()->back()
                ->with('message',trans('myservice.rollback_success'))
                ->with('message_type','warning');
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception => ".$e->getMessage(),$validator);
            return redirect()->back()
                ->with('message',trans('myservice.rollback_failed'))
                ->with('message_type','warning');
        }
    }
}
