<?php

use App\Http\Controllers\Admin\ComplaintSuggestionController;
use App\Http\Controllers\Admin\ComplianceSafetyController;
use App\Http\Controllers\Admin\ConstantDetailController;
use App\Http\Controllers\Admin\ConstantMasterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FuelEfficiencyController;
use App\Http\Controllers\Admin\GenerationUnitController;
use App\Http\Controllers\Admin\GeneratorController;
use App\Http\Controllers\Admin\MaintenanceRecordController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperationLogController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\OperatorProfileController;
use App\Http\Controllers\Admin\OperatorUnitNumberController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthorizedPhoneController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PermissionAuditLogController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\LockScreenController;
use App\Http\Controllers\Admin\VersionController;
use Illuminate\Support\Facades\Route;

// Lock Screen Routes - require auth but not lock check
Route::middleware(['auth'])->group(function () {
    Route::get('/lock-screen', [LockScreenController::class, 'show'])->name('lock-screen.show');
    Route::post('/lock-screen/lock', [LockScreenController::class, 'lock'])->name('lock-screen.lock');
    Route::post('/lock-screen/unlock', [LockScreenController::class, 'unlock'])->name('lock-screen.unlock');
});

Route::middleware(['auth', 'admin', 'operator.approved'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * User Profile (for all users)
     */
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile/change-password', [\App\Http\Controllers\Admin\ProfileController::class, 'changePassword'])->name('profile.change-password');

    /**
     * Permissions Tree
     */
    Route::prefix('permissions')->as('permissions.')->group(function () {
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::post('/assign', [PermissionsController::class, 'assignPermissions'])->name('assign');
        Route::match(['GET', 'POST'], '/search', [PermissionsController::class, 'search'])->name('search');
        Route::match(['GET', 'POST'], '/available', [PermissionsController::class, 'getAvailablePermissions'])->name('available');

        Route::get('/user/{user}', [PermissionsController::class, 'getUserPermissions'])->name('user');
        Route::get('/user/{user}/permissions', [PermissionsController::class, 'getUserPermissions'])->name('user.permissions');

        Route::get('/select2/operators', [PermissionsController::class, 'select2Operators'])->name('select2.operators');
        Route::get('/select2/users', [PermissionsController::class, 'select2Users'])->name('select2.users');
        Route::get('/select2/roles', [PermissionsController::class, 'select2Roles'])->name('select2.roles');
        Route::get('/select2/custom-roles/{operator}', [PermissionsController::class, 'select2CustomRoles'])->name('select2.custom-roles');
        Route::get('/role/{role}/permissions', [PermissionsController::class, 'getRolePermissions'])->name('role.permissions');
        Route::post('/role/{role}/assign', [PermissionsController::class, 'assignRolePermissions'])->name('role.assign');
    });

    /**
     * Permission Audit Logs
     */
    Route::get('permission-audit-logs', [PermissionAuditLogController::class, 'index'])->name('permission-audit-logs.index');
    Route::get('permission-audit-logs/{permissionAuditLog}', [PermissionAuditLogController::class, 'show'])->name('permission-audit-logs.show');

    /**
     * Activity Logs (Audit Logs)
     */
    Route::get('activity-logs', [AuditLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{auditLog}', [AuditLogController::class, 'show'])->name('activity-logs.show');

    /**
     * Roles (SuperAdmin only via Policy)
     */
    Route::resource('roles', RoleController::class);
    Route::post('roles/filter', [RoleController::class, 'filter'])->name('roles.filter');

    /**
     * Settings (SuperAdmin only)
     */
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::delete('settings/{setting}', [SettingsController::class, 'destroy'])->name('settings.destroy');

    /**
     * Constants (SuperAdmin only via Policy)
     */
    Route::resource('constants', ConstantMasterController::class);
    Route::get('constants/{constant}/details', [ConstantMasterController::class, 'show'])->name('constants.show');
    Route::get('constants/by-number/{number}', [ConstantMasterController::class, 'getByNumber'])->name('constants.get-by-number');
    Route::post('constant-details', [ConstantDetailController::class, 'store'])->name('constant-details.store');
    Route::put('constant-details/{constantDetail}', [ConstantDetailController::class, 'update'])->name('constant-details.update');
    Route::delete('constant-details/{constantDetail}', [ConstantDetailController::class, 'destroy'])->name('constant-details.destroy');
    Route::get('constant-details/by-master/{constantMaster}', [ConstantDetailController::class, 'getByMaster'])->name('constant-details.by-master');
    Route::get('constant-details/cities-by-governorate', [ConstantDetailController::class, 'getCitiesByGovernorate'])->name('constant-details.cities-by-governorate');

    /**
     * Users
     */
    Route::get('users/ajax/operators', [UserController::class, 'ajaxOperators'])
    ->name('users.ajaxOperators');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('users/stop-impersonating', [UserController::class, 'stopImpersonating'])->name('users.stop-impersonating');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class)->except(['create']);

    // المستخدمين النشطين
    Route::get('active-users', [\App\Http\Controllers\Admin\ActiveUsersController::class, 'index'])->name('active-users.index');

    // Operator employees (lock it via policy at route-level too)
    Route::get('operators/{operator}/employees', [UserController::class, 'operatorEmployees'])
        ->middleware('can:view,operator')
        ->name('operators.employees');

    /**
     * Operator profile must be before resource
     */
    Route::get('operators/profile', [OperatorProfileController::class, 'show'])->name('operators.profile');
    Route::get('operators/{operator}/profile', [OperatorProfileController::class, 'show'])->name('operators.profile.show');
    Route::put('operators/profile', [OperatorProfileController::class, 'update'])->name('operators.profile.update');

    Route::get('operators/next-unit-number/{governorate}', [OperatorUnitNumberController::class, 'getNextUnitNumber'])->name('operators.next-unit-number');
    Route::post('operators/generate-unit-code', [OperatorUnitNumberController::class, 'generateUnitCode'])->name('operators.generate-unit-code');
    Route::post('operators/{operator}/generate-generator-number', [OperatorController::class, 'generateGeneratorNumber'])->name('operators.generate-generator-number');
    
    // API للحصول على المشغلين حسب المحافظة
    Route::get('operators/by-governorate/{governorate}', [OperatorController::class, 'getByGovernorate'])->name('operators.by-governorate');

    /**
     * Operators
     */
    Route::post('operators/{operator}/toggle-status', [OperatorController::class, 'toggleStatus'])->name('operators.toggle-status');
    Route::post('operators/{operator}/toggle-approval', [OperatorController::class, 'toggleApproval'])->name('operators.toggle-approval');
    Route::get('operators/pending-approval', [OperatorController::class, 'pendingApproval'])->name('operators.pending-approval');
    Route::resource('operators', OperatorController::class);
    
    // Electricity Tariff Prices (nested under operators)
    Route::prefix('operators/{operator}')->name('operators.')->group(function () {
        Route::get('tariff-prices', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'index'])->name('tariff-prices.index');
        Route::get('tariff-prices/create', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'create'])->name('tariff-prices.create');
        Route::post('tariff-prices', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'store'])->name('tariff-prices.store');
        Route::get('tariff-prices/{tariffPrice}/edit', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'edit'])->name('tariff-prices.edit');
        Route::put('tariff-prices/{tariffPrice}', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'update'])->name('tariff-prices.update');
        Route::delete('tariff-prices/{tariffPrice}', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'destroy'])->name('tariff-prices.destroy');
        
        // API route for getting tariff price
        Route::get('api/tariff-price', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'getTariffPrice'])->name('api.tariff-price');
    });

    /**
     * Generators & related modules
     */
    Route::resource('generation-units', GenerationUnitController::class);
    Route::get('/operators/{operator}/data', [GenerationUnitController::class, 'getOperatorData'])->name('operators.data');
    Route::get('/operators/{operatorId}/generation-units-list', [GenerationUnitController::class, 'getGenerationUnitsByOperator'])->name('operators.generation-units-list');
    Route::get('/territories/all', [GenerationUnitController::class, 'getAllTerritories'])->name('territories.all');
    Route::get('/territories/check', [GenerationUnitController::class, 'checkTerritory'])->name('territories.check');
    Route::get('/generation-units/{generationUnit}/qr-code', [GenerationUnitController::class, 'qrCode'])->name('generation-units.qr-code');
    Route::get('/generation-units/{generationUnit}/can-delete', [GenerationUnitController::class, 'canDelete'])->name('generation-units.can-delete');
    Route::resource('generators', GeneratorController::class);
    Route::get('/generators/{generator}/transfer', [GeneratorController::class, 'showTransferForm'])->name('generators.transfer');
    Route::post('/generators/{generator}/transfer', [GeneratorController::class, 'transfer'])->name('generators.transfer.store');
    Route::get('/generators/{generator}/transfer/operators/{operator}/generation-units', [GeneratorController::class, 'getGenerationUnitsForTransfer'])->name('generators.transfer.generation-units');
    Route::get('/generators/{generator}/qr-code', [GeneratorController::class, 'qrCode'])->name('generators.qr-code');
    Route::get('/generators/{generator}/can-delete', [GeneratorController::class, 'canDelete'])->name('generators.can-delete');
    Route::get('/operators/{operator}/generation-units', [GeneratorController::class, 'getGenerationUnits'])->name('operators.generation-units');
    Route::get('/generation-units/{generationUnitId}/generators-list', [GeneratorController::class, 'getGeneratorsByGenerationUnit'])->name('generation-units.generators-list');
    Route::post('/generators/generate-number/{generationUnit}', [GeneratorController::class, 'generateGeneratorNumber'])->name('generators.generate-number');
    Route::resource('operation-logs', OperationLogController::class);
    Route::get('/operators/{operator}/generation-units-for-logs', [OperationLogController::class, 'getGenerationUnits'])->name('operation-logs.generation-units');
    Route::get('/generation-units/{generationUnit}/generators-for-logs', [OperationLogController::class, 'getGenerators'])->name('operation-logs.generators');
    
    // Fuel Efficiencies AJAX routes
    Route::get('/operators/{operator}/generation-units-for-efficiencies', [FuelEfficiencyController::class, 'getGenerationUnits'])->name('fuel-efficiencies.generation-units');
    Route::get('/generation-units/{generationUnit}/generators-for-efficiencies', [FuelEfficiencyController::class, 'getGenerators'])->name('fuel-efficiencies.generators');
    Route::resource('fuel-efficiencies', FuelEfficiencyController::class);
    Route::resource('maintenance-records', MaintenanceRecordController::class);
    Route::resource('compliance-safeties', ComplianceSafetyController::class);
    Route::resource('tasks', TaskController::class);
    
    // AJAX routes for tasks
    Route::get('tasks/operators/{operator}/generation-units', [TaskController::class, 'getGenerationUnits'])->name('tasks.generation-units');
    Route::get('tasks/generation-units/{generationUnit}/generators', [TaskController::class, 'getGeneratorsByGenerationUnit'])->name('tasks.generators');

    /**
     * Complaints & Suggestions
     */
    Route::get('complaints-suggestions', [ComplaintSuggestionController::class, 'index'])->name('complaints-suggestions.index');
    Route::get('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'show'])->name('complaints-suggestions.show');
    Route::get('complaints-suggestions/{complaintSuggestion}/edit', [ComplaintSuggestionController::class, 'edit'])->name('complaints-suggestions.edit');
    Route::put('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'update'])->name('complaints-suggestions.update');
    Route::post('complaints-suggestions/{complaintSuggestion}/respond', [ComplaintSuggestionController::class, 'respond'])->name('complaints-suggestions.respond');
    Route::post('complaints-suggestions/{complaintSuggestion}/close-by-operator', [ComplaintSuggestionController::class, 'closeByOperator'])->name('complaints-suggestions.close-by-operator');
    Route::delete('complaints-suggestions/{complaintSuggestion}', [ComplaintSuggestionController::class, 'destroy'])->name('complaints-suggestions.destroy');

    /**
     * Notifications
     */
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    /**
     * Messages (Internal Messaging System)
     */
    Route::get('messages/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unread-count');
    Route::get('messages/recent', [MessageController::class, 'getRecentMessages'])->name('messages.recent');
    Route::post('messages/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
    Route::post('messages/{message}/forward', [MessageController::class, 'forward'])->name('messages.forward');
    Route::post('messages/{message}/toggle-star', [MessageController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('messages/{message}/toggle-important', [MessageController::class, 'toggleImportant'])->name('messages.toggle-important');
    Route::post('messages/{message}/archive', [MessageController::class, 'archive'])->name('messages.archive');
    Route::post('messages/{message}/unarchive', [MessageController::class, 'unarchive'])->name('messages.unarchive');
    Route::resource('messages', MessageController::class);

    /**
     * Authorized Phones (Super Admin and Energy Authority)
     */
    Route::post('authorized-phones/{authorizedPhone}/toggle-status', [AuthorizedPhoneController::class, 'toggleStatus'])->name('authorized-phones.toggle-status');
    Route::post('authorized-phones/notify-pending', [AuthorizedPhoneController::class, 'notifyPending'])->name('authorized-phones.notify-pending');
    Route::post('authorized-phones/import', [AuthorizedPhoneController::class, 'import'])->name('authorized-phones.import');
    Route::delete('authorized-phones/delete-all', [AuthorizedPhoneController::class, 'deleteAll'])->name('authorized-phones.delete-all');
    Route::resource('authorized-phones', AuthorizedPhoneController::class);

    /**
     * System Logs (Super Admin only)
     */
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('logs/clear', [LogController::class, 'clear'])->name('logs.clear');
    Route::get('logs/download', [LogController::class, 'download'])->name('logs.download');

    /**
     * User Guide (الدليل الإرشادي)
     */
    Route::get('guide', [GuideController::class, 'index'])->name('guide.index');

    /**
     * Version & About (الإصدارات وحول النظام)
     */
    Route::get('about', [VersionController::class, 'about'])->name('about');
    Route::get('changelog', [VersionController::class, 'changelog'])->name('changelog');
    
    // إدارة الإصدارات (SuperAdmin فقط)
    Route::prefix('versions')->as('versions.')->group(function () {
        Route::get('/', [VersionController::class, 'index'])->name('index');
        Route::get('/create', [VersionController::class, 'create'])->name('create');
        Route::post('/', [VersionController::class, 'store'])->name('store');
        Route::get('/{version}', [VersionController::class, 'show'])->name('show');
        Route::get('/{version}/edit', [VersionController::class, 'edit'])->name('edit');
        Route::put('/{version}', [VersionController::class, 'update'])->name('update');
        Route::delete('/{version}', [VersionController::class, 'destroy'])->name('destroy');
        Route::post('/{version}/set-current', [VersionController::class, 'setCurrent'])->name('set-current');
    });

    /**
     * Welcome Messages (الرسائل الترحيبية)
     */
    Route::get('welcome-messages', [\App\Http\Controllers\Admin\WelcomeMessageController::class, 'index'])->name('welcome-messages.index');
    Route::get('welcome-messages/{welcomeMessage}/edit', [\App\Http\Controllers\Admin\WelcomeMessageController::class, 'edit'])->name('welcome-messages.edit');
    Route::put('welcome-messages/{welcomeMessage}', [\App\Http\Controllers\Admin\WelcomeMessageController::class, 'update'])->name('welcome-messages.update');

    /**
     * SMS Templates (قوالب رسائل الجوال)
     */
    Route::get('sms-templates', [\App\Http\Controllers\Admin\SmsTemplateController::class, 'index'])->name('sms-templates.index');
    Route::get('sms-templates/{smsTemplate}/edit', [\App\Http\Controllers\Admin\SmsTemplateController::class, 'edit'])->name('sms-templates.edit');
    Route::put('sms-templates/{smsTemplate}', [\App\Http\Controllers\Admin\SmsTemplateController::class, 'update'])->name('sms-templates.update');
});
