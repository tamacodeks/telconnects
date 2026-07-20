<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Bimedia\BaseSoapController;
use App\Http\Controllers\Bimedia\InstanceSoapClient;
use app\Library\ApiHelper;
use App\Models\AledaServiceRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class DematSoapBimediaController extends BaseSoapController
{
    private $service;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Get prepaidBalance from ALEDA Service
     * @return object
     */
    function sellDematBimedia($product_code)
    {

        $params = [
            'COMCOD' => config('bimedia.comcod'),
            'TPVCOD' => config('bimedia.tpvcod'),
            'IP' => config('bimedia.ip_address'),
            'NOM_OPERATEUR' => '',
            'PSW_OPERATEUR' =>  '',
            'NUM_OPERATEUR' => '',
            'timeStamp' => '',
            'codeProduit' => $product_code,
            'nombreUnites' => config('bimedia.nombreUnites'),
            'typeOperation' => config('bimedia.typeOperation'),
            'referenceClient' =>'',
            'referenceDestination' => '',
            'champOptionnel' => ''
        ];

        self::setWsdl(config('bimedia.web_service_url'));
        $this->service = InstanceSoapClient::init();
        $response_conf = $this->service->tel($params);
             return (object)[
            'timeStamp' => $response_conf->timeStamp,
            'trxref' => $response_conf->trxref,
            'dateValidite' => $response_conf->dateValidite,
            'codeConfidentiel' => $response_conf->codeConfidentiel,
            'referenceOperateur' => $response_conf->referenceOperateur,
            'montantBonus' => $response_conf->montantBonus,
            'message1' =>  $response_conf->message1,
            'message2' =>  $response_conf->message2,
            'message3' => $response_conf->message3,
            'ticket' =>  $response_conf->ticket,
        ];
        Log::info("SellDematModeES Confirm Action params data for User ".auth()->user()->id."_".auth()->user()->username,[$filtered_conf->all()]);
    }

    function FetchCatalogue()
    {
        $params = [
        'COMCOD' => config('bimedia.comcod'),
        'TPVCOD' => config('bimedia.tpvcod'),
        'IP' => config('bimedia.ip_address'),
        'NOM_OPERATEUR' => '',
        'PSW_OPERATEUR' =>  '',
        'NUM_OPERATEUR' => '',
        'timeStamp' => '',
        ];
        try {
            self::setWsdl(config('bimedia.web_service_url'));
            $this->service = InstanceSoapClient::init();
            $response_conf = $this->service->catalogue($params);
            return $response_conf;
        }
        catch(\Exception $e){
            return false;
        }
    }

    function FetchBalance()
    {
        $params = [
            'COMCOD' => config('bimedia.comcod'),
            'TPVCOD' => config('bimedia.tpvcod'),
            'IP' => config('bimedia.ip_address'),
            'NOM_OPERATEUR' => '',
            'PSW_OPERATEUR' =>  '',
            'NUM_OPERATEUR' => '',
            'timeStamp' => '',
        ];
        try {
            self::setWsdl(config('bimedia.web_service_url'));
            $this->service = InstanceSoapClient::init();
            $response_conf = $this->service->getEncours($params);
            return $response_conf;
        } catch(\Exception $e){
            return false;
        }

    }

    function acknowledgement($demat_soap)
    {
        $params = [
            'COMCOD' => config('bimedia.comcod'),
            'TPVCOD' => config('bimedia.tpvcod'),
            'IP' => config('bimedia.ip_address'),
            'NOM_OPERATEUR' => '',
            'PSW_OPERATEUR' =>  '',
            'NUM_OPERATEUR' => '',
            'timeStamp' => '',
            'trxref' => $demat_soap->trxref,
            'referenceOperateur' => $demat_soap->referenceOperateur,
            'typePaiement' => '',
            'photoIdType' => '',
            'photoIdNumber' => '',
            'photoIdExp' => '',
            'email' => '',
            'telephone' => '',
        ];
        try {
            self::setWsdl(config('bimedia.web_service_url'));
            $this->service = InstanceSoapClient::init();
            $response_conf = $this->service->acquittement($params);
            return $response_conf;
        }
        catch(\Exception $e){
            return false;
        }
    }


}
