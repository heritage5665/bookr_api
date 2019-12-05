<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HelloMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (preg_match('/balrogs$/i', $request->getRequesturi())) {
            return response('You Shall Not Pass!', 403);
        }
        return $next($request);
    }
}
