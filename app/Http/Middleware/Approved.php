<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;

class Approved
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
        if (! \Auth::user()->isApproved()) {
            throw new ApiException('Access denied. Approval is required', 403);
        }

        return $next($request);
    }

}
