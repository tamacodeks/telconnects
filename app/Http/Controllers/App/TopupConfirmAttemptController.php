<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\TopupConfirmAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class TopupConfirmAttemptController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $today = date('Y-m-d');

        return view('app.topup-confirm-attempts.index', [
            'page_title' => trans('topup_v2.confirm_attempts.page_title'),
            'from_date' => request('from_date', $today),
            'to_date' => request('to_date', $today),
        ]);
    }

    public function data(Request $request)
    {
        if (!Schema::hasTable('topup_confirm_attempts')) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'topup_confirm_attempts table does not exist. Run migrations first.',
            ]);
        }

        $fromDate = $request->input('from_date') ?: date('Y-m-d');
        $toDate = $request->input('to_date') ?: $fromDate;

        $query = TopupConfirmAttempt::query()
            ->select([
                'id',
                'request_id',
                'layer',
                'provider',
                'user_id',
                'username',
                'mobile',
                'amount',
                'status',
                'lock_key',
                'internal_reference',
                'provider_reference',
                'order_id',
                'transaction_id',
                'ip',
                'user_agent',
                'referer',
                'origin',
                'error_message',
                'metadata',
                'locked_at',
                'completed_at',
                'created_at',
                'updated_at',
            ])
            ->whereBetween('created_at', [
                $fromDate . ' 00:00:00',
                $toDate . ' 23:59:59',
            ])
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->editColumn('created_at', function ($attempt) {
                return optional($attempt->created_at)->format('Y-m-d H:i:s');
            })
            ->editColumn('locked_at', function ($attempt) {
                return optional($attempt->locked_at)->format('Y-m-d H:i:s');
            })
            ->editColumn('completed_at', function ($attempt) {
                return optional($attempt->completed_at)->format('Y-m-d H:i:s');
            })
            ->addColumn('status_label', function ($attempt) {
                return $this->statusLabel($attempt->status);
            })
            ->addColumn('request_short', function ($attempt) {
                return '<span title="' . e($attempt->request_id) . '">' . e(substr((string) $attempt->request_id, 0, 12)) . '</span>';
            })
            ->addColumn('order_link', function ($attempt) {
                return $attempt->order_id ? e($attempt->order_id) : '';
            })
            ->filter(function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }

                if ($request->filled('provider')) {
                    $query->where('provider', $request->input('provider'));
                }

                if ($request->filled('layer')) {
                    $query->where('layer', $request->input('layer'));
                }

                if ($search = trim((string) $request->input('query'))) {
                    $query->where(function ($scope) use ($search) {
                        $scope->where('request_id', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('amount', 'like', "%{$search}%")
                            ->orWhere('provider_reference', 'like', "%{$search}%")
                            ->orWhere('internal_reference', 'like', "%{$search}%")
                            ->orWhere('ip', 'like', "%{$search}%");
                    });
                }
            })
            ->rawColumns(['status_label', 'request_short', 'order_link'])
            ->make(true);
    }

    private function statusLabel($status)
    {
        $classes = [
            'SUCCESS' => 'success',
            'BLOCKED_DUPLICATE' => 'warning',
            'TOO_MANY_ATTEMPTS' => 'warning',
            'UNKNOWN' => 'danger',
            'SECURITY_BLOCKED' => 'danger',
            'PROCESSING' => 'info',
            'API_REQUEST_STARTED' => 'primary',
            'API_RESPONSE_RECEIVED' => 'default',
        ];

        $class = isset($classes[$status]) ? $classes[$status] : 'default';

        return '<span class="label label-' . e($class) . '">' . e($status) . '</span>';
    }
}
