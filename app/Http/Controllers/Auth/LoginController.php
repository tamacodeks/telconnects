<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use app\Library\AppHelper;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        parent::__construct();
    }


    function index()
    {
        if (auth()->check()) return redirect('dashboard');
        $page_data = array(
            'page_title' => APP_NAME." Login"
        );
        return view('auth.login', $page_data);
    }

    function login(Request $request)
    {
        if (config('app.env') == 'local') {
            $client_ip = \Request::getClientIp(true);
        }
        else
        {
            $client_ip = AppHelper::getIP(true);
        }
        $messages = array(
            'username.required' => trans('users.error_username'),
            'password.required' => trans('users.error_password'),
        );
        $rules = array(
            'username' => 'required',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            Log::warning('validation failed', [$request->all()]);
            return redirect('/')->withInput()->withErrors($validator);
        }
        $remember = (!$request->input('remember') == '') ? true : false;
        if (\Auth::attempt(['username' => $request->username, 'password' => $request->password, 'otp' => $request->otp, 'status' => 1], $remember)) {

            \Session::put('locale', $request->lang);
            \App::setLocale($request->lang);
            User::where('id', auth()->user()->id)->update([
                'ip_address' => $client_ip,
                'verify_ip' => '1',
                'otp' => ''
            ]);
            Log::info('user ' . $request->username . ' logged in');
            AppHelper::logger('info', 'Login', 'User ' . $request->username . ' logged in');
            return redirect('/dashboard');
        }
        Log::info('login failed, invalid credentials!', [$request->all()]);
        return redirect()
            ->back()
            ->withErrors(['username' => trans('auth.failed')])
            ->withInput()
            ->with('message', trans('auth.failed'))
            ->with('msg_type', 'warning');
    }

    public function logout(Request $request)
    {
        $username = auth()->user()->username ?? 'unknown';

        // Log the logout action
        \Log::info('User ' . $username . ' logged out');
        AppHelper::logger('info', 'Logout', 'User ' . $username . ' logged out');

        // Preserve current locale before flushing session
        $lang = session('locale', config('app.locale'));

        \Auth::logout();
        \Session::flush();

        // Re-set the locale after flush
        \Session::put('locale', $lang);
        \App::setLocale($lang);

        return redirect('/')
            ->with('message', trans('users.logged_out'))
            ->with('message_type', 'success');
    }

}
