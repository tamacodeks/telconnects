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
        return view('app.topup-confirm-attempts.index', [
            'page_title' => 'TamaTopup Confirm Attempts',
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

                if ($request->filled('from_date') || $request->filled('to_date')) {
                    $from = ($request->input('from_date') ?: '1970-01-01') . ' 00:00:00';
                    $to = ($request->input('to_date') ?: date('Y-m-d')) . ' 23:59:59';
                    $query->whereBetween('created_at', [$from, $to]);
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
