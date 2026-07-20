<?php
/**
 * Created by Decipher Lab.
 * User: Prabakar
 * Date: 03-Apr-18
 * Time: 11:38 AM
 */

namespace app\Library;


use App\Http\Controllers\Api\DematSoapController;
use App\Http\Controllers\Api\DematSoapBimediaController;
use App\Models\AledaStatistic;
use App\Models\CreditLimit;
use App\Models\Currency;
use App\Models\Log;
use App\Models\Notification;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\UserAccess;
use App\Models\DailyLimit;
use App\Models\Setting;
use App\Support\TwilioSms;
use App\Support\WaOtp;
use App\Models\UserGroup;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use App\Models\CallingCardTransaction;


use Illuminate\Support\Facades\Log as Logeer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AppHelper
{
    public $currencies = [];
    public static function iplocation($ip)
    {
        if (empty($ip)) {
            return [];
        }

        $cacheKey = "geoip:{$ip}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ip) {
            $client = new Client([
                'timeout' => 3.0,
                'http_errors' => false,
            ]);

            $providers = [
                [
                    'name' => 'freegeoip',
                    'url' => 'https://api.freegeoip.app/json/' . $ip . '?apikey=' . (config('services.freegeoip.key') ?? '96860830-45f7-11ec-9715-eba311858aa6'),
                    'map' => function (array $json) use ($ip) {
                        return [
                            'country_code' => $json['country_code'] ?? null,
                            'region_name' => $json['region_name'] ?? ($json['region'] ?? null),
                            'city' => $json['city'] ?? null,
                            'ip' => $json['ip'] ?? $ip,
                        ];
                    },
                ],
                [
                    'name' => 'ip-api',
                    'url' => 'http://ip-api.com/json/' . $ip . '?fields=status,countryCode,regionName,city,query,message',
                    'map' => function (array $json) use ($ip) {
                        if (($json['status'] ?? 'fail') !== 'success') {
                            throw new \RuntimeException('ip-api fail: ' . ($json['message'] ?? 'unknown'));
                        }

                        return [
                            'country_code' => $json['countryCode'] ?? null,
                            'region_name' => $json['regionName'] ?? null,
                            'city' => $json['city'] ?? null,
                            'ip' => $json['query'] ?? $ip,
                        ];
                    },
                ],
                [
                    'name' => 'geoplugin',
                    'url' => 'http://www.geoplugin.net/json.gp?ip=' . $ip,
                    'map' => function (array $json) use ($ip) {
                        return [
                            'country_code' => $json['geoplugin_countryCode'] ?? null,
                            'region_name' => $json['geoplugin_region'] ?? ($json['geoplugin_regionName'] ?? null),
                            'city' => $json['geoplugin_city'] ?? null,
                            'ip' => $ip,
                        ];
                    },
                ],
            ];

            foreach ($providers as $provider) {
                try {
                    $response = $client->get($provider['url']);
                    $status = $response->getStatusCode();

                    if ($status < 200 || $status >= 300) {
                        Logeer::warning("GeoIP provider {$provider['name']} HTTP {$status}", ['ip' => $ip]);
                        continue;
                    }

                    $json = json_decode((string) $response->getBody(), true);
                    if (!is_array($json)) {
                        Logeer::warning("GeoIP provider {$provider['name']} invalid JSON", ['ip' => $ip]);
                        continue;
                    }

                    $mapped = ($provider['map'])($json);

                    if (!empty($mapped['country_code']) || !empty($mapped['region_name']) || !empty($mapped['city'])) {
                        $payload = $mapped + ['provider' => $provider['name']];
                        Logeer::info("GeoIP success via {$provider['name']}", ['ip' => $ip, 'data' => $payload]);

                        return $payload;
                    }

                    Logeer::warning("GeoIP provider {$provider['name']} returned empty mapping", ['ip' => $ip, 'raw' => $json]);
                } catch (\Throwable $e) {
                    Logeer::warning("GeoIP provider {$provider['name']} error: {$e->getMessage()}", ['ip' => $ip]);
                }
            }

            Logeer::error("GeoIP lookup failed for {$ip}");

            return [
                'country_code' => null,
                'region_name' => null,
                'city' => null,
                'ip' => $ip,
                'provider' => null,
            ];
        });
    }

    /**
     * Return User Original IP
     * @param null $ip
     * @param bool $deep_detect
     * @return null
     */
    static public function getIP($deep_detect = true){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }

    /**
     * Trim text with ...
     * @param $input
     * @param $length
     * @param bool $ellipses
     * @param bool $strip_html
     * @return bool|string
     */
    static function doTrim_text($input, $length, $ellipses = true, $strip_html = true)
    {
        //strip tags, if desired
        if ($strip_html) {
            $input = strip_tags($input);
        }

        //no need to trim, already shorter than trim length
        if (strlen($input) <= $length) {
            return $input;
        }

        //find last space within length
        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space);

        //add ellipses (...)
        if ($ellipses) {
            $trimmed_text .= '...';
        }

        return $trimmed_text;
    }

    /**
     * Log application activities
     * @param string $type
     * @param string $title
     * @param string $desc
     * @param array $request
     * @param bool $is_api
     */
    static function logger($type = '', $title = '', $desc = '', $request = [], $is_api = false)
    {
        Log::insert([
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'type' => $type,
            'title' => $title,
            'description' => $desc,
            'uri' => request()->getUri(),
            'ip' => self::getIP(),
            'request_info' => json_encode($request),
            'is_api' => $is_api == true ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => auth()->check() ? auth()->user()->id : null
        ]);
    }

    /**
     * Render aside
     * @param string $position
     * @param string $active
     * @param string $group_id
     * @return array
     */
    public static function menus($position = 'sidebar', $active = '1', $group_id = '', $edit = false)
    {
        $data = array();
        $menu = self::nestedMenu(0, $position, $active, $group_id, $edit);
        foreach ($menu as $row) {
            $child_level = array();
            $menus2 = self::nestedMenu($row->id, $position, $active, $group_id, $edit);
            if (count($menus2) > 0) {
                $level2 = array();
                foreach ($menus2 as $row2) {
                    if (self::skip_service_as_menu($row2->url)) {
                        $menu2 = array(
                            'id' => $row2->id,
                            'url' => $row2->url,
                            'name' => $row2->name,
                            'trans_lang' => json_decode($row2->trans_lang, true),
                            'icon' => $row2->icon,
                            'section' => isset($row2->section) ? $row2->section : null,
                            'childs' => array()
                        );
                        $menus3 = self::nestedMenu($row2->id, $position, $active, $group_id, $edit);
                        if (count($menus3) > 0) {
                            $child_level_3 = array();
                            foreach ($menus3 as $row3) {
                                $menu3 = array(
                                    'id' => $row3->id,
                                    'url' => $row3->url,
                                    'name' => $row3->name,
                                    'trans_lang' => json_decode($row3->trans_lang, true),
                                    'icon' => $row3->icon,
                                    'section' => isset($row3->section) ? $row3->section : null,
                                    'childs' => array()
                                );
                                $child_level_3[] = $menu3;
                            }
                            $menu2['childs'] = $child_level_3;
                        }
                        $level2[] = $menu2;
                    }
                }
                $child_level = $level2;
            }
            $level = array(
                'id' => $row->id,
                'url' => $row->url,
                'name' => $row->name,
                'trans_lang' => json_decode($row->trans_lang, true),
                'icon' => $row->icon,
                'section' => isset($row->section) ? $row->section : null,
                'childs' => $child_level
            );
            $data[] = $level;
        }
//        echo '<pre>';print_r($data); echo '</pre>'; exit;
        return $data;
    }

    /**
     * Render aside query
     * @param int $parent
     * @param string $position
     * @param string $active
     * @param $group_id
     * @return mixed
     */
    public static function nestedMenu($parent = 0, $position = 'sidebar', $active = '1', $group_id, $edit = false)
    {
        if ($edit == true) {
            return DB::select(" SELECT  menus.* FROM menus WHERE parent_id ='" . $parent . "' " . $active . " AND position ='{$position}' AND group_id = '{$group_id}' GROUP BY menus.id ORDER BY ordering");
        }
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s,%s,%s,%s", [
            $parent,
            $position,
            $active,
            $group_id
        ]));
