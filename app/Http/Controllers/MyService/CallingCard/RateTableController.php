<?php

namespace App\Http\Controllers\MyService\CallingCard;

use app\Library\ApiHelper;
use app\Library\AppHelper;
use App\Models\RateTable;
use App\Models\RateTableGroup;
use App\Models\UserRateTable;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class RateTableController extends Controller
{
    private $log_title;

    public function __construct()
    {
        parent::__construct();
        $this->log_title = "Rate Table";
    }

    /**
     * View - Price lists
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $this->data['page_title'] = "Manage Price Lists";
        if(auth()->user()->group_id == 4 || auth()->user()->group_id == 5){
            return view('myservice.calling-cards.rate-tables.retailers',$this->data);
        }
        $this->data['rate_groups'] = RateTableGroup::where('user_id', auth()->user()->id)->select('id', 'name')->get();
        return view('myservice.calling-cards.rate-tables.index', $this->data);
    }

    /**
     * View - V2 Price lists
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function indexV2()
    {
        $this->data['page_title'] = trans('cc_price_lists.page_title');
        $this->data['isRetailerPriceList'] = in_array((int) auth()->user()->group_id, [4, 5], true);
        $this->data['rate_groups'] = collect();

        if (!$this->data['isRetailerPriceList']) {
            $this->data['rate_groups'] = RateTableGroup::where('user_id', auth()->user()->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        return view('v2.myservice.calling-cards.rate-tables.price-lists', $this->data);
    }

    /**
     * Ajax - Fetch Price lists
     * @param Request $request
     * @return mixed
     */
    function fetch_data(Request $request)
    {
//        dd($request->all());
        $query = RateTable::join('rate_table_groups', 'rate_table_groups.id', 'rate_tables.rate_group_id')
            ->join('calling_cards', 'calling_cards.id', 'rate_tables.cc_id')
            ->select(
                'rate_tables.id as id',
                'rate_tables.rate_group_id as rate_group_id',
                'calling_cards.name',
                'calling_cards.description as card_desc',
                'calling_cards.face_value as face_value',
                'rate_tables.sale_price as sale_price_tmp',
                'rate_tables.sale_margin as sale_margin_tmp'
            );
        if(auth()->user()->group_id == 2){
            $query->addSelect('calling_cards.buying_price as buying_price');
        }else{
            $query->addSelect('rate_tables.buying_price as buying_price');
        }
        $query->where('rate_tables.rate_group_id', $request->input('rate_table_group_id'));
        return Datatables::of($query)
            ->addColumn('description', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="' . $query->card_desc . '" data-original-title="' . $query->name . '" title="">' . AppHelper::doTrim_text($query->card_desc, 30, true) . '</span>';
            })
            ->addColumn('sale_price', function ($query) {
                return '<div class="form-group"><input  onkeypress="return isNumberKey(event,this.id);"  type="text" onBlur="validateSalePrice(this)" class="money-input form-control sp_val" id="sp_' . $query->id . '" value="' . $query->sale_price_tmp . '" data-min="0" data-max="' . number_format($query->face_value,2) . '"></div>';
            })
            ->addColumn('sale_margin', function ($query) {
                $sale_margin = $query->sale_price_tmp - $query->buying_price;
                return '<span id="sm_'.$query->id.'">'.$sale_margin.'</span>';
            })
            ->addColumn('action', function ($query) {
                return '<button id="btn_' . $query->id . '" onclick="updateSalePrice(\'' . $query->id . '\',$(\'#sp_' . $query->id . '\').val());return false;"  href="#" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i>&nbsp;' . trans('common.btn_update') . '</button>';
            })
            ->rawColumns(['action', 'description', 'sale_price','sale_margin'])
            ->make(true);
    }

    /**
     * POST - update price lists
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function update_price(Request $request)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'rate_table_id' => 'required|exists:rate_tables,id',
            'sale_price' => 'required'
        ]);
        if($validator->fails()){
            AppHelper::logger('warning',$this->log_title." AJAX Rate Update","Update validation failed",$request->all());
            return ApiHelper::response('400',200,trans('common.msg_update_error'));
        }
        \DB::beginTransaction();
        $query = RateTable::join('rate_table_groups', 'rate_table_groups.id', 'rate_tables.rate_group_id')
            ->join('calling_cards', 'calling_cards.id', 'rate_tables.cc_id')
            ->where('rate_tables.id',$request->rate_table_id)
            ->select(
                'rate_tables.id as id',
                'rate_tables.rate_group_id as rate_group_id',
                'calling_cards.name',
                'calling_cards.description as card_desc',
                'calling_cards.face_value as face_value',
                'rate_tables.cc_id',
                'rate_tables.sale_price',
                'rate_tables.sale_margin'
            );
        if(auth()->user()->group_id == 2){
            $query->addSelect('calling_cards.buying_price as buying_price');
        }else{
            $query->addSelect('rate_tables.buying_price as buying_price');
        }
        $rate_table = $query->first();
        if($request->sale_price == 0 || $request->sale_price == 0.00){
            $sale_margin =0;
        }else{
            $sale_margin = number_format($request->sale_price - $rate_table->buying_price,2);
        }
        $update = tap($rate_table)->update([
            'sale_price' => $request->sale_price,
            'sale_margin' => $sale_margin,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => auth()->user()->id
        ]);
        $rate_group_id = $rate_table->rate_group_id;
        $cc_id = $rate_table->cc_id;
        //if administrator changes the sale_price then buying_price of child's(managers) for this card need to changed
        if(auth()->user()->group_id == 2)
        {
            $childRetailers = User::where('parent_id',auth()->user()->id)->select('id','username')->get();
            foreach ($childRetailers as $childRetailer) {
                //check the manager user rate tables we selected
                $checkUserRateTable = UserRateTable::where('user_id',$childRetailer->id)
                    ->where('rate_group_id','=',$rate_group_id)
                    ->first();
                if(collect($checkUserRateTable)->count() != 0){
                    //grab the rate_table
                    $checkRateTables = RateTable::where('user_id','=',$childRetailer->id)
                        ->where('cc_id','=',$cc_id)
                        ->get();
//                    dd($checkRateTables);
                    if(collect($checkRateTables)->count() != 0) {
                        foreach ($checkRateTables as $checkRateTable) {
                            if ($request->input('sale_price') == $checkRateTable->buying_price) {
                                $buying_price_tmp = $request->input('sale_price');
                                $sale_price_tmp = $checkRateTable->sale_price;
                                $sale_margin_tmp = $checkRateTable->sale_margin;
                                RateTable::where('id', $checkRateTable->id)
                                    ->where('user_id', '=', $childRetailer->id)
                                    ->where('cc_id', '=', $cc_id)
                                    ->update([
                                        'buying_price' => $buying_price_tmp,
                                        'sale_price' => $sale_price_tmp,
                                        'sale_margin' => $sale_margin_tmp,
                                        'updated_at' => date('Y-m-d H:i:s'),
                                        'updated_by' => auth()->user()->id
                                    ]);
                                Log::info('this 0');
                            } else {
                                $checkMargin = $checkRateTable->sale_price - $request->input('sale_price');
                                if ($checkMargin < 0) {
                                    RateTable::where('id', $checkRateTable->id)
                                        ->where('user_id', '=', $childRetailer->id)
                                        ->where('cc_id', '=', $cc_id)
                                        ->update([
                                            'buying_price' => $request->input('sale_price'),
                                            'sale_margin' => $checkMargin,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'updated_by' => auth()->user()->id
                                        ]);
                                    Log::info('this 1');
                                } else {
                                    RateTable::where('id', $checkRateTable->id)
                                        ->where('user_id', '=', $childRetailer->id)
                                        ->where('cc_id', '=', $cc_id)
                                        ->update([
                                            'buying_price' => $request->input('sale_price'),
                                            'sale_price' => $checkRateTable->sale_price,
                                            'sale_margin' => $checkMargin,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'updated_by' => auth()->user()->id
                                        ]);
                                    Log::info('this 2');
                                }
                            }
                        }
                    }
                }
            }
        }
        \DB::commit();
        AppHelper::logger('success',$this->log_title." AJAX Rate Update","sale price were updated",$request->all());
        $res_data = [
            'sale_price' => number_format($update->sale_price,2),
            'sale_margin' =>  number_format($update->sale_margin,2)
        ];
        return ApiHelper::response('200',200,trans('common.msg_update_success'),$res_data);
    }


    /**
     * Ajax - Retailer price lists
     * @param Request $request
     * @return mixed
     */
    function getMyPriceLists(Request $request)
    {
        $query = RateTable::join('rate_table_groups','rate_table_groups.id','rate_tables.rate_group_id')
            ->join('user_rate_tables','user_rate_tables.rate_group_id','rate_table_groups.id')
            ->join('calling_cards','calling_cards.id','rate_tables.cc_id')
            ->where('user_rate_tables.user_id',auth()->user()->id)
            ->select([
                'calling_cards.name',
                'calling_cards.description as card_desc',
                'rate_tables.sale_price'
            ]);
        return Datatables::of($query)
            ->addColumn('description', function ($query) {
                return '<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="' . $query->card_desc . '" data-original-title="' . $query->name . '" title="">' . AppHelper::doTrim_text($query->card_desc, 30, true) . '</span>';
            })
            ->rawColumns(['description'])
            ->make(true);
    }

    /**
     * Ajax - Retailer V2 price cards
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function getMyPriceListsV2(Request $request)
    {
        $length = (int) $request->input('length', 12);
        if ($length < 1 || $length > 48) {
            $length = 12;
        }

        $page = max(1, (int) $request->input('page', 1));
        $search = trim((string) $request->input('search', ''));

        $query = RateTable::join('rate_table_groups','rate_table_groups.id','rate_tables.rate_group_id')
            ->join('user_rate_tables','user_rate_tables.rate_group_id','rate_table_groups.id')
            ->join('calling_cards','calling_cards.id','rate_tables.cc_id')
            ->join('telecom_providers','telecom_providers.id','calling_cards.telecom_provider_id')
            ->where('user_rate_tables.user_id', auth()->user()->id)
            ->select([
                'calling_cards.name',
                'calling_cards.description as card_desc',
                'telecom_providers.name as provider_name',
                'rate_tables.sale_price'
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('calling_cards.name', 'like', '%' . $search . '%')
                    ->orWhere('calling_cards.description', 'like', '%' . $search . '%')
                    ->orWhere('telecom_providers.name', 'like', '%' . $search . '%');
            });
        }

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $length));
        $page = min($page, $lastPage);

        $rows = $query->orderBy('calling_cards.name')
            ->skip(($page - 1) * $length)
            ->take($length)
            ->get()
            ->map(function ($row) {
                $description = trim(strip_tags((string) $row->card_desc));
                $providerName = trim((string) $row->provider_name);
                $providerKey = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $providerName)), '-');

                return [
                    'name' => $row->name,
                    'description' => $description,
                    'description_short' => AppHelper::doTrim_text($description, 110, true),
                    'sale_price' => number_format((float) $row->sale_price, 2),
                    'provider_name' => $providerName,
                    'provider_key' => $providerKey,
                    'initial' => strtoupper(substr(trim((string) $row->name), 0, 1)),
                ];
            })
            ->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'length' => $length,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }

}
