<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperatorApproved
{
    /**
     * Handle an incoming request.
     * يمنع الوصول لبعض الصفحات إذا كان المشغل غير معتمد
     * يسمح بالوصول لصفحات: profile, generation-units, generators (لإضافتها)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // SuperAdmin و Admin و EnergyAuthority دائماً مسموح لهم
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return $next($request);
        }

        // الصفحات المسموح بها حتى بدون اعتماد أو حتى لو كان المشغل معطل
        // المشغل غير المعتمد يمكنه فقط:
        // - إكمال ملفه الشخصي
        // - إضافة وحدات التوليد والمولدات
        // - عرض الداشبورد (محدود)
        // - الرسائل والإشعارات
        $allowedRoutes = [
            'admin.operators.profile',
            'admin.operators.profile.update',
            'admin.operators.data', // API route for getting operator data (needed for generation units form)
            'admin.operators.generation-units', // API route for getting generation units (needed for generators form)
            'admin.operators.generation-units-list', // API route for getting generation units list (needed for filters)
            'admin.generation-units.index',
            'admin.generation-units.create',
            'admin.generation-units.store',
            'admin.generation-units.show',
            'admin.generation-units.edit',
            'admin.generation-units.update',
            'admin.generation-units.qr-code',
            'admin.generation-units.generators-list', // API route for getting generators list (needed for filters)
            'admin.territories.all', // API route for getting all territories (needed for map display)
            'admin.territories.check', // API route for checking territory availability (needed for location validation)
            'admin.generators.index',
            'admin.generators.create',
            'admin.generators.store',
            'admin.generators.show',
            'admin.generators.edit',
            'admin.generators.update',
            'admin.dashboard',
            // Constants routes - needed for generation units and generators forms
            'admin.constant-details.cities-by-governorate',
            // Notifications routes - should be accessible even if operator is not approved
            'admin.notifications.index',
            'admin.notifications.read',
            'admin.notifications.read-all',
            'admin.notifications.destroy',
            // Messages routes - should be accessible even if operator is not approved
            'admin.messages.unread-count',
            'admin.messages.recent',
            'admin.messages.index',
            'admin.messages.show',
            'admin.messages.create',
            'admin.messages.store',
            'admin.messages.edit',
            'admin.messages.update',
            'admin.messages.destroy',
            'admin.messages.mark-read',
            // Profile routes
            'admin.profile.show',
            'admin.profile.change-password',
            // ملاحظة: Permissions routes تم إزالتها - يجب أن تكون فقط للمشغل المعتمد
        ];

        $routeName = $request->route()?->getName();
        
        // التحقق من الـ route name بشكل أكثر مرونة (للتعامل مع variations محتملة)
        if (!$routeName && $request->route()) {
            // محاولة الحصول على الـ route name بطريقة بديلة
            $routeName = $request->route()->getName();
        }

        // إذا كانت الصفحة مسموح بها، اتركه يمر (حتى لو كان المشغل معطل)
        if ($routeName && in_array($routeName, $allowedRoutes)) {
            return $next($request);
        }
        
        // للـ AJAX requests على routes معينة، السماح بالوصول حتى لو لم يكن الـ route name في القائمة
        // هذا للتأكد من أن routes مثل cities-by-governorate تعمل حتى لو كان هناك مشكلة في الـ route name
        if ($request->expectsJson() && $request->is('admin/constant-details/cities-by-governorate')) {
            return $next($request);
        }

        // التحقق من أن المشغل مفعل (للبقية الصفحات)
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && $operator->status === 'inactive') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.',
                    ], 403);
                }
                return redirect()->route('admin.operators.profile')
                    ->with('error', 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.');
            }
        }

        if ($user->isEmployee() || $user->isTechnician()) {
            $hasActiveOperator = $user->operators()
                ->where('status', 'active')
                ->exists();
            
            if (!$hasActiveOperator) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.',
                    ], 403);
                }
                return redirect()->route('admin.dashboard')
                    ->with('error', 'المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.');
            }
        }

        // إذا كان المشغل معتمد ومفعل، اتركه يمر
        if ($user->hasApprovedOperator()) {
            return $next($request);
        }

        // للمشغل: التحقق من حالة الاعتماد والتفعيل بشكل منفصل لإعطاء رسالة واضحة
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                if (!$operator->is_approved) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.',
                        ], 403);
                    }
                    return redirect()->route('admin.operators.profile')
                        ->with('warning', 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.');
                }
                
                if ($operator->status !== 'active') {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.',
                        ], 403);
                    }
                    return redirect()->route('admin.operators.profile')
                        ->with('error', 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.');
                }
            }
        }

        // إذا كان يحاول الوصول لصفحة محظورة، أرسله لصفحة الملف الشخصي مع رسالة
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.',
            ], 403);
        }

        return redirect()->route('admin.operators.profile')
            ->with('warning', 'حسابك في انتظار الاعتماد من سلطة الطاقة. يمكنك إضافة وحدات التوليد والمولدات فقط حتى يتم اعتماد حسابك.');
    }
}

