<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
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
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);
    }

    /**
     * @param Request $request
     * @return Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws ApiException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function signIn(Request $request)
    {
        $this->validateLogin($request);

        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        // attempt to verify the credentials and create a token for the user
        if (! $token = $this->auth->attempt($credentials)) {
            throw new ApiException('Invalid credentials', 422, 'unauthenticated');
        }

        return response($this->auth->user())->header('Authorization', $token);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function refresh()
    {
        try {
            $token = $this->auth->parseToken()->refresh();
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }

        return response([], 204)->header('Authorization', $token);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function revoke(Request $request)
    {
        try {
            \Auth::logout();
        } catch (JWTException $e) {
            //
        }

        return response([], 204);
    }

}
