<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * عرض صفحة الملف الشخصي
     */
    public function show(Request $request): View
    {
        $user = auth()->user();
        return view('admin.profile.show', compact('user'));
    }

    /**
     * تغيير كلمة مرور المستخدم
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $msg = 'تم تغيير كلمة المرور بنجاح ✅';

        // AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('admin.profile.show')->with('success', $msg);
    }
}
