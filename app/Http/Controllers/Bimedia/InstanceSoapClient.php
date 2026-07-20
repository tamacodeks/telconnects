<?php
namespace App\Http\Controllers\Bimedia;
use SoapClient;
use SoapFault;
use SoapHeader;

class InstanceSoapClient extends BaseSoapController implements InterfaceInstanceSoap
{
    public static function init(){
        $wsdlUrl = self::getWsdl();
            $wsd= 'https://wholesalers.bimedia-it.com/srdws/srdtel.wsdl';
        try {
                $soapClientOptions = [
                    'location' => $wsdlUrl,
                    'uri' => $wsdlUrl,
                    'trace' => true,
                    'stream_context' => self::generateContext(),
                    'cache_wsdl'     => WSDL_CACHE_NONE
                ];
                return new SoapClient($wsd, $soapClientOptions) ;
            }
            catch (SoapFault $fault)
            {
                echo 'Caught exception: ',  $fault->getMessage(), "\n";
            }
    }
}