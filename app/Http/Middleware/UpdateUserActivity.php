<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // تحديث last_activity للمستخدم المصادق عليه
        if (Auth::check()) {
            $user = Auth::user();
            
            // تحديث last_activity فقط إذا مرت دقيقة واحدة على الأقل (لتقليل عدد الاستعلامات)
            if (!$user->last_activity || $user->last_activity->diffInMinutes(now()) >= 1) {
                $user->update(['last_activity' => now()]);
            }
        }

        return $response;
    }
}
