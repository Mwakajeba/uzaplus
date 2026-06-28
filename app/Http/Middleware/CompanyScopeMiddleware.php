<?php

namespace App\Http\Middleware;

use App\Support\UserContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompanyScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            UserContext::applyToConfig(Auth::user());
        }

        return $next($request);
    }
}
