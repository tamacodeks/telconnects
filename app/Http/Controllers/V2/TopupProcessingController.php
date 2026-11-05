<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\TopupProcessingSetting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TopupProcessingController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('topup_processing_settings')) {
            return redirect()->route('users.v2')
                ->with('message', trans('topup_processing.table_missing'))
                ->with('message_type', 'warning');
        }

        $search = trim((string) $request->query('search', ''));
        $mode = $request->query('mode', 'all');
        $managerId = (int) $request->query('manager_id', 0);
        if (!in_array($mode, ['all', 'synchronous', 'queued'], true)) {
            $mode = 'all';
        }

        $managers = User::where('group_id', 3)
            ->orderBy('username')
            ->get(['id', 'username', 'status']);

        if ($managerId > 0 && !$managers->contains('id', $managerId)) {
            $managerId = 0;
        }

        $query = User::query()
            ->leftJoin('user_groups', 'user_groups.id', '=', 'users.group_id')
            ->leftJoin('users as managers', 'managers.id', '=', 'users.parent_id')
            ->leftJoin('topup_processing_settings', 'topup_processing_settings.user_id', '=', 'users.id')
            ->where('users.group_id', 4)
            ->select([
                'users.id', 'users.username', 'users.email', 'users.status', 'users.group_id', 'users.parent_id',
                'user_groups.name as role_name',
                'managers.username as manager_name',
                DB::raw("COALESCE(topup_processing_settings.mode, 'synchronous') as topup_processing_mode"),
            ]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('users.username', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhere('user_groups.name', 'like', '%' . $search . '%')
                    ->orWhere('managers.username', 'like', '%' . $search . '%');
            });
        }

        if ($managerId > 0) {
            $query->where('users.parent_id', $managerId);
        }

        if ($mode === 'queued') {
            $query->where('topup_processing_settings.mode', 'queued');
        } elseif ($mode === 'synchronous') {
            $query->where(function ($builder) {
                $builder->whereNull('topup_processing_settings.mode')
                    ->orWhere('topup_processing_settings.mode', 'synchronous');
            });
        }

        $users = $query->orderBy('users.username')->paginate(25);
        $users->appends($request->only(['search', 'mode', 'manager_id']));

        return view('v2.app.topup_processing.index', [
            'page_title' => trans('topup_processing.title'),
            'users' => $users,
            'search' => $search,
            'mode' => $mode,
            'managerId' => $managerId,
            'managers' => $managers,
            'queuedCount' => TopupProcessingSetting::join('users', 'users.id', '=', 'topup_processing_settings.user_id')
                ->where('users.group_id', 4)
                ->where('topup_processing_settings.mode', 'queued')
                ->count(),
        ]);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
            'mode' => 'required|in:synchronous,queued',
        ]);

        $userIds = User::whereIn('id', $request->input('user_ids'))
            ->where('group_id', 4)
            ->pluck('id')
            ->map(function ($id) { return (int) $id; })
            ->all();

        if (!$userIds) {
            return redirect()->back()
                ->with('message', trans('topup_processing.no_users'))
                ->with('message_type', 'warning');
        }

        $selectedMode = $request->input('mode');
        DB::transaction(function () use ($userIds, $selectedMode) {
            foreach ($userIds as $userId) {
                if ($selectedMode === 'synchronous') {
                    TopupProcessingSetting::where('user_id', $userId)->delete();
                    continue;
                }

                TopupProcessingSetting::updateOrCreate(
                    ['user_id' => $userId],
                    ['mode' => 'queued']
                );
            }
        });

        return redirect()->back()
            ->with('message', trans_choice('topup_processing.saved', count($userIds), ['count' => count($userIds)]))
            ->with('message_type', 'success');
    }
}
