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
use App\Http\Controllers\Admin\PortalRequestController;
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
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/pie-chart-data', [DashboardController::class, 'pieChartData'])->name('dashboard.pie-chart-data');
    Route::get('/dashboard/operators-comparison', [DashboardController::class, 'operatorsComparison'])->name('dashboard.operators-comparison');
    Route::get('/dashboard/generation-units-comparison', [DashboardController::class, 'generationUnitsComparison'])->name('dashboard.generation-units-comparison');

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
        Route::post('/assign', [PermissionsController::class, 'assignPermissions'])
            ->middleware('throttle:10,1') // Rate limit: 10 requests per minute
            ->name('assign');
        Route::match(['GET', 'POST'], '/search', [PermissionsController::class, 'search'])->name('search');
        Route::match(['GET', 'POST'], '/available', [PermissionsController::class, 'getAvailablePermissions'])->name('available');

        Route::get('/user/{user}', [PermissionsController::class, 'getUserPermissions'])->name('user');
        Route::get('/user/{user}/permissions', [PermissionsController::class, 'getUserPermissions'])->name('user.permissions');

        Route::get('/lookup/operators', [PermissionsController::class, 'select2Operators'])->name('select2.operators');
        Route::get('/lookup/users', [PermissionsController::class, 'select2Users'])->name('select2.users');
        Route::get('/lookup/roles', [PermissionsController::class, 'select2Roles'])->name('select2.roles');
        Route::get('/lookup/custom-roles/{operator}', [PermissionsController::class, 'select2CustomRoles'])->name('select2.custom-roles');
        Route::get('/role/{role}/permissions', [PermissionsController::class, 'getRolePermissions'])->name('role.permissions');
        Route::post('/role/{role}/assign', [PermissionsController::class, 'assignRolePermissions'])
            ->middleware('throttle:10,1') // Rate limit: 10 requests per minute
            ->name('role.assign');
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
    
    // Electricity Tariff Prices (عامة لجميع المشغلين)
    Route::get('electricity-tariff-prices', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'index'])->name('electricity-tariff-prices.index');
    Route::get('electricity-tariff-prices/create', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'create'])->name('electricity-tariff-prices.create');
    Route::post('electricity-tariff-prices', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'store'])->name('electricity-tariff-prices.store');
    Route::get('electricity-tariff-prices/{electricityTariffPrice}/edit', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'edit'])->name('electricity-tariff-prices.edit');
    Route::put('electricity-tariff-prices/{electricityTariffPrice}', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'update'])->name('electricity-tariff-prices.update');
    Route::delete('electricity-tariff-prices/{electricityTariffPrice}', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'destroy'])->name('electricity-tariff-prices.destroy');
    
    // API route for getting tariff price
    Route::get('api/tariff-price', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'getTariffPrice'])->name('api.tariff-price');

    // Employee Discount Rates (نسب خصم الموظفين)
    Route::get('employee-discount-rates', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'index'])->name('employee-discount-rates.index');
    Route::get('employee-discount-rates/create', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'create'])->name('employee-discount-rates.create');
    Route::post('employee-discount-rates', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'store'])->name('employee-discount-rates.store');
    Route::get('employee-discount-rates/{employeeDiscountRate}/edit', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'edit'])->name('employee-discount-rates.edit');
    Route::put('employee-discount-rates/{employeeDiscountRate}', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'update'])->name('employee-discount-rates.update');
    Route::delete('employee-discount-rates/{employeeDiscountRate}', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'destroy'])->name('employee-discount-rates.destroy');
    Route::get('api/employee-discount-rate', [\App\Http\Controllers\Admin\EmployeeDiscountRateController::class, 'getDiscountRate'])->name('api.employee-discount-rate');
    
    // Electricity Tariff Prices (nested under operators - للعرض فقط، الأسعار العامة فقط)
    Route::prefix('operators/{operator}')->name('operators.')->group(function () {
        Route::get('tariff-prices', [\App\Http\Controllers\Admin\ElectricityTariffPriceController::class, 'indexForOperator'])->name('tariff-prices.index');
        // تم إزالة routes الإنشاء والتعديل لأن الأسعار عامة فقط
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

    // Maintenance Records AJAX routes (cascading: operator → generation unit → generator)
    Route::get('/operators/{operator}/generation-units-for-maintenance-records', [MaintenanceRecordController::class, 'getGenerationUnits'])->name('maintenance-records.generation-units');
    Route::get('/generation-units/{generationUnit}/generators-for-maintenance-records', [MaintenanceRecordController::class, 'getGenerators'])->name('maintenance-records.generators');
    Route::get('/operators/{operator}/generation-units-for-compliance-safeties', [ComplianceSafetyController::class, 'getGenerationUnits'])->name('compliance-safeties.generation-units');
    Route::get('/generation-units/{generationUnit}/generators-for-compliance-safeties', [ComplianceSafetyController::class, 'getGenerators'])->name('compliance-safeties.generators');
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
     * Submitted Portal Requests (via Palestinian Digital Portal)
     */
    Route::get('portal-requests', [PortalRequestController::class, 'index'])->name('portal-requests.index');
    Route::get('portal-requests/{appId}', [PortalRequestController::class, 'show'])->name('portal-requests.show');
    Route::get('portal-requests/{appId}/change-status', [PortalRequestController::class, 'changeStatus'])->name('portal-requests.change-status');

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

    /**
     * Subscribers (إدارة بيانات المشتركين)
     */
    Route::get('subscribers/export', [\App\Http\Controllers\Admin\SubscriberController::class, 'export'])
        ->name('subscribers.export');
    Route::resource('subscribers', \App\Http\Controllers\Admin\SubscriberController::class);
    Route::post('subscribers/check-unique', [\App\Http\Controllers\Admin\SubscriberController::class, 'checkUnique'])
        ->name('subscribers.check-unique');
    Route::get('/operators/{operator}/generation-units-for-subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'getGenerationUnits'])
        ->name('subscribers.generation-units');
    
    // Subscriber Import Routes
    Route::get('subscribers/import/modal', [\App\Http\Controllers\Admin\SubscriberImportController::class, 'showImportModal'])
        ->name('subscribers.import.modal');
    Route::post('subscribers/import/preview', [\App\Http\Controllers\Admin\SubscriberImportController::class, 'preview'])
        ->name('subscribers.import.preview');
    Route::post('subscribers/import/execute', [\App\Http\Controllers\Admin\SubscriberImportController::class, 'import'])
        ->name('subscribers.import.execute');
    Route::get('subscribers/import/template', [\App\Http\Controllers\Admin\SubscriberImportController::class, 'downloadTemplate'])
        ->name('subscribers.import.template');
    
    // Subscription Number API Routes
    Route::get('/subscription-number/preview', [\App\Http\Controllers\Admin\SubscriptionNumberController::class, 'preview'])->name('subscription-number.preview');
    Route::get('/subscription-number/stats', [\App\Http\Controllers\Admin\SubscriptionNumberController::class, 'stats'])->name('subscription-number.stats');

    /**
     * Meter Readings (قراءات العدادات)
     */
    Route::get('meter-readings/bulk-create',          [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkCreate'])->name('meter-readings.bulk-create');
    Route::post('meter-readings/bulk-create/fetch',    [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkCreateFetch'])->name('meter-readings.bulk-create.fetch');
    Route::post('meter-readings/bulk-create/store',    [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkCreateStore'])->name('meter-readings.bulk-create.store');
    Route::post('meter-readings/bulk-create/export',   [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkCreateExport'])->name('meter-readings.bulk-create.export');
    Route::get('meter-readings/export',                [\App\Http\Controllers\Admin\MeterReadingController::class, 'export'])->name('meter-readings.export');
    Route::post('meter-readings/bulk-approve',        [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkApprove'])->name('meter-readings.bulk-approve');
    Route::post('meter-readings/bulk-approve-filter',   [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkApproveByFilter'])->name('meter-readings.bulk-approve-filter');
    Route::post('meter-readings/bulk-approve-abnormal', [\App\Http\Controllers\Admin\MeterReadingController::class, 'bulkApproveAbnormal'])->name('meter-readings.bulk-approve-abnormal');
    Route::post('meter-readings/{meterReading}/approve-abnormal', [\App\Http\Controllers\Admin\MeterReadingController::class, 'approveAbnormal'])->name('meter-readings.approve-abnormal');
    Route::get('meter-readings/subscribers-by-operator', [\App\Http\Controllers\Admin\MeterReadingController::class, 'getSubscribersByOperator'])->name('meter-readings.subscribers-by-operator');
    Route::get('meter-readings/subscriber/last-reading', [\App\Http\Controllers\Admin\MeterReadingController::class, 'getLastReading'])->name('meter-readings.last-reading');
    Route::resource('meter-readings', \App\Http\Controllers\Admin\MeterReadingController::class);

    /**
     * الفوترة والتحصيل (Invoices)
     */
    Route::post('invoices/{invoice}/issue',  [\App\Http\Controllers\Admin\InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('invoices/{invoice}/cancel', [\App\Http\Controllers\Admin\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/print',   [\App\Http\Controllers\Admin\InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/pdf',     [\App\Http\Controllers\Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('invoices/reading-data',      [\App\Http\Controllers\Admin\InvoiceController::class, 'getReadingData'])->name('invoices.reading-data');
    Route::get('invoices/subscribers-by-operator', [\App\Http\Controllers\Admin\InvoiceController::class, 'getSubscribersByOperator'])->name('invoices.subscribers-by-operator');
    Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class);

    /**
     * المدفوعات (Payments) - مشتقة تحت الفواتير
     */
    Route::get('invoices/{invoice}/payments',          [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('invoices.payments.index');
    Route::get('invoices/{invoice}/payments/create',   [\App\Http\Controllers\Admin\PaymentController::class, 'create'])->name('invoices.payments.create');
    Route::post('invoices/{invoice}/payments',         [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('invoices.payments.store');
    Route::get('invoices/{invoice}/payments/{payment}',[\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('invoices.payments.show');
    Route::delete('invoices/{invoice}/payments/{payment}',[\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('invoices.payments.destroy');

    /**
     * حساب المشترك (Subscriber Account)
     */
    Route::get('subscriber-account', [\App\Http\Controllers\Admin\SubscriberAccountController::class, 'index'])->name('subscriber-account.index');
    Route::get('subscriber-account/{subscriber}', [\App\Http\Controllers\Admin\SubscriberAccountController::class, 'show'])->name('subscriber-account.show');

    /**
     * تقارير الفوترة والتحصيل
     */
    Route::get('invoice-reports', [\App\Http\Controllers\Admin\InvoiceReportController::class, 'index'])->name('invoice-reports.index');

    /**
     * قواعد الحد الأدنى للفاتورة
     */
    Route::get('minimum-charge-rules', [\App\Http\Controllers\Admin\MinimumChargeRuleController::class, 'index'])->name('minimum-charge-rules.index');
    Route::put('minimum-charge-rules/{minimumChargeRule}', [\App\Http\Controllers\Admin\MinimumChargeRuleController::class, 'update'])->name('minimum-charge-rules.update');

    /**
     * قضايا التفتيش والتعدي (معرّفة صراحة لتفادي 404 من القائمة)
     */
    Route::get('inspection-violation-cases/export', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'export'])->name('inspection-violation-cases.export');
    Route::get('inspection-violation-cases', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'index'])->name('inspection-violation-cases.index');
    Route::get('inspection-violation-cases/create', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'create'])->name('inspection-violation-cases.create');
    Route::post('inspection-violation-cases', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'store'])->name('inspection-violation-cases.store');
    Route::get('inspection-violation-cases/{inspection_violation_case}', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'show'])->name('inspection-violation-cases.show');
    Route::get('inspection-violation-cases/{inspection_violation_case}/edit', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'edit'])->name('inspection-violation-cases.edit');
    Route::put('inspection-violation-cases/{inspection_violation_case}', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'update'])->name('inspection-violation-cases.update');
    Route::delete('inspection-violation-cases/{inspection_violation_case}', [\App\Http\Controllers\Admin\InspectionViolationCaseController::class, 'destroy'])->name('inspection-violation-cases.destroy');
    Route::post('inspection-violation-cases/import/preview', [\App\Http\Controllers\Admin\InspectionViolationCaseImportController::class, 'preview'])->name('inspection-violation-cases.import.preview');
    Route::post('inspection-violation-cases/import/execute', [\App\Http\Controllers\Admin\InspectionViolationCaseImportController::class, 'import'])->name('inspection-violation-cases.import.execute');
    Route::get('inspection-violation-cases/import/template', [\App\Http\Controllers\Admin\InspectionViolationCaseImportController::class, 'downloadTemplate'])->name('inspection-violation-cases.import.template');
});
