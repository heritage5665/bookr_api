<?php

namespace App\Http\Middleware;

use log;
use Closure;
use Illuminate\Http\Request;

class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Reqeust Logged\n' .
            \sprintf("~~~~~\n%s~~~~", (string) $request));
        return $next($request);
    }
}
