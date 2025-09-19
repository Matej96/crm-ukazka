<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helpers;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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

    use AuthenticatesUsers {
        logout as traitLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest', 'lang'])->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        session()->put('system_client_id', $user->system_client_id);
        if($locale = ($user->last_locale ?? session()->get('lang'))) {
            app(HomeApiController::class)->setLocale(new Request(['lang' => $locale]));
        }

        if($broker = ($user->active_broker_id ?? null)) {
            app(\App\Plugins\User\app\Http\Controllers\Api\BrokerApiController::class)->setActive($broker);
            session()->put('logged_active_broker_id', session()->get('active_broker_id'));  //Aby vyskocil popup po prihlaseni!
        }

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('Login');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $locale = $request->session()->get('lang');
        $theme = $request->session()->get('theme');

        $return = self::traitLogout($request);

        if ($locale) {
            app(HomeApiController::class)->setLocale(new Request(['lang' => $locale]));
        }

        if ($theme) {
            app(HomeApiController::class)->setTheme(new Request(['theme' => $theme]));
        }

        return $return;
    }
}
