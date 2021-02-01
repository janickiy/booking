<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;

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
    protected $redirectTo = '/';

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
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [

            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function create(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|min:6',
                'confirm_password' => 'required|min:6|same:password',
                'firstname' => 'required',
                'lastname' => 'required',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        return User::create([
            'login' => $request->email,
            'email' => $request->email,
            'userTypeId' => 0,
            'mobile' => isset($request->mobile) ? $request->mobile : '',
            'password' => bcrypt($request->password),
            'contacts' => [
                'contactEmails' => $request->email,
                'contactPhone' => isset($request->mobile) ? $request->mobile : '',
                'firstName' => $request->firstname,
                'lastName' => $request->lastname,
                'middleName' => isset($request->middlename) ? $request->middlename : '',
            ],
            'isVerifiedMobile' => false,
        ]);
    }
}
