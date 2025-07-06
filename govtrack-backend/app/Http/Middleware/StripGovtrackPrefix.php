<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StripGovtrackPrefix
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('govtrack/*')) {
            $request->server->set('REQUEST_URI', substr($request->getRequestUri(), 9)); 
        }

        return $next($request);
    }
}
