<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول للمستخدم
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials = $this->getCredentials($request);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        $user = Auth::user();

        // التحقق من حالة المستخدم
        if ($user->isSystemUser()) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        if ($user->isSuspended()) {
            Auth::logout();
            $reason = $user->suspended_reason ? " - السبب: {$user->suspended_reason}" : '';
            return response()->json([
                'success' => false,
                'message' => 'حسابك محظور/معطل. يرجى التواصل مع الإدارة.' . $reason,
            ], 403);
        }

        if (!$user->canLogin()) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'حسابك معطل. يرجى التواصل مع الإدارة.',
            ], 403);
        }

        // التحقق من حالة المشغل (إذا كان CompanyOwner)
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator && $operator->status === 'inactive') {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.',
                ], 403);
            }
        }

        // التحقق من حالة المشغل (إذا كان Employee أو Technician)
        if ($user->isEmployee() || $user->isTechnician()) {
            $hasActiveOperator = $user->operators()
                ->where('status', 'active')
                ->exists();

            if (!$hasActiveOperator) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.',
                ], 403);
            }
        }

        // إنشاء token (إذا كان Sanctum مثبت) أو استخدام session
        $token = null;
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken('mobile-app')->plainTextToken;
        }

        // Get role information
        $roleValue = $user->roleModel?->name ?? $user->role?->value;
        $roleLabel = $user->getRoleLabel();
        $roleId = $user->roleModel?->id;
        $isCustomRole = $user->hasCustomRole();
        
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $roleValue,
                    'role_id' => $roleId,
                    'role_label' => $roleLabel,
                    'role_type' => $isCustomRole ? 'custom' : 'system',
                    'is_technician' => $user->isTechnician(),
                    'is_civil_defense' => $this->isCivilDefense($user),
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // حذف token إذا كان Sanctum مستخدم
        if ($user && method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    /**
     * الحصول على معلومات المستخدم الحالي
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح.',
            ], 401);
        }

        // Get role information
        $roleValue = $user->roleModel?->name ?? $user->role?->value;
        $roleLabel = $user->getRoleLabel();
        $roleId = $user->roleModel?->id;
        $isCustomRole = $user->hasCustomRole();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $roleValue,
                    'role_id' => $roleId,
                    'role_label' => $roleLabel,
                    'role_type' => $isCustomRole ? 'custom' : 'system',
                    'is_technician' => $user->isTechnician(),
                    'is_civil_defense' => $this->isCivilDefense($user),
                ],
            ],
        ]);
    }

    /**
     * التحقق من أن المستخدم هو دفاع مدني
     * يمكن تخصيص هذا بناءً على role أو permission
     */
    private function isCivilDefense(User $user): bool
    {
        // يمكن التحقق من role مخصص أو permission
        // حالياً، يمكن استخدام role معين أو permission
        $roleName = $user->roleModel?->name ?? $user->role?->value;
        
        // يمكن إضافة role "civil_defense" أو استخدام role موجود
        // أو التحقق من permission معين
        return $roleName === 'civil_defense' 
            || $user->isEnergyAuthority() 
            || $user->hasPermission('compliance_safety.create');
    }

    /**
     * الحصول على بيانات الاعتماد
     */
    protected function getCredentials(Request $request): array
    {
        $username = $request->input('username');
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $username,
            'password' => $request->input('password'),
        ];
    }
}
