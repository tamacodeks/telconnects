<?php
/**
 * Created by Decipher Lab.
 * User: Prabakar
 * Date: 03-Apr-18
 * Time: 11:44 AM
 */

namespace app\Library;


use App\Models\AledaServiceRequest;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiHelper
{
    /**
     * API Common response format
     * @param $code
     * @param $http_code
     * @param $message
     * @param array $arr
     * @return \Illuminate\Http\JsonResponse
     */
    static function response($code,$http_code,$message,$arr=[])
    {
        $resp = (object)array(
            'data' => array(
                'code' => $code,
                'http_code' => $http_code,
                'message' => $message,
                'result' => $arr
            )
        );
        $headers = array(
            'Content-Type: application/json'
        );
        return response()->json($resp, $http_code, $headers);
    }

    static function generateTransID(){
        //The format is the following: XXXXXXXXXXXXXAAMMJJHHMMSS (24)
        //• X..X : configId (12) provided by Aleda every unique terminal
        //• AA : year
        //• MM : month
        //• JJ : day
        //• HH : hour
        //• MM : minute
        //• SS : second
        do {
            $config_id = config('aleda.config_id').date('y').date('m').date('d').date('h').date('i').date('s')."_".auth()->user()->id;
        } while (!empty(AledaServiceRequest::where('trans_id', $config_id)->first()));
        return $config_id;
    }


}