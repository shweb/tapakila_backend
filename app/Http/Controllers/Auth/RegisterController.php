<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Mail;

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
    protected $redirectTo = '/home';

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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    protected function register(Request $request){
        $input = $request->all();
        $validator = $this->validator($input);
        //dd($validator);
        //dd($validator->fails());
        if($validator->fails())
            return redirect(route('login'))->with('status',$validator->errors());
        else{
            $data = $this->create($input)->toArray();
            $data['token'] = str_random(25);
            $users = User::find($data['id']);
            $users->token = $data['token'];
            $users->save();

            Mail::send('mails.confirmation',$data,function($message) use($data){
                $message->to($data['email']);
                $message->subject('Tapakila Registration confirmation');
            });

            return redirect(route('login'))->with('status','Confirmation email has been sent. Please check your email');
        }

    }

    public function confirmation($token){
        $user = User::where('token',$token)->first();

        if($user != null){
            $user->confirmed = 1;
            $user->token = '';
            $user->save();
            return redirect(route('login'))->with('status','Your activation is completed');

        }
        return redirect(route('login'))->with('status','Something went wrong');
    }
}
