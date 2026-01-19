<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check honeypot field (anti-bot protection)
        if ($request->filled('website')) {
            // Bot detected, return generic error
            throw ValidationException::withMessages([
                'username' => __('بيانات الدخول غير صحيحة.'),
            ]);
        }

        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials = $this->getCredentials($request);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is system user (cannot login)
            if ($user->isSystemUser()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => __('بيانات الدخول غير صحيحة.'),
                ]);
            }

            // Check if user is suspended/banned
            if ($user->isSuspended()) {
                Auth::logout();
                $reason = $user->suspended_reason ? " - السبب: {$user->suspended_reason}" : '';
                throw ValidationException::withMessages([
                    'username' => __('حسابك محظور/معطل. يرجى التواصل مع الإدارة.').$reason,
                ]);
            }

            // Check if user is not active (inactive status)
            if (! $user->canLogin()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => __('حسابك معطل. يرجى التواصل مع الإدارة.'),
                ]);
            }

            // التحقق من حالة المشغل (إذا كان CompanyOwner)
            if ($user->isCompanyOwner()) {
                $operator = $user->ownedOperators()->first();
                if ($operator && $operator->status === 'inactive') {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'username' => __('حساب المشغل معطل. يرجى التواصل مع سلطة الطاقة.'),
                    ]);
                }
            }

            // التحقق من حالة المشغل (إذا كان Employee أو Technician)
            if ($user->isEmployee() || $user->isTechnician()) {
                $hasActiveOperator = $user->operators()
                    ->where('status', 'active')
                    ->exists();

                if (! $hasActiveOperator) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'username' => __('المشغل المرتبط بحسابك معطل. يرجى التواصل مع الإدارة.'),
                    ]);
                }
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        // Add small delay to prevent brute force attacks
        sleep(1);

        throw ValidationException::withMessages([
            'username' => __('بيانات الدخول غير صحيحة.'),
        ]);
    }

    /**
     * الحصول على CSRF token جديد (للتحديث التلقائي)
     */
    public function getCSRFToken(Request $request)
    {
        return response()->json([
            'token' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

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
