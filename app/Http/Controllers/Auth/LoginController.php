<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use App\User;
use Auth;


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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function handleProviderCallback(){
        try{
            $user = Socialite::driver('facebook')->user();
        }catch (\Exception $e){
            return redirect('auth/facebook');
        }
        $auth = $this->findOrCreate($user);
        Auth::login($auth,true);
        return redirect()->route('home');
    }

    public function redirectToProvider(){
        return Socialite::driver('facebook')->redirect();
    }

    public function findOrCreate($facebookUser){
        $authUser = User::where('facebook_id',$facebookUser->id)->first();

        //dd($authUser);
        if($authUser != null){
            return $authUser;
        }
        else{
            //dd($facebookUser->getId());
            return User::create([
                'name'=>$facebookUser->name,
                'email'=>$facebookUser->email,
                'facebook_id'=>$facebookUser->id,
                'confirmed'=>1
            ]);
        }
    }
}
