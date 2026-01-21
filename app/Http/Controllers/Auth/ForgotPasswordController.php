<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HotSMSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * إرسال رمز OTP إلى رقم الجوال
     */
    public function sendOTP(Request $request)
    {
        // Rate limiting: 3 محاولات كل 15 دقيقة لكل IP
        $key = 'send-otp:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => "تم تجاوز عدد المحاولات المسموح بها. يرجى المحاولة مرة أخرى بعد {$seconds} ثانية.",
            ]);
        }

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^0(59|56)\d{7}$/'],
        ]);

        // تنظيف رقم الجوال
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));

        // التحقق من وجود المستخدم بهذا الرقم
        $user = User::where('phone', $cleanPhone)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            // لا نخبر المستخدم بأن الرقم غير موجود (لمنع التطفل)
            // نعطي رسالة عامة
            RateLimiter::hit($key, 900); // 15 دقيقة
            throw ValidationException::withMessages([
                'phone' => 'إذا كان هذا الرقم مسجلاً في النظام، سيتم إرسال رمز التحقق إليه.',
            ]);
        }

        // التحقق من أن المستخدم ليس system user
        if ($user->isSystemUser()) {
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'phone' => 'إذا كان هذا الرقم مسجلاً في النظام، سيتم إرسال رمز التحقق إليه.',
            ]);
        }

        // توليد رمز OTP (6 أرقام)
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // حفظ OTP في Cache لمدة 10 دقائق
        $cacheKey = 'password-reset-otp:' . $cleanPhone;
        Cache::put($cacheKey, [
            'otp' => $otp,
            'user_id' => $user->id,
            'attempts' => 0, // عدد محاولات التحقق
        ], now()->addMinutes(10));

        // Get site name from settings
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        
        // إرسال OTP عبر SMS
        try {
            $smsService = new HotSMSService;
            $message = "رمز استعادة كلمة المرور لمنصة {$siteName}: {$otp}\nهذا الرمز صالح لمدة 10 دقائق فقط.";
            $result = $smsService->sendSMS($cleanPhone, $message, 2);

            if ($result['success']) {
                // Rate limiter: نجح الإرسال
                RateLimiter::hit($key, 900);

                \Log::info('Password reset OTP sent', [
                    'user_id' => $user->id,
                    'phone' => $cleanPhone,
                    'message_id' => $result['message_id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إرسال رمز التحقق إلى رقم الجوال المسجل.',
                ]);
            } else {
                \Log::error('Failed to send password reset OTP', [
                    'user_id' => $user->id,
                    'phone' => $cleanPhone,
                    'error_code' => $result['code'],
                    'error_message' => $result['message'],
                ]);

                RateLimiter::hit($key, 900);
                throw ValidationException::withMessages([
                    'phone' => 'حدث خطأ أثناء إرسال رمز التحقق. يرجى المحاولة مرة أخرى لاحقاً.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception while sending password reset OTP', [
                'user_id' => $user->id,
                'phone' => $cleanPhone,
                'error' => $e->getMessage(),
            ]);

            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'phone' => 'حدث خطأ أثناء إرسال رمز التحقق. يرجى المحاولة مرة أخرى لاحقاً.',
            ]);
        }
    }

    /**
     * التحقق من رمز OTP وإعادة تعيين كلمة المرور
     */
    public function verifyOTPAndResetPassword(Request $request)
    {
        // Rate limiting: 5 محاولات كل 15 دقيقة لكل IP
        $key = 'verify-otp:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => "تم تجاوز عدد المحاولات المسموح بها. يرجى المحاولة مرة أخرى بعد {$seconds} ثانية.",
            ]);
        }

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^0(59|56)\d{7}$/'],
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $otp = $request->input('otp');

        // جلب OTP من Cache
        $cacheKey = 'password-reset-otp:' . $cleanPhone;
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'otp' => 'رمز التحقق غير صحيح أو انتهت صلاحيته. يرجى طلب رمز جديد.',
            ]);
        }

        // التحقق من عدد المحاولات (حد أقصى 3 محاولات)
        if ($otpData['attempts'] >= 3) {
            Cache::forget($cacheKey);
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'otp' => 'تم تجاوز عدد محاولات التحقق المسموح بها. يرجى طلب رمز جديد.',
            ]);
        }

        // التحقق من OTP
        if ($otpData['otp'] !== $otp) {
            // زيادة عدد المحاولات
            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'otp' => 'رمز التحقق غير صحيح.',
            ]);
        }

        // OTP صحيح - جلب المستخدم
        $user = User::find($otpData['user_id']);

        if (!$user || $user->phone !== $cleanPhone) {
            Cache::forget($cacheKey);
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages([
                'otp' => 'حدث خطأ. يرجى المحاولة مرة أخرى.',
            ]);
        }

        // توليد كلمة مرور جديدة = username_123
        $newPassword = $user->username . '_123';
        $hashedPassword = Hash::make($newPassword);

        // تحديث كلمة المرور وحفظ تاريخ إعادة التعيين
        $user->update([
            'password' => $hashedPassword,
            'password_reset_at' => now(),
        ]);

        // حذف OTP من Cache
        Cache::forget($cacheKey);

        // إرسال SMS بكلمة المرور الجديدة
        try {
            $roleModel = $user->roleModel;
            $smsService = new HotSMSService;
            $loginUrl = url('/login');
            
            // استخدام template من قاعدة البيانات أو رسالة افتراضية
            $smsTemplate = \App\Models\SmsTemplate::getByKey('password_reset');
            
            if ($smsTemplate) {
                $roleName = $roleModel && $roleModel->label ? $roleModel->label : 'مستخدم';
                $message = $smsTemplate->render([
                    'name' => $user->name,
                    'username' => $user->username,
                    'password' => $newPassword,
                    'role' => $roleName,
                    'login_url' => $loginUrl,
                ]);
            } else {
                // Get site name from settings
                $siteName = \App\Models\Setting::get('site_name', 'نور');
                
                // رسالة تفصيلية (حتى 220 حرف)
                $message = "مرحباً {$user->name}،\nتم إعادة تعيين كلمة المرور لحسابك على منصة {$siteName}.\n\nاسم المستخدم: {$user->username}\nكلمة المرور الجديدة: {$newPassword}\n\nرابط الدخول: {$loginUrl}";
            }

            $result = $smsService->sendSMS($cleanPhone, $message, 2);

            if ($result['success']) {
                \Log::info('Password reset successful', [
                    'user_id' => $user->id,
                    'phone' => $cleanPhone,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                \Log::warning('Password reset successful but SMS failed', [
                    'user_id' => $user->id,
                    'phone' => $cleanPhone,
                    'error_code' => $result['code'],
                    'error_message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception while sending password reset SMS', [
                'user_id' => $user->id,
                'phone' => $cleanPhone,
                'error' => $e->getMessage(),
            ]);
            // لا نعيد خطأ - كلمة المرور تم تحديثها بنجاح
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح. تم إرسال كلمة المرور الجديدة عبر SMS.',
        ]);
    }
}
