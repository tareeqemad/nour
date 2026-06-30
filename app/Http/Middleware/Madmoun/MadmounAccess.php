<?php

namespace App\Http\Middleware\Madmoun;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * بوابة الدخول لمنصة مضمون.
 *
 * هذه هي "نقطة الربط الوحيدة" لصلاحيات مضمون. مبرمج مضمون يطوّر المنطق هنا:
 *
 *   1) طبقة الاشتراك (Entitlement): هل المشغّل دافع/اشتراكه فعّال؟
 *        $operator = $user->getAffiliatedOperator();
 *        if (! $operator?->hasActiveMadmounSubscription()) abort(403, '...');
 *
 *   2) طبقة الدور (RBAC): يستخدم نظام نور الحالي كما هو، بصلاحيات بادئتها madmoun.*
 *        if (! $user->hasPermission('madmoun.something')) abort(403);
 *
 * فحوصات الحظر/التعطيل العامة يتكفّل بها AdminMiddleware المُطبّق قبله،
 * فلا داعي لتكرارها هنا.
 */
class MadmounAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // مالك المنصة يصل دائماً.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // --- بوابة مضمون (placeholder للتطوير) ---
        // مبدئياً نسمح بصلاحية صريحة madmoun.access أو للمشغّل وموظفيه،
        // لحين ربط طبقة الاشتراك. مبرمج مضمون يُحكم هذا الشرط.
        if ($user->hasPermission('madmoun.access') || $user->isAffiliatedWithOperator()) {
            return $next($request);
        }

        abort(403, 'لا تملك صلاحية الوصول إلى منصة مضمون.');
    }
}
