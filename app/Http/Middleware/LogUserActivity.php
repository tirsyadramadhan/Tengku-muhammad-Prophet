<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ActivityLogger;

class LogUserActivity
{
    use ActivityLogger;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
