<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Front Routes
Route::get('/', [\App\Http\Controllers\PublicHomeController::class, 'index'])->name('front.home');
Route::get('/map', [\App\Http\Controllers\PublicHomeController::class, 'map'])->name('front.map');
Route::get('/stats', [\App\Http\Controllers\PublicHomeController::class, 'stats'])->name('front.stats');
Route::get('/about', [\App\Http\Controllers\PublicHomeController::class, 'about'])->name('front.about');

// Join routes - protected with guest middleware
Route::middleware('guest')->group(function () {
    Route::get('/join', [\App\Http\Controllers\PublicHomeController::class, 'join'])->name('front.join');
    Route::post('/join', [\App\Http\Controllers\PublicHomeController::class, 'storeJoinRequest'])->name('front.join.store');
});

Route::get('/api/operators/map', [\App\Http\Controllers\PublicHomeController::class, 'getOperatorsForMap'])->name('front.operators.map');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/login/csrf-token', [LoginController::class, 'getCSRFToken'])->name('login.csrf-token');
    
    // Password Reset Routes
    Route::post('/password/reset/send-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOTP'])->name('password.reset.send-otp');
    Route::post('/password/reset/verify-otp', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOTPAndResetPassword'])->name('password.reset.verify-otp');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/complaints-suggestions', [\App\Http\Controllers\ComplaintSuggestionController::class, 'index'])->name('complaints-suggestions.index');
Route::get('/complaints-suggestions/create', [\App\Http\Controllers\ComplaintSuggestionController::class, 'create'])->name('complaints-suggestions.create');
Route::post('/complaints-suggestions', [\App\Http\Controllers\ComplaintSuggestionController::class, 'store'])->name('complaints-suggestions.store');
Route::get('/complaints-suggestions/track', [\App\Http\Controllers\ComplaintSuggestionController::class, 'track'])->name('complaints-suggestions.track');
Route::post('/complaints-suggestions/search', [\App\Http\Controllers\ComplaintSuggestionController::class, 'search'])->name('complaints-suggestions.search');
Route::get('/complaints-suggestions/operators/by-governorate/{governorate}', [\App\Http\Controllers\ComplaintSuggestionController::class, 'getOperatorsByGovernorate'])->name('complaints-suggestions.operators-by-governorate');
Route::get('/complaints-suggestions/generators', [\App\Http\Controllers\ComplaintSuggestionController::class, 'getGeneratorsByLocation'])->name('complaints-suggestions.generators');
Route::get('/complaints-suggestions/generators-by-operator', [\App\Http\Controllers\ComplaintSuggestionController::class, 'getGeneratorsByOperator'])->name('complaints-suggestions.generators-by-operator');

// QR Code Public Routes
Route::get('/qr/generation-unit/{code}', [\App\Http\Controllers\PublicQrController::class, 'generationUnit'])->name('qr.generation-unit');
Route::get('/qr/generator/{code}', [\App\Http\Controllers\PublicQrController::class, 'generator'])->name('qr.generator');