//        \Cache::forget($cacheKey);
        $active = ($active == 'all' ? "" : "AND status ='1' ");
        return \Cache::remember($cacheKey, $minutes, function () use ($parent, $position, $group_id, $active) {
            return DB::select(" SELECT  menus.* FROM menus WHERE parent_id ='" . $parent . "' " . $active . " AND position ='{$position}' AND group_id = '{$group_id}' GROUP BY menus.id ORDER BY ordering");
        });
    }

    static function skip_service_as_menu($service_name)
    {
        $accessCacheKey = md5(vsprintf("%s,%s", [
            auth()->user()->id,
            $service_name
        ]));
//        \Cache::forget($accessCacheKey);
        $check = \Cache::remember($accessCacheKey,config('constants.cache_expire_in'), function () use ($service_name) {
            return \DB::select("select * from `user_access` inner join `services` on `services`.`id` = `user_access`.`service_id` where `services`.`name` like '%".ucwords(str_replace('-', ' ', $service_name))."%' and `user_access`.`user_id` = ".auth()->user()->id." and `user_access`.`status` = 1 limit 1 ");
        });
        if(in_array(auth()->user()->group_id,[1])) return true;
        if(in_array(auth()->user()->group_id,[2])){
            $service_accessCache = md5(vsprintf("%s,%s,%s,%s", [
                $service_name,auth()->user()->id,auth()->user()->username,1
            ]));
            $check_service = \Cache::remember($service_accessCache,config('constants.cache_expire_in'),function () use ($service_name){
                return \DB::select("select * from services where name like '%".ucwords(str_replace('-', ' ', $service_name))."%' and status=1");
            });
            if(collect($check_service)->count() > 0){
                return true;
            }else{
                $service_accessCache2 = md5(vsprintf("%s,%s,%s,%s", [
                    $service_name,auth()->user()->id,auth()->user()->username,2
                ]));
                $check_service_ws = \Cache::remember($service_accessCache2,config('constants.cache_expire_in'),function () use($service_name){
                    return \DB::select("select * from services where name like '%".ucwords(str_replace('-', ' ', $service_name))."%'");
                });
                if(collect($check_service_ws)->count() > 0){
                    return false;
                }else{
                    return true;
                }
            }
        }
        if ($check) {
            if(auth()->user()->group_id == 3){
                $service_accessCache3 = md5(vsprintf("%s,%s,%s,%s", [
                    $service_name,auth()->user()->id,auth()->user()->username,3
                ]));
                $check_service_parent = \Cache::remember($service_accessCache3,config('constants.cache_expire_in'),function () use($service_name){
                    return \DB::select("select * from services where name like '%".ucwords(str_replace('-', ' ', $service_name))."%' and status=1");
                });
                if(collect($check_service_parent)->count() > 0){
                    return true;
                }else{
                    return false;
                }
            }elseif(auth()->user()->group_id == 4){
                $service_accessCache4 = md5(vsprintf("%s,%s,%s,%s", [
                    $service_name,auth()->user()->id,auth()->user()->username,4
                ]));
                $check_parent_user = \Cache::remember($service_accessCache4,config('constants.cache_expire_in'),function () use($service_name){
                    return \DB::select("select * from `user_access` inner join `services` on `services`.`id` = `user_access`.`service_id` where `services`.`name` like '%".ucwords(str_replace('-', ' ', $service_name))."%' and `user_access`.`user_id` = ".auth()->user()->parent_id." and `user_access`.`status` = 1 limit 1");
                });
                if(collect($check_parent_user)->count() > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }else{
            $service_accessCache5 = md5(vsprintf("%s,%s,%s,%s", [
                $service_name,auth()->user()->id,auth()->user()->username,5
            ]));
            $check_service_final = \Cache::remember($service_accessCache5,config('constants.cache_expire_in'),function () use($service_name){
                return \DB::select("select * from services where name like '%".ucwords(str_replace('-', ' ', $service_name))."%'");
            });
            if(collect($check_service_final)->count() > 0){
                return false;
            }else {
                return true;
            }
        }
    }

    static function getAdminBalance($format=true,$get_credit_limit=false){
        $client = new Client([
            'base_uri' => API_END_POINT,
            'timeout'  => 5,
        ]);
        try{
            $response = $client->request('GET', 'balance',[
                'headers'  => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer ".API_TOKEN
                ]
            ]);
            if($response->getStatusCode() == 200){
                $data = json_decode((string)$response->getBody(),true);
                if($get_credit_limit == true){
                    return $data['data']['credit_limit'];
                }
                if($format == false){
                    return $data['data']['ws_balance'];
                }
                return $data['data']['balance'];
            }else{
                return self::formatAmount('EUR',"0.00");
            }
        }catch (\Exception $e)
        {
            \Illuminate\Support\Facades\Log::warning("Tama Retailer Balance Exception ".$e->getMessage());
            return self::formatAmount('EUR',"0.00");
        }
    }

    /**
     * Get user balance
     * @param $user_id
     * @param $iso_code
     * @param bool $format
     * @return string
     */
    static function Correction_balance($user_id, $date)
    {
        $today_date = date("Y-m-d");
        $array = \App\Models\Transaction::where('user_id', $user_id)
            ->select('id', 'prev_bal', 'balance', 'debit', 'credit','type')
            ->where('is_corection', 0)
            ->whereBetween('date', [$today_date . " 00:00:00", $today_date . " 23:59:59"])
            ->orderBy('id', 'ASC')->get();
        $length = count($array);
        for ($i = 0; $i < $length - 1; $i++) {
            $array[$i + 1]->prev_bal = $array[$i]->balance;
            if($array[$i + 1]->type == 'credit'){
                $array[$i + 1]->balance = $array[$i + 1]->prev_bal + $array[$i + 1]->credit;
            }
            else{
                $array[$i + 1]->balance = $array[$i + 1]->prev_bal - $array[$i + 1]->debit;
            }

            \App\Models\Transaction::where('id', $array[$i + 1]->id)
                ->where('is_corection', 0)
                ->update([
                    'prev_bal' => $array[$i + 1]->prev_bal,
                    'balance' => $array[$i + 1]->balance,
                    'is_corection' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            \App\Models\Transaction::where('id', $array[$i]->id)
                ->where('is_corection', 0)
                ->update([
                    'is_corection' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }
    static function getBalance($user_id, $iso_code="EUR", $format = true)
    {
        self::setCurrency();
        self::Correction_balance($user_id, date("Y-m-d"));
        $string = '';
        $decimalPlace = null;
        $precision = null;
        $result = \App\Models\Transaction::where('user_id', $user_id)->orderBy('id', 'DESC')->first();
        if (!$result) {
            return '0.00';
        } else {
            $amount = $result->balance;
        }
        if ($format == false) {
            return $amount;
        }
        return self::formatAmount($iso_code, $amount);
    }

    /**
     * Calculate the day between date range
     * @param $dt1
     * @param $dt2
     * @return string
     */
    static function daysBetween($dt1, $dt2)
    {
        return date_diff(
            date_create($dt2),
            date_create($dt1)
        )->format('%a');
    }

    /**
     * Indicate the user status
     * @param $last_activity
     * @return string
     */
    static function status_indicator($last_activity)
    {
        $usr_last_activity = self::daysBetween(date("Y-m-d H:i:s"), $last_activity);
        switch ($usr_last_activity) {
            case $usr_last_activity === 0:
                $user_status = "<span class='dot dot-active' data-toggle=\"tooltip\" title=\"Active\"></span>";
                break;
            case $usr_last_activity < 3:
                $user_status = "<span class='dot dot-mid-in-active' data-toggle=\"tooltip\" title=\"Middle In active\"></span>";
                break;
            case $usr_last_activity > 5:
                $user_status = "<span class='dot dot-in-active' data-toggle=\"tooltip\" title=\"In active\"></span>";
                break;
            default:
                $user_status = "<span class='dot dot-active' data-toggle=\"tooltip\" title=\"Active\"></span>";
        }
        return $user_status;
    }

    /**
     * label helper
     * @param string $type
     * @return string
     */
    static function message_types($type = '')
    {
        return strtr($type, [
            'info' => 'primary',
            'warning' => 'danger',
            'danger' => 'danger',
            'success' => 'success',
        ]);
    }

    static function prettyPrint($json)
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if ($ends_line_level !== NULL) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ($in_escape) {
                $in_escape = false;
            } else if ($char === '"') {
                $in_quotes = !$in_quotes;
            } else if (!$in_quotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ($char === '\\') {
                $in_escape = true;
            }
            if ($new_line_level !== NULL) {
                $result .= "\n" . str_repeat("\t", $new_line_level);
            }
            $result .= $char . $post;
        }

        return $result;
    }

    /**
     * User Level Group to add user
     * @return mixed
     */
    static function render_user_group()
    {
        $user_group_access = optional(UserGroup::find(auth()->user()->group_id))->level_access;
        $all_user_groups = UserGroup::select('id', 'name')->whereIn('id', explode(',', $user_group_access))->get();
        return $all_user_groups;
    }

    /**
     * Render parent User's
     * @return mixed
     */
    static function render_parent_manager()
    {
        $query = User::where('group_id', 3);
        if (auth()->user()->group_id == 3) {
            $query->whereIn('id', [auth()->user()->id]);
        }
        return $query->select('id', 'username')->get();
    }
    /**
     * Get User's
     * @return mixed
     */
    static function findusername($id)
    {
        $query = User::where('id', $id);
        return $query->select('username')->first();
    }
    /**
     * get the table column as array
     * @param $table
     * @return array
     */
    static function renderColumns($table)
    {
        $query = "SHOW columns FROM `{$table}`";
        $columns = [];
        foreach (\DB::select($query) as $key) {
            $columns[$key->Field] = '';
        }
        return $columns;
    }

    /**
     * Get User Credit Limit
     * @param $user_id
     * @return mixed
     */
    static function get_credit_limit($user_id)
    {
        return optional(CreditLimit::where('user_id', $user_id)->first())->credit_limit;
    }
    static function get_daily_limit($user_id)
    {
        return optional(DailyLimit::where('user_id', $user_id)->orderBy('user_id', 'DESC')->first())->daily_limit;
    }
    static function get_remaning_limit_balance($user_id)
    {
        $from = date("Y-m-d  00:00:00.000000'");
        $to = date("Y-m-d  23:59:59.999999'");
        $check_limit = DailyLimit::where('user_id', $user_id)->first();
        if (!empty($check_limit)) {
            $daily_limit = DailyLimit::where('user_id', $user_id)->first()->daily_limit;
        } else {
            $daily_limit = 0;
        }
        $result = \App\Models\Transaction::where('user_id', $user_id)->where('type','debit')->whereBetween('created_at', [$from, $to])->orderBy('id', 'DESC')->get();
        if (!$result) {
            if($daily_limit == 0)
            {
                return 0;
            } else {

                return $daily_limit;
            }
        } else {


            if($daily_limit == 0)
            {
                return 0;
            } else {

                return $amount = $result->sum('amount') - $daily_limit;
            }
        }
    }
    /**
     * Check whether user have access to the service
     * @param $service_id
     * @param $user_id
     * @return int
     */
    static function user_access($service_id, $user_id)
    {
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s,%s,%s", [
            $service_id,
            $user_id,
            'ua'
        ]));
//        \Cache::forget($cacheKey);
        $ua =  \Cache::remember($cacheKey, $minutes, function () use ($service_id, $user_id) {
            return DB::select(" SELECT  user_access.* FROM user_access WHERE service_id ='" . $service_id . "' AND user_id ='{$user_id}'");
        });
        if ($ua) {
            return $ua[0]->status;
        }
        return 0;
    }

    /**
     * Symbolize amount
     * @param string $iso_code
     * @param $amount
     * @return string
     */
    public static function formatAmount($iso_code = 'EUR', $amount)
    {
        $string = '';
        $decimalPlace = null;
        $precision = null;
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s,%s", [
            $iso_code,'fa'
        ]));
