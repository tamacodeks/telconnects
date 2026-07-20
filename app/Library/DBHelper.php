<?php
/**
 * Created by Decipher Lab.
 * User: Prabakar
 * Date: 03-Apr-18
 * Time: 11:39 AM
 */

namespace app\Library;


use App\Models\AppCommission;
use App\Models\Commission;
use App\Models\Manager_commission;
use App\Models\CreditLimit;
use App\Models\UserAccess;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DBHelper
{

    static function getAppCommission($service_id)
    {
        $accessCacheKey = md5(vsprintf("%s,%s", [
            "appcommission",
            $service_id
        ]));
        //this cache will be automatically removed if commission were changed
        //ServiceCommissionController-> line 142
        $commission = \Cache::remember($accessCacheKey, 60, function () use ($service_id) {
            return DB::select(" SELECT app_commissions.* FROM app_commissions WHERE service_id ='" . $service_id . "'");
        });
        return str_replace(".00", "", isset($commission[0]->commission) ? $commission[0]->commission : 0);
    }

    static function getCommission($user_id, $service_id)
    {
        return str_replace(".00", "", optional(Commission::where('user_id', $user_id)->where('service_id', $service_id)->first())->commission);
    }

    static function getCreditLimit($user_id)
    {
        return optional(CreditLimit::where('user_id', $user_id)->first())->credit_limit;
    }

    static function record_methods()
    {
        return [
            '1' => "View By Date",
            '2' => "View By Month",
            '3' => "View By Week"
        ];
    }

    static function update_service_access($user_id, $service_id, $status)
    {
        $user_check = UserAccess::where('user_id', $user_id)->where('service_id', $service_id)->first();
        if (!empty($user_check)) {
            $user = User::where('id',$user_id)->first();
            if ($user->group_id == 4) {
                $status = $user_check->status;
            }
            UserAccess::where('user_id', $user_id)->where('id', $user_check->id)->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . ' service ' . $service_id . ' => ' . $status . ' updated');
        } else {
            UserAccess::insert([
                'user_id' => $user_id,
                'service_id' => $service_id,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . ' service ' . $service_id . ' => ' . $status . ' added');
        }
        return true;
    }

    static function update_user_commission($user_id, $service_id, $commission)
    {
        if ($commission == 0) return true;
        $user_check = Commission::where('user_id', $user_id)->where('service_id', $service_id)->first();
        if (!empty($user_check)) {
            Commission::where('user_id', $user_id)->where('id', $user_check->id)->update([
                'commission' => $commission,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . ' commission for ' . $service_id . ' => ' . $commission . ' updated');
        } else {
            Commission::insert([
                'type' => 'custom',
                'user_id' => $user_id,
                'service_id' => $service_id,
                'commission' => $commission,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . ' service ' . $service_id . ' => ' . $commission . ' added');
        }
        return true;
    }

    static function update_manager_commission($user_id, $service_id, $commission)
    {

        if ($commission == 0) return true;
        $user_check = Manager_commission::where('user_id', $user_id)->where('service_id', $service_id)->first();
        if (!empty($user_check)) {
            Manager_commission::where('user_id', $user_id)->where('id', $user_check->id)->update([
                'commission' => $commission,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . ' Manager commission for ' . $service_id . ' => ' . $commission . ' updated');
        } else {
            Manager_commission::insert([
                'user_id' => $user_id,
                'service_id' => $service_id,
                'commission' => $commission,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id
            ]);
            Log::info('User ' . $user_id . 'Manager service ' . $service_id . ' => ' . $commission . ' added');
        }
        return true;
    }
}