<?php

namespace App\Http\Controllers\App;


use app\Library\AppHelper;
use app\Library\DBHelper;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Validator;
use App\Models\DailyLimit;
use App\Models\Service;
use App\Models\UserAccess;

class ServiceAccessController extends Controller
{
    public function index()
    {
        // Get managers with group_id = 3
        $managers = User::where('group_id', 3)->where('status', 1)->select('id', 'username')->get();

        return view('app.service-access.index', compact('managers'));
    }

    public function list(Request $request)
    {
        $data = DB::table('users')
            ->join('user_access', 'users.id', '=', 'user_access.user_id')
            ->join('services', 'services.id', '=', 'user_access.service_id')
            ->where('users.group_id', 4) // group_id 4 = Retailers
            ->where('user_access.status', 1)
            ->select('users.id', 'users.username', 'services.name as service_name')
            ->get()
            ->groupBy('username')
            ->map(function ($items, $username) {
                return [
                    'username' => $username,
                    'services' => $items->pluck('service_name')->implode(', ')
                ];
            })
            ->values();

        return DataTables::of($data)->make(true);
    }


    public function getRetailers(Request $request)
    {
        $retailers = User::where('parent_id', $request->manager_id)
            ->where('status', 1)
            ->select('id', 'username')
            ->get();

        return response()->json($retailers);
    }

    public function getRetailerServices(Request $request)
    {
        $retailer_id = $request->retailer_id;

        $services = Service::all();
        $access = UserAccess::where('user_id', $retailer_id)->pluck('status', 'service_id');

        return response()->json([
            'services' => $services,
            'access' => $access
        ]);
    }

    public function updateRetailerServices(Request $request)
    {
        $retailer_id = $request->retailer_id;
        $services = $request->input('services', []);

        // Loop all available services to handle both checked (1) and unchecked (0)
        foreach (Service::all() as $service) {
            $user_check = UserAccess::where('user_id', $retailer_id)->where('service_id', $service->id)->first();
            $status = in_array($service->id, $services) ? 1 : 0;
            UserAccess::where('user_id', $retailer_id)->where('id', $user_check->id)->update([
                'status' => $status,
                'updated_at' => now(),
                'updated_by' => auth()->user()->id
            ]);
        }
        return redirect()->back()
            ->with('message', trans('common.msg_update_success'))
            ->with('message_type','success');
    }
}
