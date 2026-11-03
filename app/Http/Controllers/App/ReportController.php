<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function retailerServiceSummary(Request $request)
    {
        list($fromDate, $toDate) = $this->dateRange($request);

        $children = $this->childUserQuery()
            ->select('id', 'username', 'first_name', 'last_name')
            ->orderBy('username')
            ->get();

        $childUserIds = $children->pluck('id')->toArray();
        $services = Service::select('id', 'name')
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->map(function ($service) {
                $service->report_name = $this->serviceName($service->name);
                return $service;
            });

        $stats = $this->serviceStats($childUserIds, $fromDate, $toDate);
        $rows = $this->buildRows($children, $services, $stats);
        $totals = $this->buildTotals($rows, $services);

        return view('app.reports.retailer-service-summary', [
            'page_title' => trans('reports.labels.service_report'),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'child_user_label' => $this->childUserLabel(),
            'child_user_singular_label' => $this->childUserSingularLabel(),
            'child_user_count' => $children->count(),
            'services' => $services,
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    private function dateRange(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $fromDate = $this->validDate($request->input('from_date')) ?: $today;
        $toDate = $this->validDate($request->input('to_date')) ?: $today;

        if ($toDate < $fromDate) {
            $toDate = $fromDate;
        }

        return [$fromDate, $toDate];
    }

    private function validDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $value);
            return $date->format('Y-m-d') === (string) $value ? $date->format('Y-m-d') : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function childUserQuery()
    {
        $user = auth()->user();
        $query = User::where('status', 1);

        if ((int) $user->group_id === 1) {
            return $query->where('group_id', 2);
        }

        if ((int) $user->group_id === 2) {
            return $query->where('parent_id', $user->id)->where('group_id', 3);
        }

        if ((int) $user->group_id === 3) {
            return $query->where('parent_id', $user->id)->where('group_id', 4);
        }

        return $query->where('id', $user->id);
    }

    private function childUserLabel()
    {
        $groupId = (int) auth()->user()->group_id;

        if ($groupId === 1) {
            return trans('reports.labels.admins');
        }

        if ($groupId === 2) {
            return trans('reports.labels.managers');
        }

        if ($groupId === 3) {
            return trans('reports.labels.retailers');
        }

        return trans('reports.labels.users');
    }

    private function childUserSingularLabel()
    {
        $groupId = (int) auth()->user()->group_id;

        if ($groupId === 1) {
            return trans('reports.labels.admin');
        }

        if ($groupId === 2) {
            return trans('reports.labels.manager');
        }

        if ($groupId === 3) {
            return trans('reports.labels.retailer');
        }

        return trans('reports.labels.user');
    }

    private function serviceStats(array $childUserIds, $fromDate, $toDate)
    {
        if (empty($childUserIds)) {
            return collect();
        }

        $groupId = (int) auth()->user()->group_id;
        $query = DB::table('orders')
            ->select([
                'orders.service_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(orders.grand_total) as transaction_amount'),
            ])
            ->where('orders.order_status_id', 7)
            ->whereBetween('orders.date', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        if ($groupId === 1) {
            $query->join('users', 'users.id', '=', 'orders.user_id')
                ->addSelect('users.parent_id as report_user_id')
                ->whereIn('users.parent_id', $childUserIds)
                ->where('users.group_id', 3)
                ->where('orders.is_parent_order', 1)
                ->groupBy('users.parent_id', 'orders.service_id');
        } else {
            $query->addSelect('orders.user_id as report_user_id')
                ->whereIn('orders.user_id', $childUserIds)
                ->where('orders.is_parent_order', in_array($groupId, [2, 3], true) ? 1 : 0)
                ->groupBy('orders.user_id', 'orders.service_id');
        }

        return $query->get()->groupBy('report_user_id');
    }

    private function buildRows($children, $services, $stats)
    {
        return $children->map(function ($childUser) use ($services, $stats) {
            $childUserStats = $stats->get($childUser->id, collect())->keyBy('service_id');
            $serviceData = [];
            $totalCount = 0;
            $totalAmount = 0;

            foreach ($services as $service) {
                $stat = $childUserStats->get($service->id);
                $count = $stat ? (int) $stat->transaction_count : 0;
                $amount = $stat ? (float) $stat->transaction_amount : 0;

                $serviceData[$service->id] = [
                    'count' => $count,
                    'amount' => $amount,
                    'percentage' => 0,
                ];

                $totalCount += $count;
                $totalAmount += $amount;
            }

            foreach ($serviceData as $serviceId => $data) {
                $serviceData[$serviceId]['percentage'] = $totalAmount > 0
                    ? round(($data['amount'] / $totalAmount) * 100, 2)
                    : 0;
            }

            return [
                'user' => $childUser,
                'services' => $serviceData,
                'total_count' => $totalCount,
                'total_amount' => $totalAmount,
            ];
        })->sortByDesc('total_amount')->values();
    }

    private function buildTotals($rows, $services)
    {
        $totals = [
            'transaction_count' => 0,
            'transaction_amount' => 0,
            'services' => [],
        ];

        foreach ($services as $service) {
            $totals['services'][$service->id] = [
                'count' => 0,
                'amount' => 0,
                'percentage' => 0,
            ];
        }

        foreach ($rows as $row) {
            $totals['transaction_count'] += $row['total_count'];
            $totals['transaction_amount'] += $row['total_amount'];

            foreach ($row['services'] as $serviceId => $serviceData) {
                $totals['services'][$serviceId]['count'] += $serviceData['count'];
                $totals['services'][$serviceId]['amount'] += $serviceData['amount'];
            }
        }

        foreach ($totals['services'] as $serviceId => $serviceData) {
            $totals['services'][$serviceId]['percentage'] = $totals['transaction_amount'] > 0
                ? round(($serviceData['amount'] / $totals['transaction_amount']) * 100, 2)
                : 0;
        }

        return $totals;
    }

    private function serviceName($name)
    {
        if ($name === 'Tama Topup') {
            return 'TopUp';
        }

        if ($name === 'Flix Bus') {
            return 'Bus';
        }

        return $name;
    }
}
