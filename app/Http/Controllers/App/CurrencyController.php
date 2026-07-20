<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Validator;

class CurrencyController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * View All Currencies
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index(){
        $this->data = [
            'page_title' => "Manage Currencies"
        ];
        return view('app.currency.index',$this->data);
    }

    /**
     * Ajax - Render services into data table
     * @param Request $request
     * @return mixed
     */
    function render_currencies(Request $request){
        $query = Currency::select([
            'id','title','symbol_left','symbol_right','code','value','decimal_point','thousand_point'
        ]);
        $currencies = $query;
        return Datatables::of($currencies)
            ->addColumn('symbol',function ($currencies){
                return $currencies->symbol_left.' '.$currencies->symbol_right;
            })
            ->addColumn('action', function ($currencies) {
                return '<a onclick="AppModal(this.href,\''.trans('common.lbl_edit').' '.$currencies->name.'\');return false;"  href="'.secure_url('currency/update/'.$currencies->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> '.trans('common.lbl_edit').'</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\''.trans('common.lbl_remove').' '.$currencies->title.'\',\''.trans('common.ask_remove').'\' );return false;" href="'.secure_url('currency/remove/'.$currencies->id).'" class="btn btn-xs btn-danger"><i class="fa fa-times-circle"></i> '.trans('common.btn_delete').'</a>';
            })
            ->rawColumns(['action','symbol'])
            ->make(true);
    }

    /**
     * Add or Update Service
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function edit($id=''){
        if(!empty($id)){
            $row = Currency::find($id)->toArray();
        }else{
            $row = AppHelper::renderColumns('currencies');
        }
        $this->data['row'] = $row;
        return view('app.currency.update',$this->data);
    }

    /**
     * Update Service
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function update(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'value' => 'required'
        ]);
        $validator->sometimes('code', 'required|unique:currencies', function ($input) {
            return $input->id == '';
        });
        if($validator->fails()){
            AppHelper::logger('warning','Currency Validation Failed','Unable to update tha Currency info',$request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $to_update = [
            'title' => $request->title,
            'code' => $request->code,
            'value' => $request->value,
            'symbol_left' => $request->symbol_left,
            'symbol_right' => $request->symbol_right,
            'decimal_point' => $request->decimal_point,
            'thousand_point' => $request->thousand_point,
        ];
        if(!empty($request->id)){
            Currency::where('id',$request->id)->update($to_update);
            $currency_id = $request->id;
        }else{
            $currency_id = Currency::insertGetId($to_update);
        }
        AppHelper::logger('success','Currency Updated','Currency ID '.$currency_id.' updated successfully');
        return redirect('currencies')
            ->with('message',trans('common.msg_update_success'))
            ->with('message_type','success');
    }

    /**
     * Delete Service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    function delete($id){
        $service = Currency::find($id);
        $service->delete();
        AppHelper::logger('success',"Currency Delete","Currency ID $id has been deleted");
        return redirect()->back()->with('message',trans('common.msg_remove_success'))
            ->with('message_type','success');
    }
}
