<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;

class Admin
{
    /**
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function handle($request, Closure $next)
    {
        if (! \Auth::user()->isAdmin()) {
            throw new ApiException('Access denied.', 403);
        }

        return $next($request);
    }

}
