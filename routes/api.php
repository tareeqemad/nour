<?php

use App\Http\Controllers\Api\AuthController;
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

// Protected routes (require authentication)
// Note: إذا لم يكن Sanctum مثبت، استخدم 'auth:api' أو 'auth'
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // QR Code routes
    Route::post('/qr/scan', [QrController::class, 'scan']);
    
    // Maintenance routes (for Technicians)
    Route::prefix('maintenance')->group(function () {
        Route::get('/form-data/{generator}', [MaintenanceController::class, 'getFormData']);
        Route::post('/store', [MaintenanceController::class, 'store']);
        Route::get('/records', [MaintenanceController::class, 'index']);
        Route::get('/records/{maintenanceRecord}', [MaintenanceController::class, 'show']);
    });
    
    // Compliance Safety routes (for Civil Defense)
    Route::prefix('compliance-safety')->group(function () {
        Route::get('/form-data/{generator}', [ComplianceSafetyController::class, 'getFormData']);
        Route::post('/store', [ComplianceSafetyController::class, 'store']);
        Route::get('/records', [ComplianceSafetyController::class, 'index']);
        Route::get('/records/{complianceSafety}', [ComplianceSafetyController::class, 'show']);
    });
});
