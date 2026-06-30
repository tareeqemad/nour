<?php

use App\Http\Controllers\Madmoun\DashboardController;
use App\Http\Controllers\Madmoun\PolicyController;
use App\Http\Middleware\Madmoun\MadmounAccess;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| راوتس منصة مضمون
|--------------------------------------------------------------------------
| كل الراوتس هنا تحت البادئة /madmoun وبأسماء madmoun.*
| الحماية: auth (مسجّل دخول) + admin (فحوصات الحظر/التعطيل المشتركة)
|          + MadmounAccess (بوابة صلاحيات/اشتراك مضمون).
|
| مبرمج مضمون: أضف راوتسك داخل هذه المجموعة. لا حاجة للمساس بـ routes/admin.php.
*/
Route::middleware(['auth', 'admin', MadmounAccess::class])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* ↓↓↓ راوتس مضمون تُضاف هنا ↓↓↓ */

    // مثال: بوالص الضمان
    Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');

});
