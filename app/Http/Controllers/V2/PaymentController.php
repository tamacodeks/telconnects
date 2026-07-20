<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $retailers = $this->retailerOptions();
        $this->selectRetailerFromUsername($request, $retailers);
        list($fromDate, $toDate) = $this->v2HistoryDateRange($request);

        return view('v2.app.payments.index', [
            'page_title' => trans('v2_history.payments.page_title'),
            'retailers' => $retailers,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    public function data(Request $request)
    {
        if ($request->filled('from_date') || $request->filled('to_date')) {
            list($fromDate, $toDate) = $this->normalizeV2HistoryDateRange(
                $request->input('from_date'),
                $request->input('to_date')
            );

            $request->merge([
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);
        }

        $query = $this->paymentsQuery();
        $this->applyDateFilter($query, $request);

        return DataTables::of($query)
            ->addColumn('comment', function ($payment) {
                if (empty($payment->prev_bal)) {
                    return implode(' ', [
                        'Payment',
                        $this->money($payment->amount),
                        "\xE2\x82\xAC",
                        'initiated',
                        $payment->username,
                        'account login',
                    ]);
                }

                return trim((string) $payment->description);
            })
            ->editColumn('amount', function ($payment) {
                return $this->money($payment->amount);
            })
            ->editColumn('prev_bal', function ($payment) {
                return $this->money($payment->prev_bal);
            })
            ->editColumn('balance', function ($payment) {
                return $this->money($payment->balance);
            })
            ->editColumn('payment_date', function ($payment) {
                return $payment->payment_date ?: '';
            })
            ->filter(function ($query) use ($request) {
                $this->applyRetailerFilter($query, $request);
                $this->applySearchFilter($query, $request);
            })
            ->make(true);
    }

    private function paymentsQuery()
    {
        $query = Payment::leftJoin('transactions', 'transactions.id', '=', 'payments.transaction_id')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->leftJoin('users as receivers', 'receivers.id', '=', 'payments.received_by')
            ->select([
                'payments.id',
                'payments.date',
                'transactions.date as payment_date',
                'users.id as user_id',
                'users.username',
                'users.cust_id',
                'payments.amount',
                'transactions.prev_bal',
                'transactions.balance',
                'payments.description',
                'receivers.username as received_by_name',
            ]);

        return $this->applyUserScope($query)->orderBy('payments.date', 'desc');
    }

    private function applyUserScope($query)
    {
        $groupId = (int) auth()->user()->group_id;

        if (in_array($groupId, [2, 3], true)) {
            $retailerIds = $this->childRetailerIds();

            return empty($retailerIds)
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('payments.user_id', $retailerIds);
        }

        if ($groupId === 4) {
            return $query->where('payments.user_id', auth()->user()->id);
        }

        return $query->where(function ($scope) {
            $scope->where('users.parent_id', '=', '0')
                ->orWhereNull('users.parent_id');
        });
    }

    private function retailerOptions()
    {
        if ((int) auth()->user()->group_id === 4) {
            return User::where('id', auth()->user()->id)
                ->select('id', 'username', 'cust_id')
                ->get();
        }

        $query = User::where('status', 1)
            ->select('id', 'username', 'cust_id')
            ->orderBy('username');

        if (in_array((int) auth()->user()->group_id, [1, 2, 3], true)) {
            $retailerIds = $this->childRetailerIds();
            $query->whereIn('id', $retailerIds);
        } else {
            $query->where(function ($scope) {
                $scope->where('parent_id', '=', '0')
                    ->orWhereNull('parent_id');
            });
        }

        return $query->get();
    }

    private function childRetailerIds()
    {
        $user = User::where('id', auth()->user()->id)->with('children')->first();

        return $user ? $user->children->pluck('id')->flatten()->toArray() : [];
    }

    private function selectRetailerFromUsername(Request $request, $retailers)
    {
        if (!$request->filled('user') || $request->filled('retailer_id')) {
            return;
        }

        $retailer = $retailers->first(function ($item) use ($request) {
            return strcasecmp((string) $item->username, (string) $request->input('user')) === 0;
        });

        if ($retailer) {
            $request->merge(['retailer_id' => [(string) $retailer->id]]);
        }
    }

    private function applyDateFilter($query, Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if (empty($fromDate) || empty($toDate)) {
            return;
        }

        $query->whereBetween('payments.date', [
            $fromDate . ' 00:00:00',
            $toDate . ' 23:59:59',
        ]);
    }

    private function applyRetailerFilter($query, Request $request)
    {
        $retailerIds = array_values(array_filter((array) $request->input('retailer_id', []), function ($value) {
            return ctype_digit((string) $value);
        }));

        if (!empty($retailerIds)) {
            $query->whereIn('users.id', $retailerIds);
        }
    }

    private function applySearchFilter($query, Request $request)
    {
        $search = trim((string) $request->input('query'));

        if ($search === '') {
            return;
        }

        $query->where(function ($scope) use ($search) {
            $scope->where('users.username', 'like', "%{$search}%")
                ->orWhere('users.cust_id', 'like', "%{$search}%")
                ->orWhere('payments.description', 'like', "%{$search}%")
                ->orWhere('payments.amount', 'like', "%{$search}%")
                ->orWhere('receivers.username', 'like', "%{$search}%");
        });
    }

    private function v2HistoryDateRange(Request $request)
    {
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from') || $request->filled('to')) {
            return $this->normalizeV2HistoryDateRange(
                $request->input('from_date', $request->input('from')),
                $request->input('to_date', $request->input('to'))
            );
        }

        return ['', ''];
    }

    private function normalizeV2HistoryDateRange($fromDate = null, $toDate = null)
    {
        $today = Carbon::now()->startOfDay();
        $minDate = $today->copy()->subMonths(3);
        $from = $this->parseV2HistoryDate($fromDate) ?: $today->copy();
        $to = $this->parseV2HistoryDate($toDate) ?: $today->copy();

        if ($from->lt($minDate)) {
            $from = $minDate->copy();
        }

        if ($to->lt($minDate)) {
            $to = $minDate->copy();
        }

        if ($from->gt($today)) {
            $from = $today->copy();
        }

        if ($to->gt($today)) {
            $to = $today->copy();
        }

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        return [$from->format('Y-m-d'), $to->format('Y-m-d')];
    }

    private function parseV2HistoryDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $value)->startOfDay();

            return $date->format('Y-m-d') === (string) $value ? $date : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function money($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return number_format((float) $value, 2, '.', '');
    }
}
