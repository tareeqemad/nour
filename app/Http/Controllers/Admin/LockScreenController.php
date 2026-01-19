<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LockScreenController extends Controller
{
    /**
     * Show the lock screen.
     */
    public function show()
    {
        // Check if user is already locked
        if (!session('screen_locked')) {
            return redirect()->route('admin.dashboard');
        }

        $user = auth()->user();
        
        return view('admin.lock-screen', compact('user'));
    }

    /**
     * Lock the screen.
     */
    public function lock(Request $request)
    {
        // Set session variable to indicate screen is locked
        session(['screen_locked' => true]);
        session(['locked_at' => now()]);
        session(['locked_user_id' => auth()->id()]);
        
        return response()->json([
            'success' => true,
            'redirect' => route('admin.lock-screen.show')
        ]);
    }

    /**
     * Unlock the screen.
     */
    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        $user = auth()->user();

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'كلمة المرور غير صحيحة'
            ])->withInput();
        }

        // Clear lock session
        session()->forget(['screen_locked', 'locked_at', 'locked_user_id']);

        return redirect()->route('admin.dashboard')->with('success', 'تم فتح القفل بنجاح');
    }
}

