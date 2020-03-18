<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Tymon\JWTAuth\JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function handle($request, Closure $next)
    {
        $this->auth->parser()->setRequest($request);

        try {
            $this->auth->parseToken();
            $this->auth->checkOrFail();

            if (! $this->auth->authenticate()) {
                $this->throwUnauthenticated();
            }

        } catch (TokenExpiredException $e) {
            $this->throwUnauthenticated($e->getMessage(), 'token_expired');
        } catch (JWTException $e) {
            $this->throwUnauthenticated($e->getMessage(), 'token_invalid');
        }

        return $next($request);
    }

    /**
     * @param string $message
     * @param string $reason
     *
     * @throws \App\Exceptions\ApiException
     */
    private function throwUnauthenticated(string $message = 'Unauthenticated', string $reason = 'unauthenticated')
    {
        throw new ApiException($message, 401, $reason);
    }
}
