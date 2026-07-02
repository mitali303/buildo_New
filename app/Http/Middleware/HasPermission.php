<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        return $next($request);
    }
}