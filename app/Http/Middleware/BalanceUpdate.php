<?php

namespace App\Http\Middleware;

use app\Library\AppHelper;
use Closure;
use Auth;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;
use App\User;

class BalanceUpdate
{

    public function handle($request, Closure $next)
    {
		// Guard: if not logged in, skip silently
		if (!auth()->check()) {
			return $next($request);
		}

		// Cache the current user once
		$me = auth()->user();

		if ($me && $me->parent_id != NULL)
		{
            $user = User::where('id', auth()->user()->parent_id)->first();
            $expires_at = Carbon::now()->subMinutes(1)->format('Y-m-d H:i:s');
            if($user->last_activity <= $expires_at) {
                $query_check = Payment::where('user_id', auth()->user()->parent_id)->where('transaction_id', NULL)->first();
                if ($query_check) {
                    $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->parent_id, auth()->user()->currency, false));
                    $trans_id = Transaction::insertGetId([
                        'user_id' => auth()->user()->parent_id,
                        'date' => date('Y-m-d H:i:s'),
                        'type' => 'credit',
                        'amount' => $query_check->amount,
                        'credit' => $query_check->amount,
                        'prev_bal' => $getBalance,
                        'balance' => $getBalance + $query_check->amount,
                        'description' => $query_check->description,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => auth()->user()->parent_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    Payment::where('id', $query_check->id)->where('user_id', auth()->user()->parent_id)->where('transaction_id', NULL)->update([
                        'transaction_id' => $trans_id
                    ]);
                }
            }
        }
        $query = Payment::where('user_id',auth()->user()->id)->where('transaction_id',NULL)->first();
        if ($query) {
            $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
            $trans_id = Transaction::insertGetId([
                'user_id' => auth()->user()->id,
                'date' => date('Y-m-d H:i:s'),
                'type' => 'credit',
                'amount' => $query->amount,
                'credit' => $query->amount,
                'prev_bal' => $getBalance,
                'balance' => $getBalance + $query->amount,
                'description' => $query->description,
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            Payment::where('id', $query->id)->where('user_id', auth()->user()->id)->where('transaction_id',NULL)->update([
                'transaction_id' => $trans_id
            ]);
            return $next($request);
        }
        return $next($request);
    }

}