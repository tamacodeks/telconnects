<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\NightMaintenanceSetting;
use App\User;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NightMaintenanceController extends Controller
{
    public function index()
    {
        $this->authorizeRoot();

        return view('app.settings.night_maintenance', [
            'global' => NightMaintenanceSetting::whereNull('user_id')->first(),
            'users' => NightMaintenanceSetting::with('user')->whereNotNull('user_id')->latest()->get(),
            'allUsers' => User::where('status', 1)->orderBy('username')->get(['id', 'username']),
            'timezones' => DateTimeZone::listIdentifiers(),
        ]);
    }

    public function indexV2()
    {
        $this->authorizeRoot();

        return view()->file(resource_path('views/v2/app/night_maintenance/index.blade.php'), [
            'global' => NightMaintenanceSetting::whereNull('user_id')->first(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'userCount' => DB::table('users')->where('status', 1)->count(),
            'overrideCount' => DB::table('night_maintenance_settings as maintenance')
                ->join('users', 'users.id', '=', 'maintenance.user_id')
                ->where('users.status', 1)->count(),
        ]);
    }

    public function usersV2(Request $request)
    {
        $this->authorizeRoot();

        $global = NightMaintenanceSetting::whereNull('user_id')->first();
        $query = DB::table('users')
            ->leftJoin('user_groups', 'user_groups.id', '=', 'users.group_id')
            ->leftJoin('night_maintenance_settings as maintenance', 'maintenance.user_id', '=', 'users.id')
            ->where('users.status', 1);

        $total = DB::table('users')->where('status', 1)->count();
        $policy = $request->input('policy');
        if ($policy === 'custom') {
            $query->whereNotNull('maintenance.id');
        } elseif ($policy === 'global') {
            $query->whereNull('maintenance.id');
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('users.username', 'like', '%' . $search . '%')
                    ->orWhere('user_groups.name', 'like', '%' . $search . '%');
            });
        }

        $filtered = (clone $query)->count();
        $columns = ['users.username', 'user_groups.name', 'maintenance.enabled', 'maintenance.allowed_start'];
        $orderIndex = (int) $request->input('order.0.column', 0);
        $direction = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($columns[$orderIndex] ?? 'users.username', $direction)->orderBy('users.id');

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        if ($length !== -1) {
            $query->skip($start)->take(max(1, min($length, 100)));
        }

        $rows = $query->get([
            'users.id', 'users.username', 'user_groups.name as role',
            'maintenance.id as setting_id', 'maintenance.enabled',
            'maintenance.allowed_start', 'maintenance.allowed_end', 'maintenance.timezone',
        ])->map(function ($user) use ($global) {
            $override = $user->setting_id !== null;
            $effective = $override ? $user : $global;

            return [
                'id' => (int) $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'mode' => $override ? 'User override' : 'Global policy',
                'enabled' => $effective ? (bool) $effective->enabled : false,
                'configured' => (bool) $effective,
                'window' => $effective ? substr($effective->allowed_start, 0, 5) . ' - ' . substr($effective->allowed_end, 0, 5) : 'Not configured',
                'timezone' => $effective ? $effective->timezone : '-',
                'setting_id' => $override ? (int) $user->setting_id : null,
                'allowed_start' => $override ? substr($user->allowed_start, 0, 5) : null,
                'allowed_end' => $override ? substr($user->allowed_end, 0, 5) : null,
                'override_timezone' => $override ? $user->timezone : null,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'overrideCount' => DB::table('night_maintenance_settings as maintenance')
                ->join('users', 'users.id', '=', 'maintenance.user_id')
                ->where('users.status', 1)->count(),
            'data' => $rows,
        ]);
    }

    public function save(Request $request)
    {
        $this->authorizeRoot();

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'enabled' => 'nullable|boolean',
            'allowed_start' => 'required|date_format:H:i',
            'allowed_end' => 'required|date_format:H:i',
            'timezone' => 'required|in:' . implode(',', DateTimeZone::listIdentifiers()),
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = $request->input('user_id') !== null && $request->input('user_id') !== ''
            ? $request->input('user_id')
            : null;

        NightMaintenanceSetting::updateOrCreate(
            ['user_id' => $userId],
            [
                'enabled' => (int) $request->input('enabled', 0) === 1 ? 1 : 0,
                'allowed_start' => $request->allowed_start,
                'allowed_end' => $request->allowed_end,
                'timezone' => $request->timezone,
            ]
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => $userId ? 'User maintenance policy saved.' : 'Global maintenance policy saved.',
            ]);
        }

        return redirect()->back()
            ->with('message', 'Night maintenance setting saved.')
            ->with('message_type', 'success');
    }
    public function delete($id)
    {
        $this->authorizeRoot();

        NightMaintenanceSetting::whereKey($id)->whereNotNull('user_id')->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'User override removed.']);
        }

        return redirect()->back()
            ->with('message', 'User override removed.')
            ->with('message_type', 'success');
    }

    private function authorizeRoot()
    {
        if (!auth()->check() || (int) auth()->user()->group_id !== 1) {
            abort(403);
        }
    }
}
