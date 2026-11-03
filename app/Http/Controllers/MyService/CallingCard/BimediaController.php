<?php

namespace App\Http\Controllers\Myservice\CallingCard;

use App\Http\Controllers\Api\DematSoapBimediaController;
use app\Library\ApiHelper;
use app\Library\AppHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Spatie\ArrayToXml\ArrayToXml;
use SimpleXMLElement;
use App\Models\BimediaAccount;
use App\Models\Bimedia_statistics;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BimediaController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Bimedia Manage Settings Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    function index()
    {
        $bimedia = new DematSoapBimediaController();
        $balance = $bimedia->FetchBalance();
        try
        {
            $data = [
                'code_client' => $balance->code_client_srd,
                'remaining_bal' => $balance->conso_srd,
                'total_amount' => $balance->max_srd,
                'conso_srdmp' => $balance->conso_srdmp,
                'max_srdmp' => $balance->max_srdmp,
            ];
            AppHelper::logger('info','SFTP Manager',auth()->user()->username ." manage files on SFTP");
            return view('myservice.calling-cards.Bimedia.index',$data);
        }
        catch(\Exception $e){
            $data = [
                'code_client' => '0.00',
                'remaining_bal' => '0.00',
                'total_amount' =>'0.00',
                'conso_srdmp' =>'0.00',
                'max_srdmp' => '0.00',
            ];
            AppHelper::logger('info','SFTP Manager',auth()->user()->username ." manage files on SFTP");
            return view('myservice.calling-cards.Bimedia.index',$data);
        }
    }

    function syncCatalogue(Request $request){
        $bimedia = new DematSoapBimediaController();
        $data = $bimedia->FetchCatalogue();
        $param =json_decode(json_encode($data->catalogueInfo), true);
        $result = ArrayToXml::convert(['products' => $param]);
        Storage::disk('public')->put('catalogue/bimedia_catalogue.xml', $result);
        return ApiHelper::response('200',200,"Sync finished!");
    }

    function bimedia_stat(Request $request){
        $providers= Bimedia_statistics::get();
        return view('myservice.calling-cards.Bimedia.statistics', $providers);
    }
    function fetch_data(Request $request)
    {
        $query = Bimedia_statistics::orderBy('id',"DESC");
        if (empty($request->input('from_date')) && empty($request->input('to_date'))) {
            $query->whereDate('bimedia_statistics.date',date('Y-m-d'))->get();
        }else{
            $from_date = $request->input('from_date').' 00:00:00';
            $to_date = $request->input('to_date').' 23:59:59';
            $query->whereBetween('bimedia_statistics.date',[$from_date,$to_date])->get();
        }
        return Datatables::of($query)
            ->addIndexColumn()
            ->make(true);
    }
	public function balances()
    {
        // Optional: list accounts (for Add modal visibility); the table loads via AJAX.
        $accounts = BimediaAccount::orderBy('id','desc')->get();

        return view('myservice.calling-cards.Bimedia.index-multi', compact('accounts'));
    }

	public function storeAccount(Request $request)
	{
		// Trim inputs first (keeps leading zeros on codes)
		$input = $request->all();
		foreach (['name','comcod','tpvcod','authorization','ip_address','web_service_url','web_service_location'] as $k) {
			if (isset($input[$k])) $input[$k] = trim($input[$k]);
		}

		// Build validator (Laravel 5.6/5.8/10 compatible)
		$v = Validator::make($input, [
			'name'   => 'bail|required|string|min:2|max:120',

			// COMCOD / TPVCOD: 5–12 digits (adjust if your real length differs)
			'comcod' => ['bail','required','string','max:120','regex:/^\d{5,12}$/'],
			'tpvcod' => [
				'bail','required','string','max:120','regex:/^\d{5,12}$/',

				// EITHER Rule::unique(...) (works in 5.6+)...
				Rule::unique('bimedia_accounts', 'tpvcod')->where(function($q) use ($input) {
					return $q->where('comcod', $input['comcod']);
				}),

				// ...OR (older style) uncomment this line instead of Rule::unique if you prefer:
				// 'unique:bimedia_accounts,tpvcod,NULL,id,comcod,'.$input['comcod'],
			],

			// Authorization: either "Basic <base64>" or just the base64 token
			'authorization' => ['bail','required','string','max:2048','regex:/^(Basic\s+)?[A-Za-z0-9+\/=]{8,}$/'],

			// Optional extras if you include them in the form:
			'ip_address'           => ['nullable','ip'],
			'web_service_url'      => ['nullable','url','regex:/\.wsdl(\?.*)?$/i'],
			'web_service_location' => ['nullable','url'],
			'active'               => ['sometimes','boolean'],
		], [
			'comcod.regex'        => 'COMCOD must be 5–12 digits.',
			'tpvcod.regex'        => 'TPVCOD must be 5–12 digits.',
			'tpvcod.unique'       => 'This COMCOD + TPVCOD combination already exists.',
			'authorization.regex' => 'Authorization must be "Basic &lt;base64(username:password)&gt;" or a base64 token.',
			'web_service_url.regex' => 'Web service URL must point to a .wsdl.',
		]);

		// Deep check: authorization should base64-decode to "username:password"
		$v->after(function($v) use (&$input) {
			$auth = (string)($input['authorization'] ?? '');
			$token = preg_replace('/^Basic\s+/i', '', $auth);
			$decoded = base64_decode($token, true);
			if ($decoded === false || strpos($decoded, ':') === false) {
				$v->errors()->add('authorization', 'Authorization token is not a valid base64-encoded "username:password".');
				return;
			}
			list($user, $pass) = array_pad(explode(':', $decoded, 2), 2, null);
			if ($user === '' || $user === null || $pass === '' || $pass === null) {
				$v->errors()->add('authorization', 'Authorization token must contain both username and password.');
			}
		});

		if ($v->fails()) {
			return back()->withErrors($v)->withInput();
		}

		// Store the token only (no "Basic " prefix) to avoid duplication later;
		// your SOAP call will prepend the right header format.
		$tokenOnly = preg_replace('/^Basic\s+/i', '', $input['authorization']);
		$input['authorization'] = $tokenOnly;

		// Create
		\App\Models\BimediaAccount::create($input);

        return redirect()->with('success', 'Account added.');
    }

    public function balancesAjax(Request $request)
    {
        $accounts = BimediaAccount::where('active', 1)->orderBy('id','asc')->get();

        $rows = [];
        foreach ($accounts as $acc) {
            $rows[] = $this->fetchBalanceForAccount($acc);
        }

        return DataTables::of($rows)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * IMPORTANT: Creates its own SoapClient per account.
     * DOES NOT use your BaseSoapController / InstanceSoapClient (so we don't have to change them).
     */
protected function fetchBalanceForAccount($acc)
{
    // WSDL & endpoint (stay WSDL-mode; don't pass location/uri)
    $wsdl = $acc->web_service_url ?: 'https://wholesalers.bimedia-it.com/srdws/srdtel.wsdl';

    // Use the vendor-registered public IP if you have it per account; fallback to config
    $ip = $acc->ip_address ?: (config('bimedia.ip_address') ?: '');

    // SOAP params
    $params = [
        'COMCOD'        => $acc->comcod,
        'TPVCOD'        => $acc->tpvcod,
        'IP'            => $ip,
        'NOM_OPERATEUR' => '',
        'PSW_OPERATEUR' => '',
        'NUM_OPERATEUR' => '',
        'timeStamp'     => '',
    ];

    // Build row with defaults (used if error)
    $row = [
        'name'        => $acc->name,
        'comcod'      => $acc->comcod,
        'tpvcod'      => $acc->tpvcod,
        'code_client' => '—',
        'max_srd'     => null,
        'conso_srd'   => null,
        'balance'     => null,
        'conso_srdmp' => null,
        'max_srdmp'   => null,
        'status'      => 'Error',
    ];

    // Normalize token (accept "Basic xxx" or just "xxx")
    $raw = (string) $acc->authorization;
    $tokenOnly = preg_replace('/^Basic\s+/i', '', $raw);

    // Try both header spacings (some gateways are picky)
    $headerVariants = [
        'Authorization:Basic ' . $tokenOnly, // no space after colon (matches your legacy code)
        'Authorization: Basic ' . $tokenOnly // standard format
    ];

    foreach ($headerVariants as $hdr) {
        try {
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'IPCARTEClient',
                    'header'     => $hdr,
                ],
                'ssl'  => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ]);

            // WSDL mode: let PHP read the WSDL and set SOAPAction/namespace correctly
            $options = [
                'trace'          => true,
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE,
                'exceptions'     => true,
                'soap_version'   => SOAP_1_1,
                // 'user_agent'   => 'IPCARTEClient', // stream context UA is sufficient
            ];

            $client = new \SoapClient($wsdl, $options);
            $resp   = $client->getEncours($params);

            $max = isset($resp->max_srd)   ? (float)$resp->max_srd   : 0.0;
            $rem = isset($resp->conso_srd) ? (float)$resp->conso_srd : 0.0;

            $row['code_client'] = isset($resp->code_client_srd) ? $resp->code_client_srd : '—';
            $row['max_srd']     = $max;
            $row['conso_srd']   = $rem;
            $row['balance']     = $max - $rem; // Max − Remaining
            $row['conso_srdmp'] = isset($resp->conso_srdmp) ? (float)$resp->conso_srdmp : null;
            $row['max_srdmp']   = isset($resp->max_srdmp)   ? (float)$resp->max_srdmp   : null;
            $row['status']      = 'OK';

            // Optional debug (only when APP_DEBUG=true):
            if (config('app.debug')) {
                \Log::debug('Bimedia OK ['.$acc->name.'] header variant used: '.$hdr);
                \Log::debug($client->__getLastRequestHeaders());
                \Log::debug($client->__getLastRequest());
                \Log::debug($client->__getLastResponseHeaders());
                \Log::debug($client->__getLastResponse());
            }
            return $row;
        } catch (\Throwable $e) {
            // If fault says "Not Allowed", we try the next header variant; otherwise we bail
            $msg = $e->getMessage();
            if (stripos($msg, 'Not Allowed') === false) {
                if (config('app.debug')) {
                    try {
                        \Log::warning('Bimedia error ['.$acc->name.'] '.$msg);
                        \Log::debug(isset($client) ? $client->__getLastRequestHeaders() : '—');
                        \Log::debug(isset($client) ? $client->__getLastRequest() : '—');
                        \Log::debug(isset($client) ? $client->__getLastResponseHeaders() : '—');
                        \Log::debug(isset($client) ? $client->__getLastResponse() : '—');
                    } catch (\Throwable $ignored) {}
                }
                $row['status'] = 'Error: '.$msg;
                return $row;
            }
            // else: continue loop to try the next header style
        }
    }

    // If both header variants failed:
    $row['status'] = 'Error: Not Allowed';
    return $row;
}

}
