<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Api\DematSoapController;
use App\Http\Controllers\Api\DematSoapBimediaController;
use app\Library\ApiHelper;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use app\Library\ServiceHelper;
use App\Models\CallingCard;
use App\Models\CallingCardAccess;
use App\Models\CallingCardPin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PinHistory;
use App\Models\TelecomProvider;
use App\Models\TelecomProviderConfig;
use App\Models\SeriveProvider;
use App\Providers\RouteServiceProvider;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\Bimedia_statistics;
use App\Models\CallingCardTransaction;
use App\Models\Log as log_data;
use Illuminate\Support\Facades\DB;
class CallingCardController extends Controller
{
    private $log_title;
    private $decipher;
    private $balance;
    function __construct()
    {
        parent::__construct();
        $this->log_title = "Calling Cards";
        $this->decipher = new SecurityHelper();
        $balance = Bimedia_statistics::orderBy('id',"DESC")->first();
        $this->balance = $balance->new_balance;
        if($balance->new_balance < 500){
            Log::emergency(APP_NAME." Low Balance Alert ". $balance->new_balance);
        }
    }

    private function translateOrFallback($key, $fallback)
    {
        $message = trans($key);

        return $message === $key ? $fallback : $message;
    }

    private function unableToPrintMessage()
    {
        return $this->translateOrFallback('myservice.unable_to_print', 'Unable to print this card right now.');
    }

    private function callingCardServiceBalance($masterRetailer)
    {
        if (!$masterRetailer) {
            Log::warning('Calling card print could not find master retailer for service balance.');
            return '0.00';
        }

        $oldCCServiceBalance = CallingCardTransaction::select('balance')
            ->lockforUpdate()
            ->where('user_id', $masterRetailer->id)
            ->orderBy('id', "DESC")
            ->first();

        if (!$oldCCServiceBalance) {
            Log::warning('Calling card service balance was empty; starting ledger from zero.', [
                'master_retailer_id' => $masterRetailer->id
            ]);
            return '0.00';
        }

        return $oldCCServiceBalance->balance;
    }