//        \Cache::forget($cacheKey);
        $getCurrencyConfigTmp = \Cache::remember($cacheKey, $minutes, function () use ($iso_code) {
            return \DB::select("select * from currencies where code='".$iso_code."' limit 1");
        });
        $getCurrencyConfig = $getCurrencyConfigTmp[0];
        $symbolLeft = $getCurrencyConfig->symbol_left;
        $symbolRight = $getCurrencyConfig->symbol_right;
        if (is_null($decimalPlace)) {
            $decimalPlace = $getCurrencyConfig->decimal_place;
        }
        $decimalPoint = $getCurrencyConfig->decimal_point;
        $thousandPoint = $getCurrencyConfig->thousand_point;
        if (!empty($symbolLeft)) {
            $string .= $symbolLeft;
            if (\Config::get('currency.use_space')) {
                $string .= ' ';
            }
        }
        if ($precision == null) {
            $precision = (int)$decimalPlace;
        }
        $string .= number_format(round($amount, (int)$precision), (int)$decimalPlace, $decimalPoint, $thousandPoint);
        if (!empty($symbolRight)) {
            if (\Config::get('currency.use_space')) {
                $string .= ' ';
            }
            $string .= $symbolRight;
        }
        return $string;
    }

    /**
     * create random cust_id
     * @param int $length
     * @return string
     */
    static function generateCustomerID($length = 5)
    {
        do {
            $digits = '';
            $numbers = range(0, 9);
            shuffle($numbers);
            for ($i = 0; $i < $length; $i++)
                $digits .= $numbers[$i];
            $cust_id = $digits;
        } while (!empty(\App\User::where('cust_id', $cust_id)->first()));
        return $cust_id;
    }

    /**
     * Check TamaTopup Configuration for current user
     * @param $phonecode
     * @param $mobile
     * @return bool
     */
    static function checkConfigTamaTopup($phonecode, $mobile, $user_id)
    {
        if (!$user_id) {
            return false;
        }
        $country = \App\Models\Country::where('phonecode', $phonecode)->first();
        if (!$country) {
            return false;
        }
        $country_id = $country->id;
//        exit;
        //lets check the country have a configuration for service id 2
        $cc = \App\Models\ServiceConfig::where('service_id', 2)->where('country_id', $country_id)->first();
        if (!$cc) {
            return false;
        }
        $no_prefix = substr($mobile, 0, $cc->count_prefix);
        $tele_prefix = explode(',', $cc->tel_prefix);
        if (in_array($no_prefix, $tele_prefix)) {
            //check user have configured
            $cf_data = json_decode($cc->config_data, true);
            $cf_data_tmp = $cf_data['users'];
            if (in_array($user_id, $cf_data_tmp)) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * If above configuration return true, get packs for the country we configure
     * @param $phonecode
     * @param $mobile
     * @return bool|\Illuminate\Support\Collection
     */
    static function getTamaTopupPacks($phonecode, $mobile)
    {
        $country = \App\Models\Country::where('phonecode', $phonecode)->first();
        if (!$country) {
            return false;
        }
        $country_id = $country->id;
        //lets check the country have a configuration for service id 2
        $cc = \App\Models\ServiceConfig::where('service_id', 2)->where('country_id', $country_id)->first();
        if (!$cc) {
            return false;
        }
        $no_prefix = substr($mobile, 0, $cc->count_prefix);
        $tele_prefix = explode(',', $cc->tel_prefix);
        if (in_array($no_prefix, $tele_prefix)) {
            //check user have configured
            $cf_data = json_decode($cc->config_data, true);
            $cf_data_tmp = $cf_data['users'];
            if (in_array(\Auth::user()->id, $cf_data_tmp)) {
                $tpacks = \App\Models\TamaTopupPacks::where('country_id', $country_id)->where('is_active', '1')->orderBy('dest_value', 'asc')->get();
                if (!$tpacks) {
                    return false;
                }
                return $tpacks;
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Return Json response for AJAX and API Requests
     * @param $code
     * @param $http_code
     * @param $message
     * @param array $arr
     * @return \Illuminate\Http\JsonResponse
     */
    static function response($code, $http_code, $message, $arr = [])
    {
        $resp = (object)array(
            'data' => array(
                'code' => $code,
                'http_code' => $http_code,
                'message' => $message,
                'result' => $arr
            )
        );
        $headers = array(
            'Content-Type: application/json'
        );
        return response()->json($resp, $http_code, $headers);
    }

    /**
     * to check currency
     * @param $currency
     * @return bool
     */
    public static function hasCurrency($currency)
    {
        $currencies = \Cache::get('currency');
        return isset($currencies[$currency]);
    }

    /**
     * Cache currency
     */
    static function setCurrency()
    {
        return \Cache::rememberForever('currency', function () {
            $cache = [];
            $tableName = 'currencies';
            foreach (Currency::all() as $currency) {
                $cache[$currency->code] = [
                    'id' => $currency->id,
                    'title' => $currency->title,
                    'symbol_left' => $currency->symbol_left,
                    'symbol_right' => $currency->symbol_right,
                    'decimal_place' => $currency->decimal_place,
                    'value' => $currency->value,
                    'decimal_point' => $currency->decimal_point,
                    'thousand_point' => $currency->thousand_point,
                    'code' => $currency->code,
                ];
            }
            return $cache;
        });
    }

    /**
     * Get Currency
     * @param string $currency
     * @return mixed
     */
    public static function getCurrency($currency = '')
    {
//        \Cache::forget('currency')
        $currencies = \Cache::get('currency');
        if ($currency && self::hasCurrency($currency)) {
            return $currencies[$currency];
        } else {
            return $currencies['EUR'];
        }
    }

    /**
     * Convert currency from one to another
     * @param $number
     * @param $fromCurrencyCode
     * @param $toCurrencyCode
     * @return float
     */
    public static function convert($number, $fromCurrencyCode, $toCurrencyCode)
    {
        $fromCurrency = self::getCurrency($fromCurrencyCode);
        $toCurrency = self::getCurrency($toCurrencyCode);
//        dd($fromCurrency);
        return number_format($number / $fromCurrency['value'] * $toCurrency['value'],2);
    }

    /**
     * Create error list
     * @param $validator
     * @return string
     */
    static function create_error_bag($validator)
    {
        $html = "<h4>" . trans('common.msg_fill_fields') . "</h4><ul>";
        foreach ($validator->messages()->getMessages() as $field_name => $messages) {
            foreach ($messages as $message) {
                $html .= "<li>" . $message . "</li>";
            }
        }
        $html .= "</ul>";
        return $html;
    }

    public static function Numeric($length)
    {
        $chars = "1234567890";
        $clen = strlen($chars) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[mt_rand(0, $clen)];
        }
        return ($id);
    }

    public static function Alphabets($length)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $clen = strlen($chars) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[mt_rand(0, $clen)];
        }
        return ($id);
    }

    static function _format_json($json, $html = false)
    {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;
        if ($html) {
            $tab = "&nbsp;&nbsp;&nbsp;";
            $newline = "<br/>";
        } else {
            $tab = "\t";
            $newline = "\n";
        }
        for ($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];
            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch ($char) {
                    case '{':
                        $tabcount++;
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '}':
                        $tabcount--;
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        break;
                    case ',':
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) $ignorenext = true;
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }
        return $result;
    }

    public static function AlphaNumeric($length)
    {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $clen = strlen($chars) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[mt_rand(0, $clen)];
        }
        return ($id);
    }

    static function getMyServiceBalance($user_id, $iso_code, $format = true)
    {
        self::setCurrency();
        $string = '';
        $decimalPlace = null;
        $precision = null;
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s,%s,%s", [
            $user_id,$iso_code,"calling-cards"
        ]));
        \Cache::forget($cacheKey);
        $result = \Cache::remember($cacheKey, $minutes, function () use ($user_id) {
            return CallingCardTransaction::select('balance')
                ->lockforUpdate()
                ->where('user_id', $user_id)
                ->orderBy('id',"DESC")
                ->get();
        });
        if (!$result || sizeof($result) == 0) {
            return '0.00';
        } else {
            $amount_tmp = array_pluck($result, 'balance');
            $amount = $amount_tmp[0];
        }
        if ($format == false) {
            return $amount;
        }
        return self::formatAmount($iso_code, $amount);
    }

    static function excel_to_datetime($date){
        $old_date_timestamp = strtotime($date);
        return date('Y-m-d H:i:s', $old_date_timestamp);
    }

    static function notification_type($type,$icon=false){
        $icon_array = [
            'success' => "fa fa-check-circle",
            'warning' => "fa fa-exclamation-circle",
            'danger' => "fa fa-exclamation-triangle",
            'information' => "fa fa-info-circle",
        ];
        $type_array = [
            'success' => "dark",
            'warning' => "red",
            'danger' => "red",
            'information' => "blue"
        ];
        if($icon == true){
            return $icon_array[$type];
        }
        return $type_array[$type];
    }

    static function negativeCheck( $number ) {
        return ( $number > 0 ) ? true : ( ( $number < 0 ) ? false : 0 );
    }

    static function decryptPin($pin,$public_key)
    {
        $secret_key = SecurityHelper::decipherEncryption($public_key . "CJJbW7SaznW7cZhVzwLo");
        return SecurityHelper::tamaCipher($pin, "d", $secret_key);
    }

    static function getNoteCount()
    {
        return Notification::where('user_id',auth()->user()->id)->where('is_read',0)->count();
    }

    static function renderNotification()
    {
        $html = '';
        $notifications = Notification::where('user_id',auth()->user()->id)->where('is_read',0)->get();
        if(collect($notifications)->count() >0){
            foreach ($notifications as $notification) {
                $icon = "fa fa-info-circle fa-3x";
                if($notification->type == 'message'){
                    $icon = "fa fa-comments fa-3x";
                }elseif ($notification->type == 'payment'){
                    $icon = "fa fa-money-bill-alt fa-3x";
                }elseif ($notification->type == 'enquiry'){
                    $icon = "fa fa-envelope fa-3x";
                }elseif ($notification->type == 'request'){
                    $icon = "fa fa-credit-card fa-3x";
                }else{
                    $icon = "fa fa-info-circle fa-3x";
                }
                $html .= '<li class="notification"><a href="'.$notification->url.'?read=true&notification='.$notification->id.'"><div class="media"><div class="media-left">                  <div class="media-object"><i class="'.$icon.'"></i> </div></div><div class="media-body"><strong class="notification-title">'.$notification->title.'</strong><!--p class="notification-desc">Extra description can go here</p--><div class="notification-meta"><small class="timestamp">'.$notification->created_at.'</small></div></div></div></a></li>';
            }
        }
        return $html;
    }

    static function notification_badge($type){
        $html = "<span class='label label-default'>$type</span>";
        switch ($type){
            case "message":
                $html = "<span class='label label-primary'>$type</span>";
                break;

            case "payment":
                $html = "<span class='label label-warning'>$type</span>";
                break;
        }
        return $html;
    }

    static function makeKeyword($str){
        $str = str_replace(".xml", "", $str);
        return str_replace("_", " ", $str);
    }

    /**
     * Aleda Statistics Report
     * @param $cc_id
     * @param $serial
     * @param $pin
     * @param $validity
     * @return bool
     */
    static function aledaStatistics($cc_id,$user_id,$serial,$pin,$validity){
        $ins_data = [
            'date' => date("Y-m-d H:i:s"),
            'cc_id' => $cc_id,
            'used_by' => $user_id,
            'serial' => $serial,
            'pin' => $pin,
            'validity' => $validity,
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => $user_id
        ];
        AledaStatistic::insert($ins_data);
        self::logger('info',"Aleda Service","New pin used",$ins_data);
        return true;
    }

    static function aledaBalance(){
        $dematSoap = new DematSoapController();
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s", [
            "Aleda-Balance"
        ]));
        \Cache::forget($cacheKey);
        return \Cache::remember($cacheKey, $minutes, function () use ($dematSoap) {
            $balance = $dematSoap->getIncurBalance();
            if(isset($balance->error)){
                \Illuminate\Support\Facades\Log::warning("Aleada API error",[$balance]);
                return '0.00';
            }else{
                return AppHelper::formatAmount('EUR', number_format(($balance /100), 2, '.', ''));
            }
        });
    }
    static function BimediaBalance(){
        $Bimedia = new DematSoapBimediaController();
        $minutes = config('constants.cache_expire_in');
        $cacheKey = md5(vsprintf("%s", [
            "Bimedeia-Balance"
        ]));
        \Cache::forget($cacheKey);
        return \Cache::remember($cacheKey, $minutes, function () use ($Bimedia) {

            try{
                $fetch_data = $Bimedia->FetchBalance();
                $balance = $fetch_data->max_srd -  $fetch_data->conso_srd;
                if(isset($balance->error)){
                    \Illuminate\Support\Facades\Log::warning("Bimdeia API error",[$balance]);
                    return '0.00';
                }else{
                    return AppHelper::formatAmount('EUR', number_format(($balance), 2, '.', ''));
                }

            } catch(\Exception $e){
                return '0.00';
            }

        });
    }
    public static function whatsappOtpConfigured()
    {
        if (trim((string) config('services.whatsapp_otp.endpoint', '')) !== '') {
            return true;
        }

        $sid = trim((string) config('services.twilio.sid', ''));
        $token = trim((string) config('services.twilio.token', ''));
        $contentSids = array_filter([
            trim((string) config('services.twilio.otp_content.fr', '')),
            trim((string) config('services.twilio.otp_content.en', '')),
            trim((string) config('services.twilio.otp_content.de', '')),
        ]);
        $messagingService = trim((string) config('services.twilio.wa_messaging_service', ''));
        $from = trim((string) config('services.twilio.whatsapp_from', config('services.twilio.wa_from', '')));

        return $sid !== ''
            && $token !== ''
            && !empty($contentSids)
            && ($messagingService !== '' || $from !== '');
    }

    protected static function normalizeMobileDigits($destinataires)
    {
        return preg_replace('/\D+/', '', (string) $destinataires);
    }


    public static function sendSms($destinataires, $message)
    {
        $normalized = self::normalizeMobileDigits($destinataires);
        if ($normalized === '') {
            Logeer::warning('SMS OTP mobile number missing or invalid');

            return null;
        }


        $client = new Client();
        $url = 'https://www.spot-hit.fr/api/envoyer/sms';
        try {
            $response = $client->request('GET', $url, [
                'query' => [
                    'key' => '8370f2140fad00290fa7a5e9e15c588b',
                    'destinataires' => $normalized,
                    'message' => $message,
                    'expediteur' => 'OTP6789'
                ],
                'verify' => false, // Disables SSL certificate verification, use with caution
            ]);

            if ($response->getStatusCode() === 200) {
                \Illuminate\Support\Facades\Log::info('Sms Send Successfully');
                return $message;
            } else {
                \Illuminate\Support\Facades\Log::error('Failed to send SMS. Status Code: ' . $response->getStatusCode());
                return null; // or throw an exception, depending on your requirement
            }
        } catch (RequestException $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send SMS. ' . $e->getMessage());
            return null; // or throw an exception, depending on your requirement
        }
    }

    public static function sendWhatsapp($destinataires, $message, $otp = null)
    {
        $twilioAttempted = false;

        if (self::whatsappOtpConfigured()) {
            $twilioAttempted = true;
            $result = WaOtp::send($destinataires, $otp, app()->getLocale());

            if (!empty($result['ok'])) {
                Logeer::info('WhatsApp OTP sent successfully via Twilio', [
                    'to' => $result['to'] ?? null,
                    'sid' => $result['sid'] ?? null,
                ]);

                return $message;
            }

            Logeer::warning('Twilio WhatsApp OTP failed', [
                'status' => $result['status'] ?? null,
                'error' => $result['error'] ?? null,
            ]);
        }

        $endpoint = trim((string) config('services.whatsapp_otp.endpoint', ''));

        if ($endpoint === '') {
            if (! $twilioAttempted) {
                Logeer::warning('WhatsApp OTP provider not configured');
            }

            return null;
        }

        $client = new Client([
            'timeout' => 10,
        ]);

        try {
            $headers = [
                'Accept' => 'application/json',
            ];

            $token = trim((string) config('services.whatsapp_otp.token', ''));
            if ($token !== '') {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $response = $client->request('POST', $endpoint, [
                'headers' => $headers,
                'json' => [
                    'to' => $destinataires,
                    'message' => $message,
                    'from' => config('services.whatsapp_otp.from', APP_NAME),
                ],
                'verify' => false,
            ]);

            if (in_array($response->getStatusCode(), [200, 201, 202])) {
                \Illuminate\Support\Facades\Log::info('WhatsApp OTP sent successfully');
                return $message;
            }

            \Illuminate\Support\Facades\Log::warning('WhatsApp OTP request failed', [
                'status' => $response->getStatusCode(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send WhatsApp OTP. ' . $e->getMessage());
        }

        return null;
    }

    public static function sendOtpMessage($destinataires, $message, $channel = 'sms', $otp = null)
    {
        $channel = strtolower(trim((string) $channel));

        if ($channel === 'whatsapp') {
            return self::sendWhatsapp($destinataires, $message, $otp);
        }

        return self::sendSms($destinataires, $message);
    }
}
