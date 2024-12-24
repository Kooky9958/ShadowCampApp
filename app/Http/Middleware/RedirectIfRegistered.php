<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfRegistered
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('post') && $request->routeIs('register') && $response->status() == 201) {
            return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
        }

        return $response;
    }
}
