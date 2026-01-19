<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Check if user is suspended/banned - log them out immediately
        if ($user->isSuspended()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'حسابك محظور/معطل. يرجى التواصل مع الإدارة.'.($user->suspended_reason ? " - السبب: {$user->suspended_reason}" : ''),
            ]);
        }

        // Check if user can login (status is active)
        if (! $user->canLogin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'حسابك معطل. يرجى التواصل مع الإدارة.',
            ]);
        }

        if (! $user->isSuperAdmin() && ! $user->isAdmin() && ! $user->isEnergyAuthority() && ! $user->isCompanyOwner() && ! $user->isEmployee() && ! $user->isTechnician()) {
            abort(403);
        }

        return $next($request);
    }
}