    /**
     * View Calling card telecom providers
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index()
    {
        $operator_type = SeriveProvider::select('primary')->first();
        $this->data['page_title'] = "Calling cards";
        if($operator_type->primary == 'Aleda')
        {
            $this->data['telecom_providers'] = TelecomProviderConfig::select('id','name')->get();
        }
        else
        {
			$this->data['telecom_providers'] = TelecomProviderConfig::where('bimedia_card', 1)
			->select('id', 'name')
			->orderBy('ordering')
			->get(); 
        }
//		if($this->balance < 500){
//			 return view('service.calling-card.temporary');
//		}else{
        return view('service.calling-card.index',$this->data);
//		}
    }

    /**
     * View Denominations by encrypted provider id
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function denominations($id)
    {
//        dd($this->decipher->decrypt('53k7er'));
        $dec_id = $this->decipher->decrypt($id);
        $card = TelecomProviderConfig::find($dec_id);
        $this->data['page_title'] = optional($card)->name;
        $operator_type = SeriveProvider::select('primary')->first();
        if($operator_type->primary == 'Aleda')
        {
            $this->data['cards'] = TelecomProvider::where('tp_config_id',$dec_id)->select('id','name','description','face_value')->orderBy('ordering',"ASC")->get();
            $this->data['cardlink'] = 'calling-cards/print';
        }
        else
        {
            $this->data['cards'] = TelecomProvider::where('tp_config_id',$dec_id)->where('bimedia_card',1)->select('id','name','description','face_value','is_card')->orderBy('ordering',"ASC")->get();
            $this->data['cardlink'] = 'callings-cards';
        }
        return view('service.calling-card.cards',$this->data);
    }

    /**
     * Get Single card to print
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    function bimedia_card_fetch($id)
    {
        $check_card = log_data::where('title', 'card')->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
        if($check_card) {
            $date = strtotime($check_card->created_at);
            $date2 = strtotime(date("Y-m-d H:i:s", time() - 25));
            if ($date >= $date2) {
                return redirect('calling-cards')
                    ->with('message', trans('common.common_card_validation'))
                    ->with('message_type', 'warning');
            }
        }
        $dec_id = $this->decipher->decrypt($id);
        $provider = TelecomProvider::find($dec_id);
        $this->data['page_title'] = $provider->name.' '.AppHelper::formatAmount('EUR',$provider->face_value);
        $this->data['card_name'] = $provider->name;
        $this->data['card_id'] = $this->decipher->encrypt($provider->tp_config_id);
        $this->data['provider'] = $provider;
        $check_card_activate = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
            ->where('calling_cards.telecom_provider_id',$dec_id)
            ->where('calling_cards.status','1')
            ->where('calling_cards.activate','1')
            ->where('calling_card_pins.is_used','0')
            ->where('calling_card_pins.is_used','0')
            ->where('calling_card_pins.is_locked', '=', '0')
            ->orderBy('calling_card_pins.id', 'ASC')
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'calling_cards.buying_price',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();
        if($check_card_activate)
        {
            $check_card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
                ->where('calling_cards.telecom_provider_id',$dec_id)
                ->where('calling_cards.status','1')
                ->where('calling_cards.activate','1')
                ->where('calling_card_pins.is_used','0')
                ->where('calling_card_pins.is_locked', '=', '1')
                ->where('calling_card_pins.locked_by', '=', auth()->user()->id)
                ->orderBy('calling_card_pins.id', 'ASC')
                ->select([
                    'calling_cards.id as cc_id',
                    'calling_cards.name',
                    'calling_cards.description',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_cards.buying_price',
                    'calling_card_pins.id as ccp_id',
                ])
                ->first();
            if($check_card)
            {
                $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                if($check_limit !=NULL)
                {
                    if (ServiceHelper::limit_check(auth()->user()->id, $check_card->buying_price)) {
                        $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                        $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                        $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                        $blink_limit = str_replace('-', '', $r_bal);
                        $manager_id =(auth()->user()->parent_id);
                        if($manager_id != '')
                        {
                            $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email,'balaji@prepaysolution.in'];
                        }
                        else
                        {
                            $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email];
                        }
                        $send_email_data = array(
                            'retailer_name' => auth()->user()->username,
                            'manager_name' => $result->username,
                            'current_bal' => $getBalance,
                            'total_limit' => $daily_limit,
                            'current_limit' => $blink_limit,
                        );
                        \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                            $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                            $message->to($emails)->subject('DEMAT PRO Limit Alert');
                        });
                        AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $check_card);
                        Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                        return redirect('calling-cards')
                            ->with('message', trans('common.contact_manager'))
                            ->with('message_type', 'warning');
                    }
                }
                $card = $check_card;//return this card
                Log::info($card->name."(".$card->cc_id.") card info fetched again by ".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                AppHelper::logger('Info','card',"Normal card fetch Check");
                $this->data['card'] = $card;
                $this->data['card_service'] = "true";
                return view('service.calling-card.print', $this->data);
            }
            else
            {

                $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                if ($check_limit != NULL) {
                    if (ServiceHelper::limit_check(auth()->user()->id, $check_card_activate->buying_price)) {
                        $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                        $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                        $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                        $blink_limit = str_replace('-', '', $r_bal);
                        $manager_id = (auth()->user()->parent_id);
                        if ($manager_id != '') {
                            $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email, 'balaji@prepaysolution.in'];
                        } else {
                            $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email];
                        }
                        $send_email_data = array(
                            'retailer_name' => auth()->user()->username,
                            'manager_name' => $result->username,
                            'current_bal' => $getBalance,
                            'total_limit' => $daily_limit,
                            'current_limit' => $blink_limit,
                        );
                        \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                            $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                            $message->to($emails)->subject('DEMAT PRO Limit Alert');
                        });
                        AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $check_card_activate);
                        Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                        return redirect('calling-cards')
                            ->with('message', trans('common.contact_manager'))
                            ->with('message_type', 'warning');
                    }
                }

                CallingCardPin::where('id', $check_card_activate->ccp_id)->update([
                    'is_locked' => 1,
                    'locked_by' => auth()->user()->id,
                    'locked_at' => date('Y-m-d H:i:s')
                ]);
                AppHelper::logger('info', $check_card_activate->name, $check_card_activate->name . "(" . $check_card_activate->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                Log::info($check_card_activate->name . "(" . $check_card_activate->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                AppHelper::logger('Info','card',"New Calling card Validation Check".$check_card_activate->ccp_id);
                $this->data['card'] = $check_card_activate;
                $this->data['card_service'] = "true";
                return view('service.calling-card.print', $this->data);
            }
        }
        else {
            $check_card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
                ->where('calling_cards.telecom_provider_id',$dec_id)
                ->where('calling_cards.status','1')
                ->where('calling_card_pins.is_used','0')
                ->where('calling_card_pins.is_locked', '=', '1')
                ->where('calling_card_pins.locked_by', '=', auth()->user()->id)
                ->orderBy('calling_card_pins.id', 'ASC')
                ->select([
                    'calling_cards.id as cc_id',
                    'calling_cards.name',
                    'calling_cards.description',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_cards.buying_price',
                    'calling_card_pins.id as ccp_id',
                ])
                ->first();
            if($check_card)
            {
                $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                if($check_limit !=NULL)
                {
                    if (ServiceHelper::limit_check(auth()->user()->id, $check_card->buying_price)) {
                        $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                        $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                        $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                        $blink_limit = str_replace('-', '', $r_bal);
                        $manager_id =(auth()->user()->parent_id);
                        if($manager_id != '')
                        {
                            $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email,'balaji@prepaysolution.in'];
                        }
                        else
                        {
                            $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email];
                        }
                        $send_email_data = array(
                            'retailer_name' => auth()->user()->username,
                            'manager_name' => $result->username,
                            'current_bal' => $getBalance,
                            'total_limit' => $daily_limit,
                            'current_limit' => $blink_limit,
                        );
                        \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                            $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                            $message->to($emails)->subject('DEMAT PRO Limit Alert');
                        });
                        AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $check_card);
                        Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                        return redirect('calling-cards')
                            ->with('message', trans('common.contact_manager'))
                            ->with('message_type', 'warning');
                    }
                }
                $card = $check_card;//return this card
                Log::info($card->name."(".$card->cc_id.") card info fetched again by ".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                AppHelper::logger('Info','card',"Multiple1 times Calling card Validation Check");
                $this->data['card'] = $card;
                $this->data['bimedia_service'] = "true";
                return view('service.calling-card.print', $this->data);
            }
            else {
                $dematSoap = new DematSoapBimediaController();
                $bimediaBalance = $dematSoap->FetchBalance();
                if($bimediaBalance == false){
                    $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                        ->where('calling_cards.telecom_provider_id', $dec_id)
                        ->where('calling_card_pins.is_used', '0')
                        ->where('calling_cards.status', '1')
                        ->where('calling_card_pins.is_locked', '=', '0')
                        ->select([
                            'calling_cards.id as cc_id',
                            'calling_cards.name',
                            'calling_card_pins.serial',
                            'calling_cards.description',
                            'calling_cards.validity',
                            'calling_cards.access_number',
                            'calling_cards.comment_1',
                            'calling_cards.comment_2',
                            'calling_cards.buying_price',
                            'calling_card_pins.id as ccp_id',
                        ])
                        ->first();
                    if ($card) {
                        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                        if ($check_limit != NULL) {
                            if (ServiceHelper::limit_check(auth()->user()->id, $card->buying_price)) {
                                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                                $blink_limit = str_replace('-', '', $r_bal);
                                $manager_id = (auth()->user()->parent_id);
                                if ($manager_id != '') {
                                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                                    $emails = [$result->email, 'balaji@prepaysolution.in'];
                                } else {
                                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                                    $emails = [$result->email];
                                }
                                $send_email_data = array(
                                    'retailer_name' => auth()->user()->username,
                                    'manager_name' => $result->username,
                                    'current_bal' => $getBalance,
                                    'total_limit' => $daily_limit,
                                    'current_limit' => $blink_limit,
                                );
                                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                                    $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                                    $message->to($emails)->subject('DEMAT PRO Limit Alert');
                                });
                                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $card);
                                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                                return redirect('calling-cards')
                                    ->with('message', trans('common.contact_manager'))
                                    ->with('message_type', 'warning');
                            }
                        }

                        CallingCardPin::where('id', $card->ccp_id)->update([
                            'is_locked' => 1,
                            'locked_by' => auth()->user()->id,
                            'locked_at' => date('Y-m-d H:i:s')
                        ]);
                        AppHelper::logger('info', $card->name, $card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                        Log::info($card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                        AppHelper::logger('Info','card',"Multiple2 times Calling card Validation Check");
                        $this->data['card'] = $card;
                        $this->data['bimedia_service'] = "true";
                        return view('service.calling-card.print', $this->data);
                    }
                    else
                    {
                        AppHelper::logger('warning','Pin Problem',"No Pin Uploaded For This Card");
                        Log::info("No Pin Uploaded For This Card".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                        return redirect('calling-cards')
                            ->with('message', trans('myservice.no_card_found'))
                            ->with('message_type', 'warning');
                    }
                }
                $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                    ->where('calling_cards.telecom_provider_id', $dec_id)
                    ->where('calling_cards.status', '1')
                    ->select([
                        'calling_cards.id as cc_id',
                        'calling_cards.name',
                        'calling_card_pins.serial',
                        'calling_cards.description',
                        'calling_cards.validity',
                        'calling_cards.access_number',
                        'calling_cards.comment_1',
                        'calling_cards.comment_2',
                        'calling_cards.buying_price',
                        'calling_card_pins.id as ccp_id',
                    ])
                    ->first();
                if ($card) {
                    $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                    if ($check_limit != NULL) {
                        if (ServiceHelper::limit_check(auth()->user()->id, $card->buying_price)) {
                            $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                            $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                            $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                            $blink_limit = str_replace('-', '', $r_bal);
                            $manager_id = (auth()->user()->parent_id);
                            if ($manager_id != '') {
                                $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                                $emails = [$result->email, 'balaji@prepaysolution.in'];
                            } else {
                                $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                                $emails = [$result->email];
                            }
                            $send_email_data = array(
                                'retailer_name' => auth()->user()->username,
                                'manager_name' => $result->username,
                                'current_bal' => $getBalance,
                                'total_limit' => $daily_limit,
                                'current_limit' => $blink_limit,
                            );
                            \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                                $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                                $message->to($emails)->subject('DEMAT PRO Limit Alert');
                            });
                            AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $card);
                            Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                            return redirect('calling-cards')
                                ->with('message', trans('common.contact_manager'))
                                ->with('message_type', 'warning');
                        }
                    }

                    CallingCardPin::where('id', $card->ccp_id)->update([
                        'is_locked' => 1,
                        'locked_by' => auth()->user()->id,
                        'locked_at' => date('Y-m-d H:i:s')
                    ]);
                    AppHelper::logger('info', $card->name, $card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                    Log::info($card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                    AppHelper::logger('Info','card',"Multiple3 times Calling card Validation Check");
                    $this->data['card'] = $card;
                    $this->data['bimedia_service'] = "true";
                    return view('service.calling-card.print', $this->data);
                }
                else
                {
                    AppHelper::logger('warning','Pin Problem',"No Pin Uploaded For This Card");
                    Log::info("No Pin Uploaded For This Card".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                    return redirect('calling-cards')
                        ->with('message', trans('myservice.no_card_found'))
                        ->with('message_type', 'warning');
                }
            }
        }
    }
    /**
     * Get Single card to print
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    function mycalling_card_fetch($id)
    {
        $check_card = log_data::where('title', 'card')->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
        if($check_card) {
            $date = strtotime($check_card->created_at);
            $date2 = strtotime(date("Y-m-d H:i:s", time() - 25));
            if ($date >= $date2) {
                return redirect('calling-cards')
                    ->with('message', trans('common.common_card_validation'))
                    ->with('message_type', 'warning');
            }
        }

        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
		$dec_id = $this->decipher->decrypt($id);
        $provider = TelecomProvider::find($dec_id);
        $this->data['page_title'] = $provider->name.' '.AppHelper::formatAmount('EUR',$provider->face_value);
        $this->data['card_name'] = $provider->name;
        $this->data['card_id'] = $this->decipher->encrypt($provider->tp_config_id);
        $this->data['provider'] = $provider;
        $this->data['face_value'] = $provider->face_value;
        $this->data['description'] = $provider->description;
        $this->data['cus_id'] = auth()->user()->cust_id;
        $this->data['telecom_provider_id'] =$provider['id'];
        if ($check_limit != NULL) {
            if (ServiceHelper::limit_check(auth()->user()->id, $provider->face_value)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id = (auth()->user()->parent_id);
                if ($manager_id != '') {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email, 'balaji@prepaysolution.in'];
                } else {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                    $message->to($emails)->subject('DEMAT PRO Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order');
                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                return redirect('calling-cards')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        $client = new Client([
            'base_uri' => API_END_POINT,
            'timeout'  => 120,
        ]);
        $ccResponse = $client->request('POST', 'Mycards', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . API_TOKEN
            ],
            'form_params' => $this->data
        ]);
        if ($ccResponse->getStatusCode() == 200) {
            $response_data = json_decode((string)$ccResponse->getBody(), true);
        }
        $response = $response_data['data']['result']['card_info'];
        $this->data['cards'] = $response;
        return view('service.calling-card.printmycard', $this->data);

    }

    function print_mycard(Request $request)
    {
        if(!$request->ajax()){
            AppHelper::logger('warning',$this->log_title,$request->user()->username. ' - access violation trying to access print card directly',$request);
            return ApiHelper::response('500',200,trans('common.access_violation'));
        }
        $validator = Validator::make($request->all(),[
            'pin_id' => 'required',
        ],[
            "pin_id.required" => $this->unableToPrintMessage(),
        ]);
        if($validator->fails())
        {
            AppHelper::logger('warning',$this->log_title,'Validation failed',$request->all());
            return ApiHelper::response('401',200,AppHelper::create_error_bag($validator));
        }
        $card_info = CallingCard::where('telecom_provider_id', $request->telecom_provider_id)->where('face_value',$request->face_value)->first();
        if(!$card_info){
            AppHelper::logger('warning',$this->log_title,'No such card was found!',$request->all());
            return ApiHelper::response('404',200,$this->unableToPrintMessage());
        }
        $public_price = $card_info['face_value'];
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check(auth()->user()->id, $public_price)) {
                //limit exceeed
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                    $message->to($emails)->subject('DEMAT PRO Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                return ApiHelper::response('400',200,trans('common.parent_rule_failed'));
            }
        }
        //lets check with the paren have sufficient balance or credit limit for this order
        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $public_price,7)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit to confirm Calling Card order', $request->all());
            Log::warning('Calling card Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
        }
        if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card_info->id)){
            AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$request->all());
            return ApiHelper::response('503',200,$this->unableToPrintMessage());
        }
        $order_amount = ServiceHelper::get_user_rate_table(auth()->user()->id,$card_info->id);
        $user_balance = AppHelper::getBalance(auth()->user()->id,'EUR',false);
        $user_credit_limit = AppHelper::get_credit_limit(auth()->user()->id);
        if(isset($order_amount->sale_price)){
            if ($user_balance < $order_amount->sale_price) {
                //check with credit limit
                if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                    AppHelper::logger('warning',$this->log_title,auth()->user()->username . ' does not have enough balance or credit limit to confirm Calling Card order', $request->all());
                    return ApiHelper::response('503',200,trans('myservice.err_no_balance'));
                }
            }
        }else{
            AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $request->all());
            return ApiHelper::response('400',200,trans('common.service_not_avail'));
        }
        $pin_printed_time = date('Y-m-d H:i:s');
        $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');
        try{
            $this->data['cus_id'] = auth()->user()->cust_id;
            $this->data['pin_id'] =$request->pin_id;
            $client = new Client([
                'base_uri' => API_END_POINT,
                'timeout'  => 120,
            ]);
            $ccResponse = $client->request('POST', 'calling-cards/confirm', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer " . API_TOKEN
                ],
                'form_params' => $this->data
            ]);

            if ($ccResponse->getStatusCode() == 200) {
                $response_data = json_decode((string)$ccResponse->getBody(), true);
                if (isset($response_data['data']['result'])) {
                    $response = $response_data['data']['result'];
                    $dec_pin = $response['pin'];
                    $dec_serial = $response['serial'];
                }
            }else{

                return ApiHelper::response('404',200,$this->unableToPrintMessage());
            }

            \DB::beginTransaction();
            //order comment
            $order_comment = "Retailer " . auth()->user()->username . " used card " . $card_info->name . " " . $card_info->face_value;
            //user order and transaction
            $trans_id = ServiceHelper::sync_transaction(auth()->user()->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
            $order_id = Order::insertGetId([
                'date' => $pin_printed_time,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $root_txn_id,
                'comment' => $order_comment,
                'currency' => "EUR",
                'public_price' => $public_price,
                'buying_price' => $order_amount->buying_price,
                'order_amount' => $order_amount->sale_price,
                'sale_margin' => $public_price - $order_amount->sale_price,
                'grand_total' => $order_amount->sale_price,
                'transaction_id' => $trans_id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_operator' => $card_info->name,
                'app_currency' => "EUR",
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find(auth()->user()->parent_id);
//            $calling_card = CallingCard::find($request->cc_id);
            $calling_card = CallingCard::where('telecom_provider_id', $request->telecom_provider_id)->where('face_value',$request->face_value)->first();
            $operator_type = SeriveProvider::select('primary')->first();
            if($operator_type->primary == 'Aleda')
            {
                $parent_buying_price = $calling_card->buying_price;
            }
            else
            {
                $parent_buying_price = $calling_card->buying_price;

            }
            if(!empty(auth()->user()->parent_id) && $parent_user && $parent_user->group_id != 2){
                $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$card_info->id);
                $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                $parent_credit_limit = AppHelper::get_credit_limit($parent_user->id);
                if(isset($parent_order_amount->sale_price)){
                    if ($parent_user_balance < $parent_order_amount->sale_price) {
                        //check with credit limit
                        if (ServiceHelper::check_with_credit_limit($parent_order_amount->sale_price, $parent_user_balance, $parent_credit_limit) == false) {
                            Log::warning($parent_user->username . ' does not have enough balance or credit limit to confirm client Calling Card order',[$request->all()]);
                            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                        }
                    }
                }else{
                    Log::warning('Rate Table Sale Price Error '. $parent_user->username . ' rate table sale price may be 0', [$request->all()]);
                    return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                }
                $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                //parent user order and transaction
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                //by retailer to manager
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_order_amount->sale_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'transaction_id' => $parent_trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
                //by manager to tamashop
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $parent_user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $parent_order_amount->sale_price,
                    'sale_margin' => $parent_order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $parent_order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            else{
                //by user to tamashop
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            AppHelper::logger('success',$this->log_title,auth()->user()->username." pin id ".$request->ccp_id." was success",$request->all());

            PinHistory::insert([
                'cc_id' => $calling_card->id,
                'date' => $pin_printed_time,
                'name' => str_replace("â‚¬", "€", $card_info->name),
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'is_aleda' => 1,
                'validity' => (new \DateTime())->modify('+5 months')->format('Y-m-d'),
                'used_by' => auth()->user()->id
            ]);
            $ret_data = [
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'time_printed' => $pin_printed_time,
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ];
            \DB::commit();
            return ApiHelper::response('200',200,trans('myservice.print_success'),$ret_data);
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
            Log::emergency(auth()->user()->username." pin print exception => ".$e->getMessage(),[$e]);
            return ApiHelper::response('500',200,$this->unableToPrintMessage());
        }

    }
    function print_card($id)
    {
        $check_card = log_data::where('title', 'card')->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
        if($check_card) {
            $date = strtotime($check_card->created_at);
            $date2 = strtotime(date("Y-m-d H:i:s", time() - 25));
            if ($date >= $date2) {
                return redirect('calling-cards')
                    ->with('message', trans('common.common_card_validation'))
                    ->with('message_type', 'warning');
            }
        }
        $dec_id = $this->decipher->decrypt($id);
        $provider = TelecomProvider::find($dec_id);
        $this->data['page_title'] = $provider->name.' '.AppHelper::formatAmount('EUR',$provider->face_value);
        $this->data['card_name'] = $provider->name;
        $this->data['card_id'] = $this->decipher->encrypt($provider->tp_config_id);
        $this->data['provider'] = $provider;
        // lets check any cards are locked by this user
        // if yes return the card bypass checking the access and rate table
        $check_card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
            ->where('calling_cards.telecom_provider_id',$dec_id)
            ->where('calling_cards.status','1')
            ->where('calling_card_pins.is_used','0')
            ->where('calling_card_pins.is_locked', '=', '1')
            ->where('calling_card_pins.locked_by', '=', auth()->user()->id)
            ->orderBy('calling_card_pins.id', 'ASC')
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'calling_cards.buying_price',
                'calling_card_pins.id as ccp_id',
            ])
            ->first();
        if($check_card)
        {
            $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
            if($check_limit !=NULL)
            {
                if (ServiceHelper::limit_check(auth()->user()->id, $check_card->buying_price)) {
                    $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                    $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                    $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                    $blink_limit = str_replace('-', '', $r_bal);
                    $manager_id =(auth()->user()->parent_id);
                    if($manager_id != '')
                    {
                        $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                        $emails = [$result->email,'balaji@prepaysolution.in'];
                    }
                    else
                    {
                        $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                        $emails = [$result->email];
                    }
                    $send_email_data = array(
                        'retailer_name' => auth()->user()->username,
                        'manager_name' => $result->username,
                        'current_bal' => $getBalance,
                        'total_limit' => $daily_limit,
                        'current_limit' => $blink_limit,
                    );
                    \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                        $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                        $message->to($emails)->subject('DEMAT PRO Limit Alert');
                    });
                    AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $check_card);
                    Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                    return redirect('calling-cards')
                        ->with('message', trans('common.contact_manager'))
                        ->with('message_type', 'warning');
                }
            }
            $card = $check_card;//return this card
            Log::info($card->name."(".$card->cc_id.") card info fetched again by ".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
        }
        else
        {
            $card = CallingCard::join('calling_card_pins','calling_card_pins.cc_id','calling_cards.id')
                ->where('calling_cards.telecom_provider_id',$dec_id)
                ->where('calling_card_pins.is_used','0')
                ->where('calling_cards.status','1')
                ->where('calling_card_pins.is_locked', '=', '0')
                ->select([
                    'calling_cards.id as cc_id',
                    'calling_cards.name',
                    'calling_cards.description',
                    'calling_cards.validity',
                    'calling_cards.access_number',
                    'calling_cards.comment_1',
                    'calling_cards.comment_2',
                    'calling_card_pins.id as ccp_id',
                ])
                ->first();
            if(!$card){
                Log::info('card is not available, changing route to aleda service');
                //lets check balance with aleda service
                $dematSoap = new DematSoapController();
                $balance = $dematSoap->getIncurBalance();
                if (empty($balance) || is_numeric($balance) == false) {
                    $card = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                        ->where('calling_cards.telecom_provider_id', $dec_id)
                        ->where('calling_card_pins.is_used', '0')
                        ->where('calling_cards.status', '1')
                        ->where('calling_card_pins.is_locked', '=', '0')
                        ->select([
                            'calling_cards.id as cc_id',
                            'calling_cards.name',
                            'calling_card_pins.serial',
                            'calling_cards.description',
                            'calling_cards.validity',
                            'calling_cards.access_number',
                            'calling_cards.comment_1',
                            'calling_cards.comment_2',
                            'calling_cards.buying_price',
                            'calling_card_pins.id as ccp_id',
                        ])
                        ->first();
                    if ($card) {
                        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                        if ($check_limit != NULL) {
                            if (ServiceHelper::limit_check(auth()->user()->id, $card->buying_price)) {
                                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false));
                                $blink_limit = str_replace('-', '', $r_bal);
                                $manager_id = (auth()->user()->parent_id);
                                if ($manager_id != '') {
                                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                                    $emails = [$result->email, 'balaji@prepaysolution.in'];
                                } else {
                                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                                    $emails = [$result->email];
                                }
                                $send_email_data = array(
                                    'retailer_name' => auth()->user()->username,
                                    'manager_name' => $result->username,
                                    'current_bal' => $getBalance,
                                    'total_limit' => $daily_limit,
                                    'current_limit' => $blink_limit,
                                );
                                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                                    $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                                    $message->to($emails)->subject('DEMAT PRO Limit Alert');
                                });
                                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $card);
                                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                                return redirect('calling-cards')
                                    ->with('message', trans('common.contact_manager'))
                                    ->with('message_type', 'warning');
                            }
                        }

                        CallingCardPin::where('id', $card->ccp_id)->update([
                            'is_locked' => 1,
                            'locked_by' => auth()->user()->id,
                            'locked_at' => date('Y-m-d H:i:s')
                        ]);
                        AppHelper::logger('info', $card->name, $card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                        Log::info($card->name . "(" . $card->cc_id . ") card has been locked by " . auth()->user()->username . ' at ' . date("Y-m-d H:i:s"));
                        $this->data['card'] = $card;
                        AppHelper::logger('Info','card',"Multiple times Calling card Validation Check");
                        $this->data['aleda_service'] = "true";
                        return view('service.calling-card.print', $this->data);
                    }
                    else
                    {
                        AppHelper::logger('warning','Pin Problem',"No Pin Uploaded For This Card");
                        Log::info("No Pin Uploaded For This Card".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                        return redirect('calling-cards')
                            ->with('message', trans('myservice.no_card_found'))
                            ->with('message_type', 'warning');
                    }
                }
                $balance = number_format(($balance / 100), 2, '.', '');
                $card_info = CallingCard::join('calling_card_pins', 'calling_card_pins.cc_id', 'calling_cards.id')
                    ->where('calling_cards.telecom_provider_id', $dec_id)
                    ->where('calling_cards.status', '1')
                    ->select([
                        'calling_cards.id as cc_id',
                        'calling_cards.name',
                        'calling_cards.description',
                        'calling_cards.validity',
                        'calling_cards.access_number',
                        'calling_cards.comment_1',
                        'calling_cards.comment_2',
                        'calling_card_pins.id as ccp_id',
                        'calling_cards.face_value',
                        'calling_cards.buying_price',
                        'calling_cards.aleda_product_code',
                    ])
                    ->first();
                if(!$card_info)
                {
                    AppHelper::logger('warning','Pin Problem',"No Pin Uploaded For This Card");
                    Log::info("No Pin Uploaded For This Card".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
                    return redirect('calling-cards')
                        ->with('message', trans('myservice.no_card_found'))
                        ->with('message_type', 'warning');
                }
                if ($balance < 0 || $balance < $card_info->face_value || empty($card_info->aleda_product_code)) {
                    AppHelper::logger('warning', $this->log_title . " " . optional($provider)->name, "Aleda balance not enough");
                    return redirect()->back()
                        ->with('message', trans('myservice.no_card_found'))
                        ->with('message_type', 'information');
                }
//                echo "Balance was ".$balance;
//                echo "<br>";
//                echo $card_info->face_value;
//                echo '<br>';
//                exit;
                //balance ok
                $this->data['card'] = $card_info;
                $this->data['aleda_service'] = "true";
                $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
                if($check_limit !=NULL)
                {
                    if (ServiceHelper::limit_check(auth()->user()->id, $this->data['card']->buying_price)) {
                        $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                        $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                        $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                        $blink_limit = str_replace('-', '', $r_bal);
                        $manager_id =(auth()->user()->parent_id);
                        if($manager_id != '')
                        {
                            $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email,'balaji@prepaysolution.in'];
                        }
                        else
                        {
                            $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                            $emails = [$result->email];
                        }
                        $send_email_data = array(
                            'retailer_name' => auth()->user()->username,
                            'manager_name' => $result->username,
                            'current_bal' => $getBalance,
                            'total_limit' => $daily_limit,
                            'current_limit' => $blink_limit,
                        );
                        \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                            $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                            $message->to($emails)->subject('DEMAT PRO Limit Alert');
                        });
                        AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $card_info);
                        Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                        return redirect('calling-cards')
                            ->with('message', trans('common.contact_manager'))
                            ->with('message_type', 'warning');
                    }
                }
                AppHelper::logger('Info','card',"Multiple times Calling card Validation Check");
                return view('service.calling-card.print', $this->data);
            }
            //lets check user has access for this calling card
            $user_cc_access = CallingCardAccess::where('user_id',auth()->user()->id)->where('cc_id',$card->cc_id)->where('status',1)->first();
            if(!$user_cc_access){
                //blocked user from using this card
                AppHelper::logger('warning',$this->log_title,auth()->user()->username." does not have access to use this card");
                return redirect()
                    ->back()
                    ->with('message',trans('common.access_violation'))
                    ->with('message_type','warning');
            }
            //check this user rate table
            if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card->cc_id)){
                //blocked rate table has not found or its 0
                AppHelper::logger('warning',$this->log_title,auth()->user()->username." sale_price was 0.00");
                return redirect()
                    ->back()
                    ->with('message',trans('myservice.contact_admin'))
                    ->with('message_type','warning');
            }
            //let's check this user parent_id rate table
            $parent_user = User::find(auth()->user()->parent_id);
            if($parent_user){
                if($parent_user->group_id != 2){
                    if(ServiceHelper::check_user_rate_table($parent_user->id,$card->cc_id)){
                        //blocked rate table has not found or its 0
                        AppHelper::logger('warning',$this->log_title,auth()->user()->username."->parent->".$parent_user->username ." sale_price was 0.00");
                        return redirect()
                            ->back()
                            ->with('message',trans('myservice.contact_admin'))
                            ->with('message_type','warning');
                    }
                }
            }
            //lock this card for this logged user
            CallingCardPin::where('id',$card->ccp_id)->update([
                'is_locked' => 1,
                'locked_by' => auth()->user()->id,
                'locked_at' => date('Y-m-d H:i:s')
            ]);
            Log::info($card->name."(".$card->cc_id.") card has been locked by ".auth()->user()->username.' at '.date("Y-m-d H:i:s"));
            AppHelper::logger('Info','card',"Multiple times Calling card Validation Check");
        }
        $this->data['card'] = $card;
        $this->data['aleda_service'] = "true";
        return view('service.calling-card.print',$this->data);
    }

    /**
     * Ajax - Confirm print
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function cardPrint($data){

        $user_info = User::find(auth()->user()->id);
        //lets check the card actually locked by this user
        $card_info = CallingCardPin::where('id',$data['ccp_id'])
            ->where('cc_id',$data['cc_id'])
            ->where('is_used','0')
            ->where('is_locked',1)
            ->where('locked_by',auth()->user()->id)
            ->first();
        if(!$card_info){
            AppHelper::logger('warning',$this->log_title,'No such card was found!',$data);
            return ApiHelper::response('404',200,$this->unableToPrintMessage());
        }
        $public_price = $card_info->value;
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check(auth()->user()->id, $public_price)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $data);
                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        //lets check with the paren have sufficient balance or credit limit for this order
        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $public_price,7)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit to confirm Calling Card order', $data);
            Log::warning('Calling card Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
        }
        if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card_info->cc_id)){
            AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$data);
            return ApiHelper::response('503',200,$this->unableToPrintMessage());
        }
        $order_amount = ServiceHelper::get_user_rate_table(auth()->user()->id,$card_info->cc_id);
        $user_balance = AppHelper::getBalance(auth()->user()->id,'EUR',false);
        $user_credit_limit = AppHelper::get_credit_limit(auth()->user()->id);
        if(isset($order_amount->sale_price)){
            if ($user_balance < $order_amount->sale_price) {
                //check with credit limit
                if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                    AppHelper::logger('warning',$this->log_title,auth()->user()->username . ' does not have enough balance or credit limit to confirm Calling Card order', $data);
                    return ApiHelper::response('503',200,trans('myservice.err_no_balance'));
                }
            }
        }else{
            AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $data);
            return ApiHelper::response('400',200,trans('common.service_not_avail'));
        }
        $pin_printed_time = date('Y-m-d H:i:s');
        $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');
        try{
            \DB::beginTransaction();
            //update pin status
            CallingCardPin::where('id',$data['ccp_id'])->update([
                'is_used' => 1,
                'used_by' => auth()->user()->id,
                'is_locked' => 0,
                'locked_by' => null,
                'updated_at' => $pin_printed_time,
                'updated_by' => auth()->user()->id
            ]);
            //decrypt the pin
            $secret_key = SecurityHelper::decipherEncryption($card_info->public_key . "CJJbW7SaznW7cZhVzwLo");
            $dec_pin = SecurityHelper::tamaCipher($card_info->pin, "d", $secret_key);
            //order comment
            $order_comment = "Retailer " . auth()->user()->username . " used card " . $card_info->name . " " . $card_info->value;
            //user order and transaction
            $trans_id = ServiceHelper::sync_transaction(auth()->user()->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
            $order_id = Order::insertGetId([
                'date' => $pin_printed_time,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $root_txn_id,
                'comment' => $order_comment,
                'currency' => "EUR",
                'public_price' => $public_price,
                'buying_price' => $order_amount->buying_price,
                'order_amount' => $order_amount->sale_price,
                'sale_margin' => $public_price - $order_amount->sale_price,
                'grand_total' => $order_amount->sale_price,
                'transaction_id' => $trans_id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_operator' => $card_info->name,
                'app_currency' => "EUR",
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find(auth()->user()->parent_id);
            $calling_card = CallingCard::find($data['cc_id']);
            if(!empty(auth()->user()->parent_id) && $parent_user && $parent_user->group_id != 2){
                $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$card_info->cc_id);
                $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                $parent_credit_limit = AppHelper::get_credit_limit($parent_user->id);
                if(isset($parent_order_amount->sale_price)){
                    if ($parent_user_balance < $parent_order_amount->sale_price) {
                        //check with credit limit
                        if (ServiceHelper::check_with_credit_limit($parent_order_amount->sale_price, $parent_user_balance, $parent_credit_limit) == false) {
                            Log::warning($parent_user->username . ' does not have enough balance or credit limit to confirm client Calling Card order',[$data]);
                            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                        }
                    }
                }else{
                    Log::warning('Rate Table Sale Price Error '. auth()->user()->username . ' rate table sale price may be 0', [$data]);
                    return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                }
                $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                //parent user order and transaction
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                //by retailer to manager
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_order_amount->sale_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'transaction_id' => $parent_trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
                //by manager to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $parent_user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $calling_card->buying_price,
                    'order_amount' => $parent_order_amount->sale_price,
                    'sale_margin' => $parent_order_amount->sale_price - $calling_card->buying_price,
                    'grand_total' => $parent_order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            else{
                //by user to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            //finally deduct balance from myservice balance
            $master_retailer = User::where('group_id',2)->select('id','username','currency')->orderBy('id','ASC')->first();
            $oldCCServiceBalance = $this->callingCardServiceBalance($master_retailer);
            $newCCBalance = number_format((float)$oldCCServiceBalance - (float)$calling_card->buying_price, 2, '.', '');
            Log::info('New myservice balance '.$newCCBalance);
            ServiceHelper::sync_myservice_transaction($master_retailer->id, $data['cc_id'], $pin_printed_time, 'debit', $calling_card->buying_price, $oldCCServiceBalance, $newCCBalance, $order_comment);
            PinHistory::insert([
                'cc_id' => $data['cc_id'],
                'date' => $pin_printed_time,
                'name' => str_replace("â‚¬", "€", $card_info->name),
                'pin' => $dec_pin,
                'serial' => $card_info->serial,
                'is_aleda' => 0,
                'used_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success',$this->log_title,auth()->user()->username." pin id ".$data['ccp_id']." was success",$data);
            $ret_data = [
                'pin' => $dec_pin,
                'serial' => $card_info->serial,
                'time_printed' => $pin_printed_time,
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ];
            return ApiHelper::response('200',200,trans('myservice.print_success'),$ret_data);
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
            Log::emergency(auth()->user()->username." pin print exception => ".$e->getMessage());
            return ApiHelper::response('500',200,$this->unableToPrintMessage());
        }
    }

    function aledaPrintCard(Request $request){
        $check_card = log_data::where('title', 'pin')->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
        if($check_card) {
            $date = strtotime($check_card->created_at);
            $date2 = strtotime(date("Y-m-d H:i:s", time() - 25));
            if ($date >= $date2) {
                return ApiHelper::response('400',200,trans('common.common_card_validation'));
            }
            AppHelper::logger('Info', 'pin', "Multiple times Calling card Validation Check");
        }
        $operator_type = SeriveProvider::select('primary')->first();
        if($operator_type->primary == 'Bimedia')
        {
            AppHelper::logger('info','Route Change',$request->user()->username. ' - Route Has been Changed to Bimedia From Aleda',$request);
            return ApiHelper::response('400',200,trans('common.access_violation'));
        }
        if(!$request->ajax()){
            AppHelper::logger('warning',$this->log_title,$request->user()->username. ' - access violation trying to access print card directly',$request);
            return ApiHelper::response('500',200,trans('common.access_violation'));
        }
        $validator = Validator::make($request->all(),[
            'cc_id' => 'required|exists:calling_cards,id',
            'ccp_id' => 'required|exists:calling_card_pins,id',
            'aleda_service' => "required"
        ],[
            "cc_id.required" => $this->unableToPrintMessage(),
            "ccp_id.required" => $this->unableToPrintMessage(),
            "aleda_service.required" => $this->unableToPrintMessage()
        ]);
        if($validator->fails())
        {
            AppHelper::logger('warning',$this->log_title,'Validation failed',$request->all());
            return ApiHelper::response('400',200,AppHelper::create_error_bag($validator));
        }
        $card_info = CallingCard::find($request->cc_id);
        if(!$card_info || empty($card_info->aleda_product_code)){
            AppHelper::logger('warning',$this->log_title,'No such card was found!',$request->all());
            return ApiHelper::response('404',200,$this->unableToPrintMessage());
        }
        //checking balance and changing route
        $dematSoap = new DematSoapController();
        $aledaBalance = $dematSoap->getIncurBalance();
        if(isset($aledaBalance->error)){
            return $this->cardPrint($request->all());
        }
        $public_price = $card_info->face_value;
        //lets check with the paren have sufficient balance or credit limit for this order
        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $public_price,7)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit to confirm Calling Card order', $request->all());
            Log::warning('Calling card Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
        }
        if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card_info->id)){
            AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$request->all());
            return ApiHelper::response('503',200,$this->unableToPrintMessage());
        }
        $order_amount = ServiceHelper::get_user_rate_table(auth()->user()->id,$card_info->id);
        $user_balance = AppHelper::getBalance(auth()->user()->id,'EUR',false);
        $user_credit_limit = AppHelper::get_credit_limit(auth()->user()->id);
        if(isset($order_amount->sale_price)){
            if ($user_balance < $order_amount->sale_price) {
                //check with credit limit
                if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                    AppHelper::logger('warning',$this->log_title,auth()->user()->username . ' does not have enough balance or credit limit to confirm Calling Card order', $request->all());
                    return ApiHelper::response('503',200,trans('myservice.err_no_balance'));
                }
            }
        }else{
            AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $request->all());
            return ApiHelper::response('400',200,trans('common.service_not_avail'));
        }
        $pin_printed_time = date('Y-m-d H:i:s');
        $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');

        try{
            //check the product whether ES or AS
            $catalogue_xml = Storage::disk('public')->get('catalogue/catalogue.xml');
            $ob= simplexml_load_string($catalogue_xml);
            $json  = json_encode($ob);
            $configData = json_decode($json, true);
            $collection = collect($configData['product']);
            $filtered = $collection->whereStrict('Gencod', $card_info->aleda_product_code);
            $catalogue = $filtered->first();
            if(!$catalogue){
                Log::emergency("Product Code not found for  ".$card_info->name." ".$card_info->aleda_product_code);
                throw new \Exception($this->unableToPrintMessage());
            }
            $aleda = new DematSoapController();
            if($catalogue['productType'] == "ES"){
                $dematSOAP = $aleda->sellDematModeES($card_info->aleda_product_code);
                if(isset($dematSOAP->error)){
                    throw new \Exception($dematSOAP->error);
                }
                $dec_pin = $dematSOAP->secretCode;
                $dec_serial = $dematSOAP->serialNb;
                $dec_validityDate = $dematSOAP->validityDate;
                if($dec_pin == "" || $dec_serial == ""){
                    throw new \Exception("Please try again!");
                }
            }elseif($catalogue['productType'] == "AS"){
                $dematSOAP = $aleda->sellDematModeXS($card_info->aleda_product_code);
                if(isset($dematSOAP->error)){
                    throw new \Exception($dematSOAP->error);
                }
                $productList = $dematSOAP->productASList;
                $dec_pin = $productList->secretCode;
                $dec_serial = $productList->serialNb;
                $dec_validityDate = $productList->validityDate;
                if($dec_pin == "" || $dec_serial == ""){
                    throw new \Exception("Please try again!");
                }
            }else{
                Log::emergency("Unknown Product Type for  ".$card_info->name." ".$card_info->aleda_product_code." ".$catalogue['productType']);
                throw new \Exception($this->unableToPrintMessage());
            }
            \DB::beginTransaction();
            //order comment
            $order_comment = "Retailer " . auth()->user()->username . " used card " . $card_info->name . " " . $card_info->face_value;
            //user order and transaction
            $trans_id = ServiceHelper::sync_transaction(auth()->user()->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
            $order_id = Order::insertGetId([
                'date' => $pin_printed_time,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $root_txn_id,
                'comment' => $order_comment,
                'currency' => "EUR",
                'public_price' => $public_price,
                'buying_price' => $order_amount->buying_price,
                'order_amount' => $order_amount->sale_price,
                'sale_margin' => $public_price - $order_amount->sale_price,
                'grand_total' => $order_amount->sale_price,
                'transaction_id' => $trans_id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_operator' => $card_info->name,
                'app_currency' => "EUR",
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find(auth()->user()->parent_id);
            $calling_card = CallingCard::find($request->cc_id);
            if($operator_type->primary == 'Aleda')
            {
                $parent_buying_price = $calling_card->buying_price;
            }
            else
            {
                $parent_buying_price = $calling_card->buying_price1;

            }
            if(!empty(auth()->user()->parent_id) && $parent_user && $parent_user->group_id != 2){
                $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$card_info->id);
                $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                $parent_credit_limit = AppHelper::get_credit_limit($parent_user->id);
                if(isset($parent_order_amount->sale_price)){
                    if ($parent_user_balance < $parent_order_amount->sale_price) {
                        //check with credit limit
                        if (ServiceHelper::check_with_credit_limit($parent_order_amount->sale_price, $parent_user_balance, $parent_credit_limit) == false) {
                            Log::warning($parent_user->username . ' does not have enough balance or credit limit to confirm client Calling Card order',[$request->all()]);
                            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                        }
                    }
                }else{
                    Log::warning('Rate Table Sale Price Error '. $parent_user->username . ' rate table sale price may be 0', [$request->all()]);
                    return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                }
                $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                //parent user order and transaction
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                //by retailer to manager
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_order_amount->sale_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'transaction_id' => $parent_trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
                //by manager to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $parent_user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $parent_order_amount->sale_price,
                    'sale_margin' => $parent_order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $parent_order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            else{
                //by user to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            AppHelper::logger('success',$this->log_title,auth()->user()->username." pin id ".$request->ccp_id." was success",$request->all());
            $conv_date  = str_replace("/", "-", $dec_validityDate);
            $ret_data = [
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'time_printed' => $pin_printed_time,
                'validity' => $dec_validityDate == '' ? "" : date('Y-m-d', strtotime($conv_date)),
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ];
            AppHelper::aledaStatistics($card_info->id,auth()->user()->id, $dec_serial, $dec_pin, date('Y-m-d', strtotime($conv_date)));
            PinHistory::insert([
                'cc_id' => $request->cc_id,
                'date' => $pin_printed_time,
                'name' => str_replace("â‚¬", "€", $card_info->name),
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'is_aleda' => 1,
                'validity' => date('Y-m-d', strtotime($conv_date)),
                'used_by' => auth()->user()->id
            ]);
            CallingCardPin::where('id', $request->ccp_id)->where('is_locked', 1)->update([
                'is_locked' => 0,
                'locked_by' => NULL,
                'locked_at' => NULL
            ]);
            Log::info("Aleda Response info => ",$ret_data);
            $cacheKey = md5(vsprintf("%s", [
                "Aleda-Balance"
            ]));
            \Cache::forget($cacheKey);
            getAledaBalance:
            $dematSoap = new DematSoapController();
            $aledaBalance = $dematSoap->getIncurBalance();
            if(isset($aledaBalance->error)){
                $aledaRemainBalance = '0.00';
            }else{
                if(empty($aledaBalance) || is_numeric($aledaBalance) == false){
                    AppHelper::logger('warning',$this->log_title,"Terminal may be resync happened, trigger goto procedure!");
                    //sleep for 3 seconds
                    sleep(3);
                    goto getAledaBalance;
                }else{
                    $aledaRemainBalance = AppHelper::formatAmount('EUR', number_format(($aledaBalance /100), 2, '.', ''));
                }
            }
            Log::info("aleda new balance will be $aledaRemainBalance");
            \DB::commit();
            //add it cache
            \Cache::put($cacheKey, $aledaRemainBalance, 60);
            return ApiHelper::response('200',200,trans('myservice.print_success'),$ret_data);
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
            Log::emergency(auth()->user()->username." pin print exception => ".$e->getMessage(),[$e]);
            return ApiHelper::response('500',200,$this->unableToPrintMessage());
        }
    }
    function print_card_bimedia(Request $request)
    {
        $check_card = log_data::where('title', 'pin1')->where('created_by', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
        if($check_card) {
            $date = strtotime($check_card->created_at);
            $date2 = strtotime(date("Y-m-d H:i:s", time() - 35));
            if ($date >= $date2) {
                AppHelper::logger('warning','Limit Exceed','More than 30 Sec');
                return ApiHelper::response('404',200,$this->unableToPrintMessage());
            }
        }
        AppHelper::logger('Info', 'pin1', "Printing card of pin");


        $operator_type = SeriveProvider::select('primary')->first();
        if($operator_type->primary == 'Aleda')
        {
            AppHelper::logger('info','Route Change',$request->user()->username. ' - Route Has been Changed to Aleda From Bimedia',$request);
            return ApiHelper::response('400',200,trans('common.access_violation'));
        }
        if(!$request->ajax()){
            AppHelper::logger('warning',$this->log_title,$request->user()->username. ' - access violation trying to access print card directly',$request);
            return ApiHelper::response('500',200,trans('common.access_violation'));
        }
        $validator = Validator::make($request->all(),[
            'cc_id' => 'required|exists:calling_cards,id',
            'ccp_id' => 'required|exists:calling_card_pins,id',
            'bimedia_service' => "required"
        ],[
            "cc_id.required" => $this->unableToPrintMessage(),
            "ccp_id.required" => $this->unableToPrintMessage(),
            "bimedia_service.required" => $this->unableToPrintMessage()
        ]);
        if($validator->fails())
        {
            AppHelper::logger('warning',$this->log_title,'Validation failed',$request->all());
            return ApiHelper::response('400',200,AppHelper::create_error_bag($validator));
        }
        $dematSoap = new DematSoapBimediaController();
        $aledaBalance = $dematSoap->FetchBalance();
        if($aledaBalance == false){
            return $this->cardPrint($request->all());
        }

        $card_info = CallingCard::find($request->cc_id);
        if(!$card_info || empty($card_info->bimedia_product_code)){
            AppHelper::logger('warning',$this->log_title,'No such card was found!',$request->all());
            return ApiHelper::response('404',200,$this->unableToPrintMessage());
        }
        $public_price = $card_info->face_value;
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check(auth()->user()->id, $public_price)) {
                //limit exceeed
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'DEMAT PRO');
                    $message->to($emails)->subject('DEMAT PRO Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $request->all());
                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                return ApiHelper::response('400',200,trans('common.parent_rule_failed'));
            }
        }
        //lets check with the paren have sufficient balance or credit limit for this order
        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $public_price,7)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit to confirm Calling Card order', $request->all());
            Log::warning('Calling card Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
        }
        if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card_info->id)){
            AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$request->all());
            return ApiHelper::response('503',200,$this->unableToPrintMessage());
        }
        $order_amount = ServiceHelper::get_user_rate_table(auth()->user()->id,$card_info->id);
        $user_balance = AppHelper::getBalance(auth()->user()->id,'EUR',false);
        $user_credit_limit = AppHelper::get_credit_limit(auth()->user()->id);
        if(isset($order_amount->sale_price)){
            if ($user_balance < $order_amount->sale_price) {
                //check with credit limit
                if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                    AppHelper::logger('warning',$this->log_title,auth()->user()->username . ' does not have enough balance or credit limit to confirm Calling Card order', $request->all());
                    return ApiHelper::response('503',200,trans('myservice.err_no_balance'));
                }
            }
        }else{
            AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $request->all());
            return ApiHelper::response('400',200,trans('common.service_not_avail'));
        }
        $pin_printed_time = date('Y-m-d H:i:s');
        $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');

        try{
            $dematSoap = new DematSoapBimediaController();
            $fetch_data = $dematSoap->FetchBalance();
            $previousBalance = $fetch_data->max_srd -  $fetch_data->conso_srd;
//                check the product whether ES or AS
            $bimedia = new DematSoapBimediaController();
            $dematSOAP = $bimedia->sellDematBimedia($card_info->bimedia_product_code);
            if(isset($dematSOAP->error)){
                throw new \Exception($dematSOAP->error);
            }
            $dec_pin = $dematSOAP->codeConfidentiel;
            $dec_trxref = $dematSOAP->trxref;
            $dec_serial = $dematSOAP->referenceOperateur;
            $dec_validityDate = $dematSOAP->dateValidite;
            if($dec_pin == "" || $dec_serial == ""){
                throw new \Exception("Please try again!");
            }
            \DB::beginTransaction();
            //order comment
            $order_comment = "Retailer " . auth()->user()->username . " used card " . $card_info->name . " " . $card_info->face_value;
            //user order and transaction
            $trans_id = ServiceHelper::sync_transaction(auth()->user()->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
            $order_id = Order::insertGetId([
                'date' => $pin_printed_time,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $root_txn_id,
                'comment' => $order_comment,
                'currency' => "EUR",
                'public_price' => $public_price,
                'buying_price' => $order_amount->buying_price,
                'order_amount' => $order_amount->sale_price,
                'sale_margin' => $public_price - $order_amount->sale_price,
                'grand_total' => $order_amount->sale_price,
                'transaction_id' => $trans_id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_operator' => $card_info->name,
                'app_currency' => "EUR",
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find(auth()->user()->parent_id);
            $calling_card = CallingCard::find($request->cc_id);
            $operator_type = SeriveProvider::select('primary')->first();
            if($operator_type->primary == 'Aleda')
            {
                $parent_buying_price = $calling_card->buying_price;
            }
            else
            {
                $parent_buying_price = $calling_card->buying_price1;

            }
            if(!empty(auth()->user()->parent_id) && $parent_user && $parent_user->group_id != 2){
                $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$card_info->id);
                $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                $parent_credit_limit = AppHelper::get_credit_limit($parent_user->id);
                if(isset($parent_order_amount->sale_price)){
                    if ($parent_user_balance < $parent_order_amount->sale_price) {
                        //check with credit limit
                        if (ServiceHelper::check_with_credit_limit($parent_order_amount->sale_price, $parent_user_balance, $parent_credit_limit) == false) {
                            Log::warning($parent_user->username . ' does not have enough balance or credit limit to confirm client Calling Card order',[$request->all()]);
                            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                        }
                    }
                }else{
                    Log::warning('Rate Table Sale Price Error '. $parent_user->username . ' rate table sale price may be 0', [$request->all()]);
                    return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                }
                $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                //parent user order and transaction
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                //by retailer to manager
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_order_amount->sale_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'transaction_id' => $parent_trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
                //by manager to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $parent_user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $parent_order_amount->sale_price,
                    'sale_margin' => $parent_order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $parent_order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            else{
                //by user to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            AppHelper::logger('success',$this->log_title,auth()->user()->username." pin id ".$request->ccp_id." was success",$request->all());
            $conv_date  = str_replace("/", "-", $dec_validityDate);
            $ret_data = [
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'time_printed' => $pin_printed_time,
                'validity' => $dec_validityDate == '' ? "" : date('Y-m-d', strtotime($conv_date)),
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ];
//                AppHelper::aledaStatistics($card_info->id,auth()->user()->id, $dec_serial, $dec_pin, date('Y-m-d', strtotime($conv_date)));
            PinHistory::insert([
                'cc_id' => $request->cc_id,
                'date' => $pin_printed_time,
                'name' => str_replace("â‚¬", "€", $card_info->name),
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'is_aleda' => 1,
                'validity' => date('Y-m-d', strtotime($conv_date)),
                'used_by' => auth()->user()->id
            ]);
            CallingCardPin::where('id', $request->ccp_id)->where('is_locked', 1)->update([
                'is_locked' => 0,
                'locked_by' => NULL,
                'locked_at' => NULL
            ]);
            Log::info("Bimedia Response info => ",$ret_data);
            $cacheKey = md5(vsprintf("%s", [
                "bimedia-Balance"
            ]));
            \Cache::forget($cacheKey);
            getBimediaBalance:
            $dematSoap = new DematSoapBimediaController();
            $fetch_data = $dematSoap->FetchBalance();
            $BimediaBalance = $fetch_data->max_srd -  $fetch_data->conso_srd;
            if(isset($fetch_data->error)){
                $BimediaRemainBalance = '0.00';
            }else{
                if(empty($BimediaBalance) || is_numeric($BimediaBalance) == false){
                    AppHelper::logger('warning',$this->log_title,"Terminal may be resync happened, trigger goto procedure!");
                    //sleep for 3 seconds
                    sleep(3);
                    goto getBimediaBalance;
                }else{
                    $BimediaRemainBalance = AppHelper::formatAmount('EUR', number_format(($BimediaBalance /100), 2, '.', ''));
                }
            }
            $amount_detected =$previousBalance -$BimediaBalance;
            Bimedia_statistics::insert([
                'date' => $pin_printed_time,
                'card_name' => str_replace("â‚¬", "€", $card_info->name),
                'face_value' => $public_price,
                'amount_deducted' => $order_amount->sale_price,
                'bimedia_amount_deducted' => $amount_detected,
                'previous_balance'  =>$previousBalance,
                'new_balance'  => $BimediaBalance,
                'pin' => $dec_pin,
                'serial' => $dec_serial,
                'trxref' => $dec_trxref,
                'validity' => date('Y-m-d', strtotime($conv_date)),
                'used_by' => auth()->user()->id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $acknowledge = $bimedia->acknowledgement($dematSOAP);
            if(isset($acknowledge->error)){
                throw new \Exception($dematSOAP->error);
                Log::info("Ack Failed");
            }
            else
            {
                Log::info("Ack Passed");
            }
            Log::info("Bimedia new balance will be $BimediaRemainBalance");
            \DB::commit();
            //add it cache
            \Cache::put($cacheKey, $BimediaRemainBalance, 60);
            return ApiHelper::response('200',200,trans('myservice.print_success'),$ret_data);
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
            Log::emergency(auth()->user()->username." pin print exception => ".$e->getMessage(),[$e]);
            return ApiHelper::response('500',200,$this->unableToPrintMessage());
        }

    }
    function print_card_activated(Request $request)
    {
        $data = $request->all();
        $user_info = User::find(auth()->user()->id);
        //lets check the card actually locked by this user
        $card_info = CallingCardPin::where('id',$request->ccp_id)
            ->where('cc_id',$request->cc_id)
            ->where('is_used','0')
            ->where('is_locked',1)
            ->where('locked_by',auth()->user()->id)
            ->first();
        if(!$card_info){
            AppHelper::logger('warning',$this->log_title,'No such card was found!',$data);
            return ApiHelper::response('404',200,$this->unableToPrintMessage());
        }
        $public_price = $card_info->value;
        $check_limit = AppHelper::get_daily_limit(auth()->user()->id);
        if($check_limit !=NULL)
        {
            if (ServiceHelper::limit_check(auth()->user()->id, $public_price)) {
                $r_bal = (\app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id));
                $daily_limit = (\app\Library\AppHelper::get_daily_limit(auth()->user()->id));
                $getBalance = (\app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency, false));
                $blink_limit = str_replace('-', '', $r_bal);
                $manager_id =(auth()->user()->parent_id);
                if($manager_id != '')
                {
                    $result = \app\User::where('id', $manager_id)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email,'balaji@prepaysolution.in'];
                }
                else
                {
                    $result = \app\User::where('id', 1)->orderBy('id', 'DESC')->first();
                    $emails = [$result->email];
                }
                $send_email_data = array(
                    'retailer_name' => auth()->user()->username,
                    'manager_name' => $result->username,
                    'current_bal' => $getBalance,
                    'total_limit' => $daily_limit,
                    'current_limit' => $blink_limit,
                );
                \Mail::send('emails.daily_limit_alert', $send_email_data, function ($message) use ($emails) {
                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                    $message->to($emails)->subject('Tama Daily Limit Alert');
                });
                AppHelper::logger('warning', 'Daily Limit Exceed', auth()->user()->username . 'Daily limit exceed to confirm tama topup order', $data);
                Log::warning('TamaTopup Daily Limit Exceed => ' . auth()->user()->username . ' => ' . auth()->user()->id);
                return redirect('tama-topup')
                    ->with('message', trans('common.contact_manager'))
                    ->with('message_type', 'warning');
            }
        }
        //lets check with the paren have sufficient balance or credit limit for this order
        if (ServiceHelper::parent_rule_check(auth()->user()->parent_id, $public_price,7)) {
            //parent does not have enough money or credit limit
            //order will be failed
            AppHelper::logger('warning', 'Parent Rule Failed', auth()->user()->username . ' parent does not have enough balance or credit limit to confirm Calling Card order', $data);
            Log::warning('Calling card Parent Rule Failed => ' . auth()->user()->username . ' => ' . auth()->user()->parent_id);
            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
        }
        if(ServiceHelper::check_user_rate_table(auth()->user()->id,$card_info->cc_id)){
            AppHelper::logger('warning',$this->log_title,'Rate Table is not set for this user',$data);
            return ApiHelper::response('503',200,$this->unableToPrintMessage());
        }
        $order_amount = ServiceHelper::get_user_rate_table(auth()->user()->id,$card_info->cc_id);
        $user_balance = AppHelper::getBalance(auth()->user()->id,'EUR',false);
        $user_credit_limit = AppHelper::get_credit_limit(auth()->user()->id);
        if(isset($order_amount->sale_price)){
            if ($user_balance < $order_amount->sale_price) {
                //check with credit limit
                if (ServiceHelper::check_with_credit_limit($order_amount->sale_price, $user_balance, $user_credit_limit) == false) {
                    AppHelper::logger('warning',$this->log_title,auth()->user()->username . ' does not have enough balance or credit limit to confirm Calling Card order', $data);
                    return ApiHelper::response('503',200,trans('myservice.err_no_balance'));
                }
            }
        }else{
            AppHelper::logger('warning', 'Rate Table Sale Price Error', auth()->user()->username . ' rate table sale price may be 0', $data);
            return ApiHelper::response('400',200,trans('common.service_not_avail'));
        }
        $pin_printed_time = date('Y-m-d H:i:s');
        $root_txn_id = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
        $after_order_balance = number_format((float)$user_balance - $order_amount->sale_price, 2, '.', '');
        try{
            \DB::beginTransaction();
            //update pin status
            CallingCardPin::where('id',$data['ccp_id'])->update([
                'is_used' => 1,
                'used_by' => auth()->user()->id,
                'is_locked' => 0,
                'locked_by' => null,
                'updated_at' => $pin_printed_time,
                'updated_by' => auth()->user()->id
            ]);
            //decrypt the pin
            $secret_key = SecurityHelper::decipherEncryption($card_info->public_key . "CJJbW7SaznW7cZhVzwLo");
            $dec_pin = SecurityHelper::tamaCipher($card_info->pin, "d", $secret_key);
            //order comment
            $order_comment = "Retailer " . auth()->user()->username . " used card " . $card_info->name . " " . $card_info->value;
            //user order and transaction
            $trans_id = ServiceHelper::sync_transaction(auth()->user()->id, $pin_printed_time,'debit', $order_amount->sale_price, $user_balance, $after_order_balance, $order_comment);
            $order_id = Order::insertGetId([
                'date' => $pin_printed_time,
                'user_id' => auth()->user()->id,
                'service_id' => '7',
                'order_status_id' => '7',
                'txn_ref' => $root_txn_id,
                'comment' => $order_comment,
                'currency' => "EUR",
                'public_price' => $public_price,
                'buying_price' => $order_amount->buying_price,
                'order_amount' => $order_amount->sale_price,
                'sale_margin' => $public_price - $order_amount->sale_price,
                'grand_total' => $order_amount->sale_price,
                'transaction_id' => $trans_id,
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            $order_item_id = OrderItem::insertGetId([
                'order_id' => $order_id,
                'tt_operator' => $card_info->name,
                'app_currency' => "EUR",
                'created_at' => $pin_printed_time,
                'created_by' => auth()->user()->id
            ]);
            //update the order item id to order
            Order::where('id',$order_id)->update([
                'order_item_id' => $order_item_id
            ]);
            $parent_user = User::find(auth()->user()->parent_id);
            $calling_card = CallingCard::find($data['cc_id']);
            $operator_type = SeriveProvider::select('primary')->first();

            $parent_buying_price = $calling_card->buying_price;

            if(!empty(auth()->user()->parent_id) && $parent_user && $parent_user->group_id != 2){
                $parent_order_amount = ServiceHelper::get_user_rate_table($parent_user->id,$card_info->cc_id);
                $parent_user_balance = AppHelper::getBalance($parent_user->id,'EUR',false);
                $parent_credit_limit = AppHelper::get_credit_limit($parent_user->id);
                if(isset($parent_order_amount->sale_price)){
                    if ($parent_user_balance < $parent_order_amount->sale_price) {
                        //check with credit limit
                        if (ServiceHelper::check_with_credit_limit($parent_order_amount->sale_price, $parent_user_balance, $parent_credit_limit) == false) {
                            Log::warning($parent_user->username . ' does not have enough balance or credit limit to confirm client Calling Card order',[$data]);
                            return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                        }
                    }
                }else{
                    Log::warning('Rate Table Sale Price Error '. auth()->user()->username . ' rate table sale price may be 0', [$data]);
                    return ApiHelper::response('400',200,trans('myservice.contact_admin'));
                }
                $parent_balance_after_order = number_format((float)$parent_user_balance - $parent_order_amount->sale_price, 2, '.', '');
                //parent user order and transaction
                $parent_trans_id = ServiceHelper::sync_transaction($parent_user->id, $pin_printed_time,'debit', $parent_order_amount->sale_price, $parent_user_balance, $parent_balance_after_order, $order_comment);
                //by retailer to manager
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_order_amount->sale_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_order_amount->sale_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'transaction_id' => $parent_trans_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
                //by manager to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => $parent_user->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $parent_order_amount->sale_price,
                    'sale_margin' => $parent_order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $parent_order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            else{
                //by user to dematpro
                Order::insertGetId([
                    'date' => $pin_printed_time,
                    'user_id' => auth()->user()->id,
                    'service_id' => '7',
                    'order_status_id' => '7',
                    'txn_ref' => $root_txn_id,
                    'comment' => $order_comment,
                    'currency' => "EUR",
                    'public_price' => $public_price,
                    'buying_price' => $parent_buying_price,
                    'order_amount' => $order_amount->sale_price,
                    'sale_margin' => $order_amount->sale_price - $parent_buying_price,
                    'grand_total' => $order_amount->sale_price,
                    'is_parent_order' => 1,
                    'order_item_id' => $order_item_id,
                    'created_at' => $pin_printed_time,
                    'created_by' => auth()->user()->id
                ]);
            }
            //finally deduct balance from myservice balance
            $master_retailer = User::where('group_id',2)->select('id','username','currency')->orderBy('id','ASC')->first();
            $oldCCServiceBalance = $this->callingCardServiceBalance($master_retailer);
            $newCCBalance = number_format((float)$oldCCServiceBalance - (float)$calling_card->buying_price, 2, '.', '');
            Log::info('New myservice balance '.$newCCBalance);
            ServiceHelper::sync_myservice_transaction($master_retailer->id, $data['cc_id'], $pin_printed_time, 'debit', $calling_card->buying_price, $oldCCServiceBalance, $newCCBalance, $order_comment);
            PinHistory::insert([
                'cc_id' => $data['cc_id'],
                'date' => $pin_printed_time,
                'name' => str_replace("â‚¬", "€", $card_info->name),
                'pin' => $dec_pin,
                'serial' => $card_info->serial,
                'is_aleda' => 0,
                'used_by' => auth()->user()->id
            ]);
            \DB::commit();
            AppHelper::logger('success',$this->log_title,auth()->user()->username." pin id ".$data['ccp_id']." was success",$data);
            $ret_data = [
                'pin' => $dec_pin,
                'serial' => $card_info->serial,
                'time_printed' => $pin_printed_time,
                'remain_balance' => AppHelper::getBalance(auth()->user()->id, 'EUR')
            ];
            return ApiHelper::response('200',200,trans('myservice.print_success'),$ret_data);
        }catch (\Exception $e){
            \DB::rollBack();
            AppHelper::logger('warning',$this->log_title,"Exception ".$e->getMessage());
            Log::emergency(auth()->user()->username." pin print exception => ".$e->getMessage());
            return ApiHelper::response('500',200,$this->unableToPrintMessage());
        }
    }
}
