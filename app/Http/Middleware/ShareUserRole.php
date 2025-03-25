<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ShareUserRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            View::share('userRole', Auth::user()->role()->first());
        }
        
        return $next($request);
    }
}
