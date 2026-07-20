<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use DB;
use Illuminate\Support\Facades\Auth as AuthConfig;
use Illuminate\Support\Facades\Log;
use app\Library\AppHelper;
use Nexmo\Laravel\Facade\Nexmo;
use PragmaRX\Google2FA\Google2FA;
use Response;
use Validator;

class TwoStepAuthenticationController extends Controller
{
    private $app_name;
    function __construct()
    {
        parent::__construct();
        $this->app_name =config('app.name');
    }
    protected function guard()
    {
        return AuthConfig::guard();
    }
    function check()
    {
        if (auth()->check()) return redirect('dashboard');
        $page_data = array(
            'page_title' => APP_NAME." Login"
        );
        return view('auth.login', $page_data);
    }
    function generate_otp(Request $request)
    {
//        dd($request->all());
        $rules = [
            'mobile' => "required|min:9|max:15"
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            AppHelper::logger('warning', 'mobile required', 'User Does not Enter Mobile Number', $request->all());
            $html = AppHelper::create_error_bag($validator);
            return redirect()->back()
                ->with('message', $html)
                ->with('message_type', 'warning');
        }
        $mobileNumber = str_replace("+", "", $request->input('mobile'));
        $user = User::where('username', $request->username)->first();
        $otp = rand(1000, 9999);
        User::where('id', $user->id)->update([
            'otp' => $otp,
            'mobile' =>$mobileNumber,
            'email' => $request->email,
            'ip_address2'=>$user->ip_address,
        ]);
//        Nexmo::message()->send([
//            'to'   => $request->mobile,
//            'from' => '917904721979',
//            'text' => 'Hello ' .$user->username.' Greetings From '.$this->app_name.' Your Verification Code is ' . $otp
//        ]);
        AppHelper::sendSms($request->mobile, 'Hello ' .$user->username.' Greetings From '.APP_NAME.' Your Verification Code is ' . $otp);
        $emails = [$user->email];
//        $send_email_data = array(
//            'retailer_name' => $user->username,
//            'otp' => $otp,
//        );
//        if(!empty($user->email)){
//            \Mail::send('emails.otp', $send_email_data, function ($message) use ($emails) {
//                $message->from('noreply@tamaexpress.com', 'Tama Retailer');
//                $message->to($emails)->subject('Tama OTP');
//            });
//        }
        $page_data = [
            'username' => $request->username,
            'password' => $request->password,
            'lang' => $request->lang,
        ];
        return view('auth.otp_check', $page_data);
    }

