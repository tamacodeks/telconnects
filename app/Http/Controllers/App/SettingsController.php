<?php

namespace App\Http\Controllers\App;

use app\Library\AppHelper;
use App\Models\Currency;
use App\Models\Banner;
use DateTimeZone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;

class SettingsController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function app_settings(){

        $page_data = [
            'page_title' => "Manage Application Settings",
            'currencies' => collect(Currency::all())->pluck('title','code'),
            'timezones' => DateTimeZone::listIdentifiers(DateTimeZone::ALL)
        ];
        return view('app.settings.app_settings',$page_data);
    }

    function save(Request $request){
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'app_name' => 'required'
        ]);
        if ($validator->fails()) {
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message',$html)
                ->with('message_type','warning');
        }
        $app_name = $request->app_name;
        $app_logo= 'logo_inverse.png';
        if($request->hasFile('app_logo')){
            $file = $request->file('app_logo');
            //Move Uploaded File
            $destinationPath = 'images/';
            $app_logo = 'logo_inverse.png';
            $file->move($destinationPath,$app_logo);
        }
        $enable_multi_lang = !empty($request->enable_multi_lang) ? 1 : 0;
        $enable_email = !empty($request->enable_email) ? 1 : 0;
        $enable_slack = !empty($request->enable_slack) ? 1 : 0;
        $def_currency = $request->app_currency;
        $app_lang = $request->app_lang;
        $app_timezone = $request->app_timezone;
        $per_page = $request->per_page;
        $record_order = $request->record_order;
        $record_method = $request->record_method;
        $payment_emails = $request->payment_emails;
        $order_prefix = $request->order_prefix;
        $transaction_prefix = $request->transaction_prefix;
        $comcod = $request->comcod;
        $tpvcod = $request->tpvcod;
        $authorization = $request->authorization;
        $api_token = !empty($request->api_token) ? $request->api_token : "";
        $manager_limit = $request->manager_limit;
        $admin_limit = $request->admin_limit;
        $api_end_point = !empty($request->api_end_point) ? $request->api_end_point : "";
        $requestedBusDesign = $request->input('bus_v2_design', 'standard');
        $bus_v2_design = in_array($requestedBusDesign, ['standard', 'desk'], true) ? $requestedBusDesign : 'standard';
        $val = "<?php \n";
        $val .= "/**
 * Created by TAMAEXPRESS
 * Developer: Syed Khalid T
 * Date: ".date("Y-m-d H:i:s")."
 */\n";
        $val .= "define('APP_NAME','" . $app_name . "');\n";
        $val .= "define('APP_LOGO','" . $app_logo . "');\n";
        $val .= "define('ENABLE_MULTI_LANG','" . $enable_multi_lang . "');\n";
        $val .= "define('DEFAULT_CURRENCY','" . $def_currency . "');\n";
        $val .= "define('DEFAULT_LANG','" . $app_lang . "');\n";
        $val .= "define('DEFAULT_TIMEZONE','" . $app_timezone . "');\n";
        $val .= "define('PER_PAGE','" . $per_page . "');\n";
        $val .= "define('RECORD_ORDER_BY','" . $record_order . "');\n";
        $val .= "define('ENABLE_EMAIL','" . $enable_email . "');\n";
        $val .= "define('ENABLE_SLACK','" . $enable_slack . "');\n";
        $val .= "define('DEFAULT_RECORD_METHOD','" . $record_method . "');\n";
        $val .= "define('PAYMENT_EMAILS','" . $payment_emails . "');\n";
        $val .= "define('ORDER_PREFIX','" . $order_prefix . "');\n";
        $val .= "define('TRANSACTION_PREFIX','" . $transaction_prefix . "');\n";
        $val .= "define('ADMIN_LIMIT','" . $admin_limit . "');\n";
        $val .= "define('MANAGER_LIMIT','" . $manager_limit . "');\n";
        $val .= "define('API_TOKEN','" . $api_token . "');\n";
        $val .= "define('API_END_POINT','" . $api_end_point . "');\n";
        $val .= "define('BUS_V2_DESIGN','" . $bus_v2_design . "');\n";
        $val .= "define('COMCOD','" . $comcod . "');\n";
        $val .= "define('TPVCOD','" . $tpvcod . "');\n";
        $val .= "define('AUTHORIZATION','" . $authorization . "');\n";
        $val .= "?>";
        $filename = base_path() . '/settings.php';
        $fp = fopen($filename, "w+");
        fwrite($fp, $val);
        fclose($fp);
        Log::emergency(APP_NAME.' Application Settings were updated');
        AppHelper::logger('success','Settings Update',"Application settings were updated");
        $clearCache = \Artisan::call("cache:clear");
        $configCache = \Artisan::call("config:cache");
        return redirect()->back()->with('message',trans('common.msg_update_success'))->with('message_type','success');
    }
    function all_banners()
    {
        $query = Banner::join('users', 'users.id', 'banners.user_id')
            ->select([
                'banners.user_id as id',
                'users.username',
                \DB::raw('count(banners.user_id) AS total_banner'),
            ])
            ->groupby('users.id')
            ->get();
        $query1 = Banner::
        select([
            'user_id as id',
            \DB::raw('count(banners.user_id) AS total_banner'),
        ])
            ->whereDate('to_date', '>=', date("Y-m-d"))
            ->groupby('user_id')
            ->get();
        $page_data = [
            'page_title' => "Manage Banners",
            'add_title' => "Add Banners",
            'banners' => $query,
            'active' => $query1,
            'row' => ''
        ];
        AppHelper::logger('info', 'Banners view', "Viewing all Banners");
        return view('app.settings.view', $page_data);
    }
    function banner()
    {
        $query = Banner::join('users', 'users.id', 'banners.user_id')
            ->select([
                'banners.id as id',
                'users.username',
                'banners.title',
                'banners.from_date',
                'banners.to_date',
                'banners.banner',
            ])->where('banners.user_id', '=', auth()->user()->id)
            ->get();
        $page_data = [
            'page_title' => "Manage Banners",
            'add_title' => "Add Banners",
            'banners' => $query,
            'row' => ''
        ];
        AppHelper::logger('info', 'Banners view', "Viewing all Banners");
        return view('app.settings.index', $page_data);
    }
    function view_user_banner($id)
    {
        $query = Banner::join('users', 'users.id', 'banners.user_id')
            ->select([
                'banners.id as id',
                'users.username',
                'banners.title',
                'banners.from_date',
                'banners.to_date',
                'banners.banner',
            ])->where('banners.user_id', '=', $id)
            ->get();
        $page_data = [
            'page_title' => "Users Banners",
            'banners' => $query,
        ];
        AppHelper::logger('info', 'Banners view', "Viewing all Banners");
        return view('app.settings.view_user_banner', $page_data);
    }
    function edit($id)
    {
        $query = Banner::join('users', 'users.id', 'banners.user_id')
            ->select([
                'banners.id as id',
                'users.username',
                'banners.title',
                'banners.from_date',
                'banners.to_date',
                'banners.banner',
            ])->where('banners.user_id', '=', auth()->user()->id)
            ->get();
        $data = Banner::where('id', '=', $id)->first();
        $page_data = [
            'page_title' => "Manage Banners",
            'add_title' => "Edit Banners",
            'banners' => $query,
            'row' => $data
        ];
        AppHelper::logger('info', 'Banners view', "Viewing all Banners");
        return view('app.settings.index', $page_data);
    }
    function add(Request $request){

        if($request->id == 0){
            $get_banner =Banner::whereIn('user_id', [1,auth()->user()->id])->count();
            $limit = MANAGER_LIMIT  ;
            if(auth()->user()->group_id == 1 || auth()->user()->group_id == 2)
            {
                $limit = ADMIN_LIMIT ;
            }
            if($get_banner <= $limit)
            {
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $name = auth()->user()->username . time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/images/banner');
                    $image->move($destinationPath, $name);
                }
                $setting = new Banner();
                $setting->title = $request->title;
                $setting->user_id = auth()->user()->id;
                $setting->from_date = $request->from;
                $setting->to_date = $request->to;
                $setting->banner = 'banner/'.$name;
                $setting->created_at = date("Y-m-d H:i:s");
                $setting->created_by = auth()->user()->id;
                $setting->save();
                AppHelper::logger('success',"Banner Added","Banner has been Added Successfully");
                return redirect()->back()->with('message',trans('common.msg_add_success'))->with('message_type','success');
            }else{
                AppHelper::logger('warning',"Limit Exceed","Banner Limit  has been Exceed");
                return redirect()->back()->with('message',trans('common.msg_add_error'))->with('message_type','warning');
            }
        }else{
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name = auth()->user()->username . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/images/banner');
                $image->move($destinationPath, $name);
                $save_image = 'banner/'.$name;
            }else{
                $get =Banner::where('id', $request->id)->first();
                $save_image = $get['banner'];
            }
            Banner::where('id', $request->id)
                ->update([
                    'title' => $request->title,
                    'from_date' => $request->from,
                    'to_date' => $request->to,
                    'banner' => $save_image,
                    'updated_at' =>date("Y-m-d H:i:s"),
                    'updated_by' =>  auth()->user()->id,
                ]);
            AppHelper::logger('success',"Banner Updated","Banner has been Updated Successfully");
            return redirect()->back()->with('message',trans('common.msg_update_success'))->with('message_type','success');
        }
    }

    function delete($id)
    {
        $banner = Banner::find($id);
        $banner->delete();
        AppHelper::logger('success',"Banner Deleted","Banner ID $id has been deleted");
        return redirect()->back()->with('message',trans('common.msg_remove_success'))
            ->with('message_type','success');
    }
}
