<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\BrokerRegisterRequest;
use App\Plugins\System\app\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
//            'firstname' => ['required', 'string', 'max:255'],
//            'lastname' => ['required', 'string', 'max:255'],
//            'email' => ['required', 'string', 'email', 'max:255', 'unique:system_users'],
//            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
//            'firstname' => $data['firstname'],
//            'lastname' => $data['lastname'],
//            'email' => $data['email'],
//            'password' => Hash::make($data['password']),
//            'system_client_id' => 1,
        ]);
    }

    public function showRegistrationForm()
    {
        $formFields = app(HomeApiController::class)->createFormFields('system', 'users', true, ['broker_register' => true])->getOriginalContent();

        return view('auth.register', [
            'formFields' => $formFields,
        ]);
    }

    public function register(BrokerRegisterRequest $request)
    {
        $user = new User();
        $user->fill($request->except(['_token', 'system_client_id', 'id', 'password', 'password_confirmation', 'group_ids']));
        $user->password = '';
        $user->system_client_id = 1;

        $user->save();

        event(new Registered($user));

        $user->assignRole('user');

        $this->guard()->login($user);

//        return response()->json(['status' => 'Done']);
//
//        if ($response = $this->registered($request, $user)) {
//            return $response;
//        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect( route('app.profile', $user->id) );
    }
}
