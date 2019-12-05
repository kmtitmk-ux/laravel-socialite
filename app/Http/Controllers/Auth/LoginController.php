<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use Socialite;

class LoginController extends Controller
{
    private $user;
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
    protected $redirectTo = '/home';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = \Auth::user();
            if (empty($this->user->id)) {
                $this->user = new \stdClass;
                $this->user->id = false;
            }
            return $next($request);
        });
        //$this->middleware('guest')->except('logout');
    }


    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }


    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try {
            $data = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login');
        }

        //追加か更新
        if ($this->user->id) {
            $u = User::where('id', $this->user->id)->update(
                [$provider.'_id' => $data->getId()]
            );
        } else {
            $u = User::firstOrCreate([$provider.'_id' => $data->getId()], [
                $provider.'_id' => $data->getId(),
                'name' => $data->getName()
            ]);
            \Auth::login($u);
        }

        return redirect($this->redirectTo);
    }
}
