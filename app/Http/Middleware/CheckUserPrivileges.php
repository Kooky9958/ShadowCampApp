<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPrivileges
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user privileges are >= 10000
            if ($user->privileges >= 10000) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}

