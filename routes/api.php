<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeneratorController;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ComplianceSafetyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login']);

// بيانات المولد من QR Code - للتطبيق المحمول (بدون تسجيل دخول)
Route::get('/generators/qr-data/{code}', [GeneratorController::class, 'qrData'])->name('api.generators.qr-data');

// Protected routes (require authentication)
// Note: إذا لم يكن Sanctum مثبت، استخدم 'auth:api' أو 'auth'
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // QR Code routes
    Route::post('/qr/scan', [QrController::class, 'scan']);
    
    // Maintenance routes (for Technicians فقط)
    Route::prefix('maintenance')->group(function () {
        Route::get('/form-data/{generator}', [MaintenanceController::class, 'getFormData']);
        Route::post('/store', [MaintenanceController::class, 'store']);
        Route::put('/records/{maintenanceRecord}', [MaintenanceController::class, 'update']);
        Route::get('/records', [MaintenanceController::class, 'index']);
        Route::get('/records/{maintenanceRecord}', [MaintenanceController::class, 'show']);
    });

    // Compliance Safety routes (لدفاع مدني فقط)
    Route::prefix('compliance-safety')->group(function () {
        Route::get('/form-data/{generator}', [ComplianceSafetyController::class, 'getFormData']);
        Route::post('/store', [ComplianceSafetyController::class, 'store']);
        Route::put('/records/{complianceSafety}', [ComplianceSafetyController::class, 'update']);
        Route::get('/records', [ComplianceSafetyController::class, 'index']);
        Route::get('/records/{complianceSafety}', [ComplianceSafetyController::class, 'show']);
    });
});