    function resend_otp(Request $request)
    {
        $user = User::where('username', $request->username)->first();
        $otp = rand(1000, 9999);
        User::where('id', $user->id)->update([
            'otp' => $otp,
        ]);

//        Nexmo::message()->send([
//            'to'   => $user->mobile,
//            'from' => '917904721979',
//            'text' => 'Hello ' .$user->username.' Greetings From '.$this->app_name.' Your Verification Code is ' . $otp
//        ]);
        AppHelper::sendSms($user->mobile, 'Hello ' .$user->username.' Greetings From '.APP_NAME.' Your Verification Code is ' . $otp);
        $emails = [$user->email];
//        $send_email_data = array(
//            'retailer_name' => $user->username,
//            'otp' => $otp,
//        );
//        if(!empty($user->email)){
//            \Mail::send('emails.otp', $send_email_data, function ($message) use ($emails) {
//                $message->from('noreply@tamaexpress.com', 'Tama Retailer');
//                $message->to($emails)->subject('Tama OTP');
//            });
//        }

        return Response::json(array(
            'success' => 0,
        ));
    }
    function failed_otp(Request $request)
    {
        if (config('app.env') == 'local') {
            $client_ip = \Request::getClientIp(true);
        }
        else
        {
            $client_ip = AppHelper::getIP(true);
        }
        if (\Auth::attempt(['username' => $request->username, 'password' => $request->password, 'otp' => $request->otp, 'status' => 1])) {
            User::where('id', auth()->user()->id)->update([
                'ip_address' => $client_ip,
                'verify_ip' => '1',
                'otp' => '',
                'last_session_id' => session()->getId(),
            ]);
            return Response::json(array(
                'success' => 0,
            ));
        }
        else{
            return Response::json(array(
                'success' => 1,
            ));
        }
    }
    function index(Request $request)
    {
//        dd($request->all());
        if (config('app.env') == 'local') {
            $client_ip = \Request::getClientIp(true);
        }
        else
        {
            $client_ip = AppHelper::getIP(true);
        }

        $page_data = [
            'username' => $request->username,
            'password' => $request->password,
            'ip_address' => $client_ip,
            'lang' => $request->lang,
            'select_flag ' => 'france',
        ];
        $user = User::where('username', $request->username)->first();
        if ($user) {
            $session_data = \Session::all();
            if (\Hash::check($request->password, $user->password)) {
                User::where('id', $user->id)->update([
                    'last_session_id' =>  $session_data['_token'],
                ]);

                if (in_array($user->group_id, [4])) {
                    if($user->enable_ip == 1)
                    {
                        $remember = (!$request->input('remember') == '') ? true : false;
                        if (\Auth::attempt(['username' => $request->username, 'password' => $request->password, 'status' => 1], $remember)) {

//                                \Session::put('locale', $request->lang);
//                                \App::setLocale($request->lang);

                            Log::info('user ' . $request->username . ' logged in');
                            AppHelper::logger('info', 'Login', 'User ' . $request->username . ' logged in');
                            return redirect()->back()->with('message', trans('users.lbl_welcome') . ' ' . $request->username)->with('msg_type', 'info');
                        }
                    }
                    else{
                        if ($user->ip_address == NULL) {
                            return view('auth.ip_address', $page_data);
                        }
                        if ($user->ip_address != $client_ip) {
                            $otp = rand(1000, 9999);
//                            Nexmo::message()->send([
//                                'to'   => $user->mobile,
//                                'from' => '917904721979',
//                                'text' => 'Hello ' .$user->username.' Greetings From '.$this->app_name.' Your Verification Code is ' . $otp
//                            ])
                            AppHelper::sendSms($user->mobile, 'Hello ' .$user->username.' Greetings From '.APP_NAME.' Your Verification Code is ' . $otp);
                            $emails = [$user->email];
//                            $send_email_data = array(
//                                'retailer_name' => $user->username,
//                                'otp' => $otp,
//                            );
//                            if(!empty($user->email)){
//                                \Mail::send('emails.otp', $send_email_data, function ($message) use ($emails) {
//                                    $message->from('noreply@tamaexpress.com', 'Tama Retailer');
//                                    $message->to($emails)->subject('Tama OTP');
//                                });
//                            }
                            User::where('id', $user->id)->update([
                                'otp' => $otp,
                                'ip_address2' =>$user->ip_address
                            ]);
                            return view('auth.otp_check', $page_data)->with('message', trans('common.msg_update_success'));
                        }

                        else{
                            $remember = (!$request->input('remember') == '') ? true : false;
                            if (\Auth::attempt(['username' => $request->username, 'password' => $request->password, 'status' => 1], $remember)) {
//                                    \Session::put('locale', $request->lang);
//                                    \App::setLocale($request->lang);
                                Log::info('user ' . $request->username . ' logged in');
                                AppHelper::logger('info', 'Login', 'User ' . $request->username . ' logged in');
                                return redirect()->back()->with('message', trans('users.lbl_welcome') . ' ' . $request->username)->with('msg_type', 'info');
                            }
                        }
                    }
                }
                else
                {
                    $remember = (!$request->input('remember') == '') ? true : false;
                    if (\Auth::attempt(['username' => $request->username, 'password' => $request->password, 'status' => 1], $remember)) {
//                            \Session::put('locale', $request->lang);
//                            \App::setLocale($request->lang);
                        Log::info('user ' . $request->username . ' logged in');
                        AppHelper::logger('info', 'Login', 'User ' . $request->username . ' logged in');
                        return redirect()->back()->with('message', trans('users.lbl_welcome') . ' ' . $request->username)->with('msg_type', 'info');
                    }
                }
            } else {
                Log::info('login failed, invalid credentials!', [$request->all()]);
                return redirect()
                    ->back()
                    ->withErrors(['username' => trans('auth.failed')])
                    ->withInput()
                    ->with('message', trans('auth.failed'))
                    ->with('msg_type', 'warning');
            }
        } else {
            Log::info('login failed, invalid credentials!', [$request->all()]);
            return redirect()
                ->back()
                ->withErrors(['username' => trans('auth.failed')])
                ->withInput()
                ->with('message', trans('auth.failed'))
                ->with('msg_type', 'warning');
        }
    }

