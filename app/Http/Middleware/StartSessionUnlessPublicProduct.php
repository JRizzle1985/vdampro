<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

class StartSessionUnlessPublicProduct extends StartSession
{
    private bool $suppressCookie = false;

    public function handle($request, Closure $next)
    {
        $this->suppressCookie = $request instanceof Request
            && $request->getHost() === config('app.public_product_host');

        return parent::handle($request, $next);
    }

    protected function addCookieToResponse(Response $response, Session $session)
    {
        if ($this->suppressCookie) {
            return;
        }

        parent::addCookieToResponse($response, $session);
    }
}
