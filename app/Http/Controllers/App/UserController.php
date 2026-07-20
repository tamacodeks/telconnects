<?php

namespace App\Http\Controllers\App;

use App\Events\PaymentReceived;
use app\Library\AppHelper;
use app\Library\DBHelper;
use app\Library\SecurityHelper;
use App\Models\AppCommission;
use App\Models\CallingCard;
use App\Models\CallingCardAccess;
use App\Models\CallingCardPin;
use App\Models\Commission;
use App\Models\Country;
use App\Models\CreditLimit;
use App\Models\Payment;
use App\Models\RateTable;
use App\Models\RateTableGroup;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\UserGroup;
use App\Models\UserRateTable;
use App\User;
use App\Models\Manager_commission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Validator;
use App\Models\DailyLimit;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            if(!in_array(auth()->user()->group_id,[1,2,3])){
                Log::warning('access denied for users index');

                AppHelper::logger('warning',"Users",auth()->user()->username." trying to access users list,access denied");
                return redirect()->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            return $next($request);
        })->except(['end_impersonate','profile','update_profile']);
    }

    /**
     * View All Users
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index(Request $request)
    {
        $page_data = [
            'page_title' => "Manage Users"
        ];
        AppHelper::logger('info', 'Users view', "Viewing all users");
        return view('app.users.index', $page_data);
    }

    function user_info(Request $request)
    {
        $page_data = [
            'page_title' => "Information of Users"
        ];
        AppHelper::logger('info', 'Users view', "Viewing all users");
        return view('app.users.user_info', $page_data);
    }

    /**
     * fetch ip address and phone of all users
     */

    public function getIpData()
    {
        $query = User::join('user_groups', 'user_groups.id', 'users.group_id')
            ->select(['users.username', 'user_groups.name', 'users.ip_address', 'users.mobile', 'users.email']);
        $query->where('users.status',1);
        $query->where('users.ip_address','!=','NULL');
        $users = $query;
        return Datatables::of($users)
            ->make(true);
    }

    /**
     * Ajax - Fetch all users
     * @return mixed
     */
    public function getRowDetailsData()
    {
        $query = User::join('user_groups', 'user_groups.id', 'users.group_id')
            ->select(['users.cust_id', 'users.username', 'user_groups.name', 'users.created_at', 'users.updated_at', 'users.parent_id', 'users.id', 'users.currency', 'users.group_id', 'users.last_activity', 'users.method']);
        if (auth()->user()->group_id == 1) {
            $query->where(function ($query) {
                $query->where('users.parent_id', '=', '0')
                    ->orWhereNull('users.parent_id');
            });
        } elseif (auth()->user()->group_id == 2) {
            $query->where('users.parent_id', auth()->user()->id);
            $query->where('users.group_id', '!=', 1);
            $query->where('users.id', '!=', auth()->user()->id);
        } elseif (auth()->user()->group_id == 3) {
            $query->where('users.parent_id', auth()->user()->id);
        } else {
            $query->where('users.id', '!=', auth()->user()->id);
            $query->where('users.group_id', '=', 6);
        }
        $query->where('users.status',1);
        $users = $query;
        return Datatables::of($users)
            ->addColumn('representative', function ($users) {
                return optional(User::find($users->parent_id))->username;
            })
            ->addColumn('balance', function ($users) {
                if ($users->group_id == 2) {
                    return AppHelper::getAdminBalance(true);
                }
                return AppHelper::getBalance($users->id, DEFAULT_CURRENCY, true);
            })
            ->addColumn('action', function ($users) {
                $html = "";
                $enc_id = SecurityHelper::simpleEncDec('ec', $users->id);
                $html .='<a href="' . secure_url('user/view/' . $users->id) . '" class="btn btn-xs btn-success"><i class="fa fa-id-card"></i> ' . trans('common.lbl_view') . '</a>&nbsp;&nbsp;<a href="' . secure_url('user/update/' . $users->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> ' . trans('common.lbl_edit') . '</a>&nbsp;&nbsp;<a onclick="AppConfirmDelete(this.href,\'' . trans('common.lbl_remove') . ' ' . $users->username . '\',\'' . trans('common.ask_remove') . '\' );return false;" href="' . secure_url('user/remove/' . $users->id) . '" class="btn btn-xs btn-danger hide"><i class="fa fa-times-circle"></i> ' . trans('common.btn_delete') . '</a>
                <a href="'.secure_url('user/impersonate/'.$enc_id).'" data-toggle="tooltip" title="'.trans('users.lbl_user_impersonate').' '.$users->username.'" class="btn btn-primary btn-xs"><i class="fa fa-user-secret"></i></a>';
                if($users->group_id == 5){
                    $html .= '&nbsp;&nbsp;<a href="'.secure_url('cc/refresh/price-lists/'.$users->id).'" onclick="syncPriceLists(this.href,this.id);return false;" id="sync'.$users->id.'" data-toggle="tooltip" title="Refresh price lists for '.$users->username.'" class="btn btn-primary btn-xs"><i class="fa fa-sync-alt"></i></a>';
                }
                return $html;
            })
            ->addColumn('credit_limit', function ($users) {
                if ($users->group_id == 2) {
                    return AppHelper::getAdminBalance(true,true);
                }
                return optional(CreditLimit::where('user_id', $users->id)->first())->credit_limit;
            })
            ->addColumn('last_online_at', function ($users) {
                return $users->last_activity != '' ? $users->last_activity : "-";
            })
            ->addColumn('status_indicator', function ($users) {
                return AppHelper::status_indicator($users->last_activity);
            })
            ->addColumn('auth_method', function ($users) {
                $method = (int) $users->method;
                $methods = [
                    1 => [
                        'label' => '1 - IP OTP',
                        'class' => 'auth-method-badge auth-method-badge--otp',
                        'title' => 'OTP is required when the login IP changes',
                    ],
                    2 => [
                        'label' => '2 - 2FA',
                        'class' => 'auth-method-badge auth-method-badge--totp',
                        'title' => 'Authenticator 2FA is used when enabled and verified',
                    ],
                ];

                $methodData = $methods[$method] ?? [
                    'label' => '0 - No Auth',
                    'class' => 'auth-method-badge auth-method-badge--none',
                    'title' => 'No extra authentication step',
                ];

                return '<span class="'.$methodData['class'].'" title="'.$methodData['title'].'">'.$methodData['label'].'</span>';
            })
            ->rawColumns(['action', 'status_indicator', 'auth_method'])
            ->make(true);
    }

    public function runResetCorrectionsToday(Request $request)
    {
        if (!in_array(auth()->user()->group_id, [1, 2])) {
            AppHelper::logger('warning', 'Users', auth()->user()->username . ' unauthorized reset corrections today attempt');
            return response()->json([
                'success' => false,
                'message' => trans('common.access_violation')
            ], 403);
        }

        try {
            Artisan::call('transactions:reset-corrections-today');
            $output = trim(Artisan::output());

            AppHelper::logger('info', 'Users', auth()->user()->username . ' ran reset corrections today');

            return response()->json([
                'success' => true,
                'message' => 'Reset corrections command executed.',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::warning('Reset corrections today failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to run reset corrections.'
            ], 500);
        }
    }

    public function resetTransactionCorrections(Request $request)
    {
        if (!in_array(auth()->user()->group_id, [1, 2])) {
            AppHelper::logger('warning', 'Users', auth()->user()->username . ' unauthorized correction reset attempt');
            return response()->json([
                'success' => false,
                'message' => trans('common.access_violation')
            ], 403);
        }

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $start = Carbon::parse($request->input('from'));
        $end = Carbon::parse($request->input('to'));
        if ($start->greaterThan($end)) {
            return response()->json([
                'success' => false,
                'message' => 'From date must be before To date.'
            ], 422);
        }
        $batchSize = 5000;
        $totalUpdated = 0;

        do {
            $ids = DB::table('transactions')
                ->whereBetween('date', [$start, $end])
                ->where('is_corection', 1)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $updated = DB::table('transactions')
                ->whereIn('id', $ids)
                ->update(['is_corection' => 0]);

            $totalUpdated += $updated;
        } while ($ids->count() === $batchSize);

        AppHelper::logger('info', 'Users', auth()->user()->username . ' reset transaction corrections', [
            'rows_updated' => $totalUpdated
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction corrections reset for selected range.',
            'rows_updated' => $totalUpdated,
            'from' => $start->format('Y-m-d H:i:s'),
            'to' => $end->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * View User by ID
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function view(Request $request, $id)
    {
        $user = User::where('id', $id)->withCount('children')->with('group')->withCount('orders')->with('commissions')->with('payment_history')->first();
        $user_rate_table = UserRateTable::join('rate_table_groups', 'rate_table_groups.id', 'user_rate_tables.rate_group_id')->where('user_rate_tables.user_id', $id)->select('rate_table_groups.name')->first();
        $page_data = array(
            'page_title' => "Viewing " . $user->username,
            'row' => $user,
            'next_user' => '',
            'prev_user' => '',
            'services' => Service::select('id', 'name', 'status')->get(),
            'user_image' => count($user->getMedia('avatar')) > 0 ? $user->getMedia('avatar')->first()->getUrl('thumb') : 'images/avatar.png',
            'rate_table' => optional($user_rate_table)->name
        );
        AppHelper::logger('info', 'User view', 'View user ' . $user->username);
        return view('app.users.view', $page_data);
    }

    /**
     * Impersonate User
     * @param Request $request
     * @param $enc
     * @return \Illuminate\Http\RedirectResponse
     */
    function impersonate(Request $request, $enc)
    {
        if ($enc == '') {
            AppHelper::logger('warning', 'Impersonate Failed', 'Unable to impersonate, enc id not found!');
            return redirect()->back()->with('message', trans('users.impersonate_failed'))->with('message_type', 'warning');
        }
        $dec_user_id = SecurityHelper::simpleEncDec('de', $enc);
        $user = User::find($dec_user_id);
        if (!$user) {
            AppHelper::logger('warning', 'Impersonate Failed', 'Unable to impersonate, user not found!');
            return redirect()->back()->with('message', trans('users.impersonate_failed'))->with('message_type', 'warning');
        }
        if (\Session::has('impersonate')) {
            $impersonate_array = \Session::get('impersonate');
            $collection = collect($impersonate_array);
            if ($collection->contains(auth()->user()->id)) {
                //do nothing
            } else {
                $impersonate_array = array_add($impersonate_array, count($impersonate_array), auth()->user()->id);
            }
        } else {
            $impersonate_array = [
                auth()->user()->id
            ];
        }
        \Session::put('impersonate', $impersonate_array);
        $old_user = auth()->user()->username;
        \Session::put('impersonated', 'true');
        auth()->loginUsingId($user->id);
        \Session::put('userGroup',optional(UserGroup::find(auth()->user()->group_id))->name);
        AppHelper::logger('success', 'Impersonate OK', $old_user . ' impersonate to ' . $user->username);
        return redirect('dashboard')->with('message', trans('users.impersonate_success'))->with('message_type', 'success');
    }

    /**
     * End Impersonate
     * @param Request $request
     * @param $enc
     * @return \Illuminate\Http\RedirectResponse
     */
    function end_impersonate(Request $request, $enc)
    {
        if ($enc == '' || \Session::has('impersonate') == false) {
            AppHelper::logger('warning', 'Stop impersonate Failed', 'Unable to impersonate, enc id not found!');
            return redirect()->back()->with('message', trans('users.impersonate_stop_failed'))->with('message_type', 'warning');
        }
        $dec_user_id = SecurityHelper::simpleEncDec('de', $enc);
        $user = User::find($dec_user_id);
        if (!$user) {
            AppHelper::logger('warning', 'Stop Impersonate Failed', 'Unable to stop impersonate, user not found!');
            return redirect()->back()->with('message', trans('users.impersonate_stop_failed'))->with('message_type', 'warning');
        }
        $old_user = auth()->user()->username;
        $collection = collect(\Session::get('impersonate'));
        if ($collection->count() != 0) {
            \Auth::loginUsingId(array_last(\Session::get('impersonate')));
            \Session::put('userGroup',optional(UserGroup::find(auth()->user()->group_id))->name);
            $collection->pop();
            $impersonate_array = $collection->all();
            \Session::forget('impersonate');
            \Session::put('impersonate', $impersonate_array);
            $new_collection = collect($impersonate_array);
            if ($new_collection->count() == 0) {
                \Session::forget('impersonate');
                \Session::forget('impersonated');
            }
            AppHelper::logger('success', 'Stop impersonate OK', $old_user . ' impersonate to ' . $user->username);
            return redirect('dashboard')->with('message', trans('users.impersonate_back_account'))->with('message_type', 'success');
        } else {
            \Session::forget('impersonate');
            \Session::forget('impersonated');
            return redirect('dashboard')->with('message', trans('users.impersonate_back_account'))->with('message_type', 'success');
        }
    }

    /**
     * View Edit User
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function edit($id = '')
    {
        if ($id != '') {
            if ($id == auth()->user()->id) {
                AppHelper::logger('warning', 'User Update', 'User ' . auth()->user()->username . ' trying to edit himself', []);
                return redirect()->back()
                    ->with('message', trans('common.msg_update_error'))
                    ->with('message_type', 'warning');
            }
            $row = User::where('id', $id)->with(['payment_history' => function ($q) {
                $q->take(10);
            }])->first()->toArray();
            $user = User::find($id);
//            dd($row);
            $row['user_image'] = count($user->getMedia('avatar')) > 0 ? $user->getMedia('avatar')->first()->getUrl('thumb') : 'images/avatar.png';
            $user_rate_table = UserRateTable::join('rate_table_groups', 'rate_table_groups.id', 'user_rate_tables.rate_group_id')->where('user_rate_tables.user_id', $id)->select('rate_table_groups.id')->first();
            $row['rate_group_id'] = optional($user_rate_table)->id;
            $child = User::where('parent_id' ,$user->id)->latest()->first();
            $row['child_id'] =  optional($child)->id;
            $row['max_active_sessions'] = isset($row['max_active_sessions']) ? (int) $row['max_active_sessions'] : 1;
        } else {
            $row = AppHelper::renderColumns('users');
            $row['payment_history'] = [];
            $row['user_image'] = 'images/avatar.png';
            $row['rate_group_id'] = '';
            $row['max_active_sessions'] = 1;
        }
//        dd($row);
        $page_data = array(
            'row' => $row,
            'user_groups' => AppHelper::render_user_group(),
            'parent_manager' => AppHelper::render_parent_manager(),
            'countries' => Country::select('id', 'nice_name', 'currency', 'timezone')->where('status', 1)->where('id',73)->get(),
            'services' => Service::select('id', 'name', 'status')->get(),
            'rate_table_groups' => RateTableGroup::where('user_id', auth()->user()->id)->select('id', 'name')->get()
        );
        return view('app.users.update', $page_data);
    }

    /**
     * Post Update User
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    function update(Request $request)
    {
//        dd($request->all());
        $mobile_number = ltrim($request->mobile, '+');
        $rules = [
//            'email' => 'required|email',
            'country_id' => 'exists:countries,id',
            'group_id' => 'exists:user_groups,id',
            'first_name' => 'required',
            'active_device_limit' => 'in:1,2',
        ];
        if ($request->id == '') {
            $rules['username'] = 'required|unique:users';
        }
        if ($request->group_id == 5) {
            $rules['web_hook_url'] = 'required';
            $rules['web_hook_uri'] = 'required';
            $rules['web_hook_token'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            AppHelper::logger('warning', 'User Update Failed', 'Unable to update user', $request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message', $html)
                ->with('message_type', 'warning');
        }
        try {
            \DB::beginTransaction();
            //user creation
            if (auth()->user()->group_id == 1) {
                $parent_id = null;
            } elseif ($request->group_id == 2) {
                $parent_id = null;
            } elseif (!empty($request->parent_id)) {
                $parent_id = $request->parent_id;
            } else {
                $parent_id = auth()->user()->id;
            }
            if ($request->id != '') {
                $manager = User::where('id', $request->id)->first();
                //email send when manager change retailer phone number
//                if ($request->input('group_id') == 4) {
//                    if ($mobile_number != $manager->mobile) {
//                        $emails = ['sydkhalid7@gmail.com'];
//                        $send_email_data = array(
//                            'username' => $request->username,
//                            'mobile_number' => $mobile_number
//                        );
//                        \Mail::send('emails.manager_email_support', $send_email_data, function ($message) use ($emails) {
//                            $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
//                            $message->to($emails)->subject('DEMAT PRO Alert');
//                        });
//                    }
//                }
            }
            $activeDeviceLimit = (int) $request->input('active_device_limit', $request->id ? ($manager->max_active_sessions ?? 1) : 1);
            if (!in_array($activeDeviceLimit, [1, 2], true)) {
                $activeDeviceLimit = 1;
            }
            $authenticationMethod = ($request->authentication_method !== null && $request->authentication_method !== '')
                ? (int) $request->authentication_method
                : ($request->id ? (int) ($manager->method ?? 0) : 0);

            if ($activeDeviceLimit > 1) {
                $authenticationMethod = 2;
            }

            $user_data = [
                'group_id' => $request->group_id,
                'parent_id' => $parent_id,
                'username' => $request->username,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $mobile_number,
                'country_id' => $request->country_id,
                'currency' => $request->currency,
                'timezone' => $request->timezone,
                'address' => $request->address,
                'method' => $authenticationMethod,
                'max_active_sessions' => $activeDeviceLimit,
                'status' => !empty($request->status) ? 1 : 0,
                'pin_print_again' => !empty($request->pin_print_again) ? 1 : 0,
                'enable_ip' => !empty($request->enable_ip) ? 1 : 0,
                'is_api_user' => !empty($request->is_api_user) ? 1 : 0,
                'daily' => !empty($request->daily) ? $request->daily : null,
                'weekly' => !empty($request->weekly) ? $request->weekly : null,
                'monthly' => !empty($request->monthly) ? $request->monthly : null,
                'web_hook_uri' => !empty($request->web_hook_uri) ? $request->web_hook_uri : null,
                'web_hook_token' => !empty($request->web_hook_token) ? $request->web_hook_token : null,
            ];
            if ($request->id != '') {
                $user_id = $request->id;
                $child_id = User::select('id')->where('parent_id', $user_id)->get();
                if($request->has('password') && !empty($request->input('password'))){
                    $user_data['password'] = \Hash::make($request->input('password'));
                }
                $user_data['updated_at'] = date('Y-m-d H:i:s');
                $user_data['updated_by'] = auth()->user()->id;
                User::where('id', $request->id)->update($user_data);
            } else {
                $user_data['cust_id'] = AppHelper::generateCustomerID();
                $user_data['api_token'] = str_random(60); //for chat, we need this token
                $user_data['password'] = \Hash::make($request->input('password'));
                $user_data['created_at'] = date('Y-m-d H:i:s');
                $user_data['created_by'] = auth()->user()->id;
                $user_id = User::insertGetId($user_data);

                //just add this user to access all cards
                $calling_cards = CallingCard::all();
                foreach ($calling_cards as $calling_card) {
                    CallingCardAccess::insert([
                        'cc_id' => $calling_card->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => auth()->user()->id
                    ]);
                }

                if(!empty($request->rate_group_id)){
                    //create one default rate group and add all prices under the rate group
                    if ($request->group_id == 3) {
                        $new_rate_group_id = RateTableGroup::insertGetId([
                            'user_id' => $user_id,
                            'name' => "DEFAULT",
                            'description' => "Default price list created by system",
                            'status' => "1",
                            'updated_at' => date("Y-m-d H:i:s"),
                            'updated_by' => auth()->user()->id
                        ]);
                        //copy the rate_table and set the sale_price as 0.00
                        $rate_table_group = RateTableGroup::find($request->rate_group_id);
                        $fetch_rate_tables = RateTable::where('rate_group_id', $rate_table_group->id)
                            ->where('user_id', $rate_table_group->user_id)->get();
                        if (!empty($fetch_rate_tables)) {
                            foreach ($fetch_rate_tables as $fetch_rate_table) {
                                $check_cc = CallingCard::find($fetch_rate_table->cc_id);
                                if($check_cc){
                                    $rate_table_data = array(
                                        'user_id' => $user_id,
                                        'rate_group_id' => $new_rate_group_id,
                                        'cc_id' => $fetch_rate_table->cc_id,
                                        'buying_price' => $fetch_rate_table->sale_price,
                                        'sale_price' => "0.00",
                                        'sale_margin' => "0.00",
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'created_by' => auth()->user()->id
                                    );
                                    RateTable::insert($rate_table_data);
                                }
                            }
                        }
                    }
                }
            }
            //user image
            if ($request->hasFile('image')) {
                $user = User::find($user_id);
                $fileTmp = $request->file('image');
                $fileName = str_slug($request->username, '_') . '.' . $fileTmp->getClientOriginalExtension();
                $user->addMedia($request->file('image'))->usingFileName($fileName)->toMediaCollection('avatar');
            }
            //add user balance
            if (!empty($request->amount) && $request->amount != '0') {
                $old_user_balance = AppHelper::getBalance($user_id, $request->currency, false);
                $new_balance = $old_user_balance + $request->amount;
                $trans_id = Transaction::insertGetId([
                    'user_id' => $user_id,
                    'date' => date('Y-m-d H:i:s'),
                    'type' => 'credit',
                    'amount' => $request->amount,
                    'credit' => $request->amount,
                    'prev_bal' => $old_user_balance,
                    'balance' => $new_balance,
                    'description' => $request->description,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => auth()->user()->id,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $payment_id = Payment::insertGetId([
                    'user_id' => $user_id,
                    'transaction_id' => $trans_id,
                    'date' => date('Y-m-d H:i:s'),
                    'amount' => $request->amount,
                    'description' => $request->description,
                    'received_by' => auth()->user()->id
                ]);
                $payment = Payment::find($payment_id);
//                event(new PaymentReceived($payment));
            }
            if (!empty($request->credit_limit) && $request->credit_limit != '0') {
                //check user already have a credit limit
                $credit_limit = CreditLimit::where('user_id', $user_id)->first();
                if (!empty($credit_limit)) {
                    CreditLimit::where('id', $credit_limit->id)->where('user_id', $user_id)->update([
                        'credit_limit' => '-' . $request->credit_limit,
                        'updated_at' => date("Y-m-d H:i:s"),
                        'updated_by' => auth()->user()->id
                    ]);
                } else {
                    //insert as new
                    CreditLimit::insert([
                        'type' => 'credit',
                        'user_id' => $user_id,
                        'credit_limit' => '-' . $request->credit_limit,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => auth()->user()->id
                    ]);
                }
                Log::info('user ' . $user_id . ' credit limit was updated');
            }
            if (!empty($request->daily_limit) && $request->daily_limit != '0') {
                //check user already have a daily limit
                $daily_limit = DailyLimit::where('user_id', $user_id)->first();
                if (!empty($daily_limit)) {
                    DailyLimit::where('id', $daily_limit->id)->where('user_id', $user_id)->update([
                        'daily_limit' => $request->daily_limit,
                        'updated_at' => date("Y-m-d H:i:s"),
                        'updated_by' => auth()->user()->id
                    ]);
                } else {
                    //insert as new
                    DailyLimit::insert([
                        'type' => 'credit',
                        'user_id' => $user_id,
                        'daily_limit' => $request->daily_limit,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => auth()->user()->id
                    ]);
                }
                Log::info('user ' . $user_id . ' daily limit was updated');
            }

            if (in_array(auth()->user()->group_id, [2, 3])) {
                //service access
                $services = Service::select('id', 'name')->get();
                foreach ($services as $service) {
                    if(auth()->user()->group_id == 2){
                        //update service access
                        $status = !empty($request->input('service_' . $service->id)) ? 1 : 0;
                        DBHelper::update_service_access($user_id, $service->id, $status);
                    }
                    $service_name = $service->name;
                    $get_app_def_commission = AppCommission::where('service_id', $service->id)->first();
                    if ($request->group_id == 3) {
                        $commission = optional($get_app_def_commission)->mgr_def_com;
                        if(Service::where('name','like', "%" . ucwords(str_replace('-', ' ', $service_name)) . "%")->where('status',1)->first()){
                            $status = 1;
                        }else{
                            $status = 0;
                        }
                        //get all childs and update the service access by status
                        $child_retailers = User::where('group_id', 4)->where('parent_id', $user_id)->select(['id', 'username'])->get();
                        if (collect($child_retailers)->count() > 0) {
                            foreach ($child_retailers as $child_retailer) {
                                DBHelper::update_service_access($child_retailer->id, $service->id, $status);
                            }
                        }
                    } elseif ($request->group_id == 4) {
                        $commission = optional($get_app_def_commission)->retailer_def_com;
                        if ($parent_id == null) {
                            if(Service::where('name','like', "%" . ucwords(str_replace('-', ' ', $service_name)) . "%")->where('status',1)->first()){
                                $status = 1;
                            }else{
                                $status = 0;
                            }
                        } else {
                            $parent_check = \DB::select("select * from `user_access` inner join `services` on `services`.`id` = `user_access`.`service_id` where `services`.`name` like '%" . ucwords(str_replace('-', ' ', $service_name)) . "%' and `user_access`.`user_id` = " . $parent_id . " and `user_access`.`status` = 1 limit 1 ");
                            if (collect($parent_check)->count() > 0) {
                                $status = 1;
                            } else {
                                $status = 0;
                            }
                        }
                    } else {
                        $commission = 0;
                        $status = 0;
                    }
                    if($request->id != '' && $service->id == '2' && auth()->user()->group_id == 2){
                        DBHelper::update_user_commission($user_id, $service->id, $request->m_commission);
                        DBHelper::update_manager_commission($user_id, $service->id, $request->r_commission);
                        foreach($child_id as $child){
                            DBHelper::update_user_commission($child->id, $service->id, $request->r_commission);
                        }
                    }
                    else{
                        DBHelper::update_user_commission($user_id, $service->id, $commission);
                        if($service->id == '2' && auth()->user()->group_id == 2){
                            DBHelper::update_manager_commission($user_id, $service->id, optional($get_app_def_commission)->retailer_def_com);
                        }
                        $user_check = Manager_commission::where('user_id', auth()->user()->id)->where('service_id', '2')->first();
//                        dd($user_check);
                        if($user_check && $service->id == '2'){
                            DBHelper::update_user_commission($user_id, $service->id, $user_check->commission);
                        }

                    }
                    //update service access
                    DBHelper::update_service_access($user_id, $service->id, $status);
                }
            }

            //update user rate table
            if (!empty($request->rate_group_id)) {
                UserRateTable::updateOrCreate([
                    'user_id' => $user_id
                ], [
                    'user_id' => $user_id,
                    'rate_group_id' => $request->rate_group_id,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => auth()->user()->id
                ]);
                $rate_table_parent = RateTable::where('user_id', $parent_id)
                    ->where('rate_group_id', $request->rate_group_id)
                    ->get();
                //change the manager buying_price and sale_price if sale_margin goes to negative
                if ($request->group_id == 3) {
                    if (isset($rate_table_parent)) {
                        foreach ($rate_table_parent as $item) {
                            $price_lists_check = RateTable::join('rate_table_groups', 'rate_table_groups.id', 'rate_tables.rate_group_id')
                                ->where('rate_tables.user_id', $user_id)
                                ->where('rate_tables.cc_id', $item->cc_id)
                                ->select([
                                    'rate_tables.sale_price',
                                    'rate_tables.buying_price',
                                    'rate_tables.rate_group_id',
                                    'rate_tables.cc_id'
                                ])
                                ->first();
                            if ($price_lists_check) {
                                //check for equivalence of master_retailer.sale_price == manager.buying_price
                                //if yes, don't update the table keep remain same or difference between the
                                // new buying_price and sale_price of manager produce negative, update
                                //sale_price and sale_margin as 0.00
                                if ($item->sale_price == $price_lists_check->buying_price) {
                                    //do nothing to update
                                } else {
                                    //check for new buying_price with sale_price will produce negative or not
                                    if (AppHelper::negativeCheck($price_lists_check->sale_price - $item->sale_price)) {
                                        RateTable::where('user_id', $user_id)
                                            ->where('rate_group_id', $price_lists_check->rate_group_id)
                                            ->where('cc_id', $item->cc_id)
                                            ->update([
                                                'buying_price' => $item->sale_price,
                                                'sale_price' => $price_lists_check->sale_price,
                                                'sale_margin' => $price_lists_check->sale_price - $item->sale_price,
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'updated_by' => auth()->user()->id
                                            ]);
                                    } else {
                                        RateTable::where('user_id', $user_id)
                                            ->where('rate_group_id', $price_lists_check->rate_group_id)
                                            ->where('cc_id', $item->cc_id)
                                            ->update([
                                                'buying_price' => $item->sale_price,
                                                'sale_price' => '0.00',
                                                'sale_margin' => "0.00",
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'updated_by' => auth()->user()->id
                                            ]);
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ($request->group_id == 5){
                    //web hook the change in rate tables
                    if (isset($rate_table_parent)) {
                        foreach ($rate_table_parent as $item) {
                            event(new \App\Events\RateTablePush($item,$user_id));
                        }
                    }
                }
            }
            //password change email to system administrator
            if($request->has('password') && !empty($request->input('password'))){
                $emails = ['tama2express@gmail.com'];
                $send_email_data = array(
                    'username' => $request->username,
                    'password_text' => $request->password
                );
                try{
                    Mail::send('emails.password_reset', $send_email_data, function($message) use ($emails,$send_email_data)
                    {
                        $message->to($emails)->from('noreply@tamaexpress.com', 'DEMAT PRO System')->subject("Password Changed for " . $send_email_data['username']);
                    });
                }catch (\Exception $e){
                    Log::warning('Password change email does not sent exception '.$e->getMessage());
                }
            }
            \DB::commit();
            AppHelper::logger('success', 'User Update', 'User ID ' . $user_id . ' has been updated', $request->except(['password','confirm_password']));
            return redirect('user/view/'.$user_id)->with('message', trans('common.msg_update_success'))
                ->with('message_type', 'success');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::warning('User Update Exception => ' . $e->getMessage());
            \DB::transaction(function () use ($e) {
                $update_error = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage()
                ];
                AppHelper::logger('warning', 'User Update Exception', $e->getMessage(), $update_error);
                Log::warning('User Update Exception Trace String => ' . $e->getTraceAsString());
            });
            return redirect()->back()
                ->with('message', trans('common.msg_update_error'))
                ->with('message_type', 'warning');
        }
    }

    /**
     * View User Profile
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function profile()
    {
        $page_data = [
            'page_tile' => "Profile " . auth()->user()->username,
            'user_image' => count(Auth::user()->getMedia('avatar')) > 0 ? Auth::user()->getMedia('avatar')->first()->getUrl('thumb') : 'images/avatar.png',
            'services' => Service::all()
        ];
        return view('common.profile', $page_data);
    }

    /**
     * Post Update Profile
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    function update_profile(Request $request)
    {
//        dd($request->all());
        $mobile_number = ltrim((string) $request->mobile, '+');
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'mobile' => 'required',
            'password' => ['nullable', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'password.regex' => 'Password must include at least one letter and one number.',
        ]);
        if ($validator->fails()) {
            AppHelper::logger('warning', 'User Update Failed', 'Unable to update user', $request->all());
            return redirect()->back()
                ->with('message', trans('common.msg_field_required'))
                ->with('message_type', 'warning')
                ->withErrors($validator)
                ->withInput();
        }
        $user = User::find(auth()->user()->id);
        if ($request->hasFile('image')) {
            $fileTmp = $request->file('image');
            $fileName = str_slug(auth()->user()->username, '_') . '.' . $fileTmp->getClientOriginalExtension();
            $user->addMedia($request->file('image'))->usingFileName($fileName)->toMediaCollection('avatar');
        }
        $user->email = $request->email;
        $user->mobile =  $mobile_number;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->updated_at = date("Y-m-d H:i:s");
        $user->updated_by = auth()->user()->id;
        if ($request->password != '') $user->password = \Hash::make($request->password);
        $user->save();
        if($request->has('password') && !empty($request->input('password'))){
            $emails = ['tama2express@gmail.com'];
            $send_email_data = array(
                'username' => $user->username,
                'password_text' => $request->password
            );
            try{
                Mail::send('emails.password_reset', $send_email_data, function($message) use ($emails,$send_email_data)
                {
                    $message->to($emails)->from('noreply@tamaexpress.com', 'TamaExpress Reseller System')->subject("Password Changed for " . $send_email_data['username']);
                });
            }catch (\Exception $e){
                Log::warning('Password change email does not sent exception '.$e->getMessage());
            }
        }
        AppHelper::logger('success', 'Profile Update', "User $user->username update his profile");
        return redirect()->back()->with('message', trans('common.msg_update_success'))
            ->with('message_type', 'success');
    }


    function delete($id)
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $user = User::find($id);
        AppHelper::logger('success', "Users", "User " . $user->username . "(" . $user->id . ") has been removed by " . auth()->user()->username);
        User::where('id',$id)->update([
            'status' => 0,
            'updated_at' => date("Y-m-d H:i:s"),
            'updated_by' => auth()->user()->id
        ]);
        \DB::commit();
        return redirect()
            ->back()
            ->with('message', trans('common.msg_remove_success'))
            ->with('message_type', 'success');
        try {
            \DB::beginTransaction();
            $user = User::find($id);
            Log::info("Starting remove user " . $user->username);
            //remove foreign keys first
            $calling_card_access = CallingCardAccess::where('user_id', $id)->delete();
            Log::info("calling card access for user " . $user->username . " deleted");
            //check if any pins locked by this account, if yes , unlock them now
            $calling_card_pins = CallingCardPin::where('locked_by', $id)->update([
                'locked_by' => null,
                'is_locked' => 0
            ]);
            Log::info("calling card pins released... ");
            Commission::where('user_id', $id)->delete();
            Log::info("All the commissions were deleted...");
            CreditLimit::where('user_id', $id)->delete();
            Log::info("Credit Limits were deleted...");
            AppHelper::logger('success', "Users", "User " . $user->username . "(" . $user->id . ") has been removed by " . auth()->user()->username);
            $user->delete();
            \DB::commit();
            return redirect()
                ->back()
                ->with('message', trans('common.msg_remove_success'))
                ->with('message_type', 'success');
        } catch (\Exception $exception) {
            \DB::rollBack();
            AppHelper::logger('warning', "Users", $exception->getMessage());
            return redirect()
                ->back()
                ->with('message', trans('common.msg_remove_remove') . " " . $exception->getMessage())
                ->with('message_type', 'success');
        }
    }

    function whoIsOnline()
    {
        $this->data['page_title'] = "View Who is online";
        $this->data['users'] = User::all();
        return view('common.online',$this->data);
    }
    public function checkUsername(Request $request){

        $isExists =User::where('username', '=', $request->username)->first();
        if($isExists){
            return response()->json([
                'success' => false,
                'message' => 'Username Already Exist'
            ]);
        }
        else{
            return response()->json([
                'success' => true,
            ]);
        }
    }
    function all_users(Request $request)
    {
        $page_data = [
            'page_title' => "All Users",
            'user_list' => User::select('id','username')->where('group_id', 3)->get()
        ];
        AppHelper::logger('info', 'Users view', "Viewing all users");
        return view('app.users.all_user', $page_data);
    }

    function refresh_popup_seen_users(Request $request)
    {
        if (!in_array(auth()->user()->group_id, [1, 2])) {
            AppHelper::logger('warning', 'Users', auth()->user()->username . ' unauthorized correction reset attempt');
            return response()->json([
                'success' => false,
                'message' => trans('common.access_violation')
            ], 403);
        }

        $page_data = [
            'page_title' => "Refresh Popup Seen Users",
        ];
        AppHelper::logger('info', 'Users view', "Viewing refresh popup seen users");
        return view('app.users.refresh_popup_seen_users', $page_data);
    }

    function fetch_all_users(Request $request){
        $query = User::join('user_groups', 'user_groups.id', 'users.group_id')
            ->select(['users.username', 'user_groups.name', 'users.status', 'users.last_activity', 'users.parent_id', 'users.id', 'users.currency', 'users.group_id'])->orderBy('users.status', 'DESC');
        $orders = $query;
        return Datatables::of($orders)
            ->addIndexColumn()
            ->addColumn('representative', function ($users) {
                return optional(User::find($users->parent_id))->username;
            })
            ->addColumn('status', function ($users) {
                if ($users->status == 1) {
                    return "Active";
                }
                return "InActive";
            })
            ->addColumn('balance', function ($users) {
                if ($users->group_id == 2) {
                    return AppHelper::getAdminBalance(true);
                }
                return AppHelper::getBalance($users->id, DEFAULT_CURRENCY, true);
            })
            ->addColumn('credit_limit', function ($users) {
                if ($users->group_id == 2) {
                    return AppHelper::getAdminBalance(true,true);
                }
                return optional(CreditLimit::where('user_id', $users->id)->first())->credit_limit;
            })
            ->filter(function ($query) use ($request) {
                if (!empty($request->input('parent_id'))) {
                    $query->where('users.parent_id',[$request->input('parent_id')]);
                }
                if (!empty($request->input('status'))) {
                    if($request->input('status') == '1') {
                        $query->where('users.status', '1');
                    }
                    else if($request->input('status') == '2')
                    {
                        $query->where('users.status', '0');
                    }
                }

            })
            ->make(true);
    }

    function fetch_refresh_popup_seen_users(Request $request){
        if (!in_array(auth()->user()->group_id, [1, 2])) {
            AppHelper::logger('warning', 'Users', auth()->user()->username . ' unauthorized refresh popup seen users fetch attempt');
            return response()->json([
                'success' => false,
                'message' => trans('common.access_violation')
            ], 403);
        }

        $oneWeekAgo = Carbon::now()->subDays(7);

        $query = User::join('user_groups', 'user_groups.id', 'users.group_id')
            ->leftJoin('users as parent_users', 'parent_users.id', '=', 'users.parent_id')
            ->select([
                'users.username',
                'users.status',
                'users.group_id',
                'users.last_activity',
                'users.tt_v2_refresh_popup_seen',
                'users.parent_id',
                DB::raw('parent_users.username as parent_name'),
                DB::raw('parent_users.status as parent_status'),
            ])
            ->where('users.tt_v2_refresh_popup_seen','!=', 1)
            ->where('users.group_id', 4)
            ->where('users.status', 1)
            ->whereNotNull('parent_users.id')
            ->where('parent_users.status', 1)
            ->whereNotNull('users.last_activity')
            ->where('users.last_activity', '>=', $oneWeekAgo)
            ->orderBy('parent_users.username', 'ASC')
            ->orderBy('users.username', 'ASC');

        $orders = $query;
        return Datatables::of($orders)
            ->addIndexColumn()
            ->addColumn('parent_name', function ($users) {
                return !empty($users->parent_name) ? $users->parent_name : '-';
            })
            ->addColumn('parent_status', function ($users) {
                return (int) $users->parent_status === 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('status', function ($users) {
                if ($users->tt_v2_refresh_popup_seen == 1) {
                    return "Clicked";
                }
                return "Not Clicked";
            })
            ->make(true);
    }
}