    public function secure_login_validate(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $credentials['status'] = 1; // Only allow active users
        $remember = $request->filled('remember');

        $client_ip = config('app.env') === 'local'
            ? \Request::getClientIp(true)
            : AppHelper::getIP(true);

        $lang = in_array($request->lang, ['en', 'fr', 'de']) ? $request->lang : 'fr';
        \Session::put('locale', $lang);
        \App::setLocale($lang);

        $validator = Validator::make($credentials, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Log::warning('Login validation failed', ['username' => $credentials['username']]);
            return redirect()->back()->withInput()->withErrors($validator);
        }

        if (AuthConfig::once($credentials)) {
            $user = AuthConfig::user();
            if ($user->group_id == 4) {
                $parent = User::find($user->parent_id);
                if (!$parent || $parent->status != 1) {
                    Log::warning('Retailer login blocked due to inactive parent', [
                        'retailer_id' => $user->id,
                        'parent_id' => $user->parent_id
                    ]);
                    return redirect()->back()->withErrors([
                        'username' => 'Login denied. Parent account is inactive.'
                    ]);
                }
            }
            if ($user->method == '1') {
                // Check IP mismatch
                if ($user->ip_address !== $client_ip) {
                    $otp = random_int(1000, 9999); // Secure OTP
//                    $otp = '1234';
                    if (config('app.env') !== 'local') {
                        // Send OTP via SMS
                        AppHelper::sendSms(
                            $user->mobile,
                            'Hello ' . $user->username . ', Greetings from ' . APP_NAME . '. Your verification code is ' . $otp
                        );
                    }
                    // Send OTP via Email
                    if (!empty($user->email)) {
                        $emailData = [
                            'retailer_name' => $user->username,
                            'otp' => $otp,
                        ];
                        try {
                            \Mail::send('emails.otp', $emailData, function ($message) use ($user) {
                                $message->from('noreply@tamaexpress.com', 'Tama Retailer');
                                $message->to($user->email)->subject('Tama OTP');
                            });
                            \Log::info('OTP email sent to ' . $user->email);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send OTP email to ' . $user->email . ': ' . $e->getMessage());
                        }
                    }

                    // Store OTP and log IP
                    User::where('id', $user->id)->update([
                        'otp' => $otp,
                        'ip_address2' => $user->ip_address,
                        'verify_ip' => 1,
                    ]);

                    $page_data = [
                        'username' => $request->username,
                        'password' => $request->password,
                        'ip_address' => $client_ip,
                        'lang' => $lang,
                        'select_flag' => 'france',
                    ];

                    return view('auth.otp_check', $page_data)->with('message', trans('common.msg_update_success'));
                }

                // No IP mismatch – normal login
                $this->guard()->login($user, $remember);

                session()->regenerate(); // important to avoid session fixation attacks
                User::where('id', $user->id)->update([
                    'last_activity' => now(),
                    'ip_address' => $client_ip,
                    'verify_ip' => 1,
                    'last_session_id' => session()->getId(),
                ]);
                Log::info('User ' . $user->username . ' logged in (IP verified)');
                return redirect('/dashboard');
            }

            // method == 2 → 2FA check
            if ($user->method == '2') {
                if ($user->enable_2fa && $user->verify_2fa) {
                    $request->session()->put('login_data', $request->only('username', 'lang'));
                    return redirect('/validate_otp');
                }

                // Normal login
                $this->guard()->login($user, $remember);
                session()->regenerate(); // important to avoid session fixation attacks
                User::where('id', $user->id)->update([
                    'last_activity' => now(),
                    'ip_address' => $client_ip,
                    'verify_ip' => 1,
                    'last_session_id' => session()->getId(),
                ]);
                Log::info('User ' . $user->username . ' logged in');
                return redirect('/dashboard');
            }

            // Fallback for other method values
            $this->guard()->login($user, $remember);
            User::where('id', $user->id)->update([
                'last_activity' => now(),
                'ip_address' => $client_ip,
                'last_session_id' => session()->getId(),
            ]);
            Log::info('User ' . $user->username . ' logged in');
			AppHelper::logger('info', 'Login', 'User ' . $request->username . ' logged in from ' . $client_ip);
            return redirect('/dashboard');
        }

        return redirect()
            ->back()
            ->withErrors(['username' => 'Login failed, invalid credentials!']);
    }


    public function totp_view()
    {
        if (auth()->check()) return redirect('dashboard');
        $page_data = array(
            'page_title' => APP_NAME." Login"
        );
        return view('auth.verifytotp', $page_data);
    }

    public function verify(Request $request)
    {
        $rules = [
            'totp' => 'required|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed'], 422);
        }

        if (($username = session('login_data.username')) && ($password = session('login_data.password'))) {
            $credentials = compact('username', 'password');
            $remember = !empty($username);

            if (AuthConfig::attempt($credentials + ['status' => 1], $remember)) {
                $user = AuthConfig::user();

                if ($user->enable_2fa == 1) {
                    $google2fa = app(Google2FA::class);

                    $inputSecret = $request->totp;
                    $secret = $user->secret;
                    // Verify the input secret with a wider time window
                    $valid = $google2fa->verifyKey($secret, $inputSecret, 2);
                    Log::info('OTP verification result: ' . ($valid ? 'valid' : 'invalid'));


                    if ($valid) {
                        $session_data = \Session::all();
                        $client_ip = config('app.env') == 'local' ? \Request::getClientIp(true) : AppHelper::getIP(true);
                        User::where('id', $user->id)->update([
                            'verify_2fa' => 1,
                            'last_activity' => now(),
                            'ip_address' => $client_ip,
                            'verify_ip' => '1',
                            'last_session_id' =>  $session_data['_token'],
                        ]);

                        $request->session()->forget('login_data');

                        $collection = collect(config('translation'));
//                        if ($collection->contains('folder', session('login_data.lang'))) {
//                            session()->put('locale', session('login_data.lang'));
//                            App::setLocale(session('login_data.lang'));
//                        }

                        Log::info('User ' . $user->username . ' logged in');
                        AppHelper::logger('info', 'Login', 'User ' . $user->username . ' logged in');
                        return redirect('/dashboard');
                    } else {
                        AuthConfig::logout();
                        return redirect()
                            ->back()
                            ->withErrors(['username' =>'Login failed, Invalid 2FA code!']);
                    }
                } else {

                    return redirect()
                        ->back()
                        ->withErrors(['username' =>'Login failed, 2FA not enabled!']);
                }
            }
        }

        return redirect()
            ->back()
            ->withErrors(['username' =>'Login failed, invalid credentials!']);
    }

}
