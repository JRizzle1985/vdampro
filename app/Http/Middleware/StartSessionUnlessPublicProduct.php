<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

class StartSessionUnlessPublicProduct extends StartSession
{
    public function handle($request, Closure $next)
    {
        if ($request instanceof Request && $request->getHost() === config('app.public_product_host')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
