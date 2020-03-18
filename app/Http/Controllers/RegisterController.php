<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Tymon\JWTAuth\JWTAuth;

class RegisterController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $auth;

    /**
     * LoginController constructor.
     *
     * @param \Tymon\JWTAuth\JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRegister(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:8',
            'name' => 'required|max:255',
        ]);
    }

    /**
     * @param array $data
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        /** @var User $user */
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = app('hash')->make($data['password']);
        $user->status = User::STATUS_PENDING;
        $user->save();

        return $user;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function signUp(Request $request)
    {
        $this->validateRegister($request);

        $user = $this->create($request->all());

        $token = \Auth::login($user);

        return response($user, 201)->header('Authorization', $token);
    }

}
