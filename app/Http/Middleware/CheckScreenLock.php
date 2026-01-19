<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckScreenLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if screen is locked
        if (session('screen_locked') && !$request->is('admin/lock-screen*') && !$request->is('logout')) {
            return redirect()->route('admin.lock-screen.show');
        }
        
        // If screen is not locked but trying to access lock screen, redirect to dashboard
        if (!session('screen_locked') && $request->is('admin/lock-screen') && !$request->routeIs('admin.lock-screen.unlock')) {
            return redirect()->route('admin.dashboard');
        }
        
        return $next($request);
    }
}
