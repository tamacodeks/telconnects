<?php

namespace App\Http\Controllers\Service\V2;

use App\Http\Controllers\Controller;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class TamaTopupV2BaseController extends Controller
{
    protected $service_id = 2;
    protected $decipher;
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->decipher = new SecurityHelper();
        $this->middleware(function ($request, $next) {
            if (API_TOKEN == '' || API_END_POINT == '') {
                AppHelper::logger('warning', 'API SETTINGS ERROR', 'Missing API Token or API end point url', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            if (AppHelper::user_access($this->service_id, auth()->user()->id) == 0) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access tamatopup service', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            if (\app\Library\AppHelper::skip_service_as_menu('tama-topup') == false) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access TamaTopup service but parent of this user does not have a access', request()->all());
                return redirect('dashboard')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }
            $this->client = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 180,
            ]);
            return $next($request);
        });
    }

    protected function callProviderApi($method, $uri, $params, array $options = [])
    {
        $clientOptions = array_merge([
            'base_uri' => API_END_POINT,
            'timeout'  => 180,
            'connect_timeout' => 10,
        ], $options);

        $client = new Client($clientOptions);
        try {
            if ($method === 'GET') {
                $response = $client->request($method, $uri . '?' . http_build_query($params), [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . API_TOKEN,
                    ],
                ]);
            } else {
                $response = $client->request($method, $uri, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . API_TOKEN,
                    ],
                    'form_params' => $params,
                ]);
            }
            if ($response->getStatusCode() == 200) {
                return json_decode((string) $response->getBody(), true);
            }
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : $exception->getMessage();
            return [
                'status' => $response ? $response->getStatusCode() : 500,
                'message' => $responseBody,
                'data' => [],
            ];
        }
    }

    protected function encryptTopupInputs(array $inputs, array $except = ['_token', '_method'])
    {
        $encrypted = [];
        foreach ($inputs as $key => $value) {
            if (in_array($key, $except, true)) {
                continue;
            }
            $encrypted[$key] = $this->encryptTopupValue($value);
        }

        return $encrypted;
    }

    protected function decryptTopupRequest(Request $request, array $except = ['_token', '_method'])
    {
        $request->replace($this->decryptTopupInputs($request->all(), $except));
    }

    protected function decryptTopupInputs(array $inputs, array $except = ['_token', '_method'])
    {
        $decrypted = [];
        foreach ($inputs as $key => $value) {
            if (in_array($key, $except, true)) {
                $decrypted[$key] = $value;
                continue;
            }
            $decrypted[$key] = $this->decryptTopupValue($value, $key);
        }

        return $decrypted;
    }

    private function encryptTopupValue($value)
    {
        if (is_array($value)) {
            return array_map(function ($item) {
                return $this->encryptTopupValue($item);
            }, $value);
        }

        return SecurityHelper::randomEncDec('ec', (string) $value);
    }

    private function decryptTopupValue($value, $field)
    {
        if (is_array($value)) {
            return array_map(function ($item) use ($field) {
                return $this->decryptTopupValue($item, $field);
            }, $value);
        }

        $decrypted = SecurityHelper::randomEncDec('de', (string) $value);
        if ($decrypted === false) {
            throw new \InvalidArgumentException('Invalid encrypted topup field: ' . $field);
        }

        return $decrypted;
    }
}
