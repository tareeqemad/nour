<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionAuditLog;
use Illuminate\View\View;

class PermissionAuditLogController extends Controller
{
    public function index(): View
    {
        // التحقق من الصلاحية باستخدام Policy
        $this->authorize('viewAny', PermissionAuditLog::class);

        $user = auth()->user();
        $query = PermissionAuditLog::with(['user', 'performedBy', 'permission']);

        // SuperAdmin يرى جميع السجلات
        // CompanyOwner يرى فقط سجلات موظفيه وفنييه
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operatorUserIds = $operator->users->pluck('id')->toArray();
                $query->whereIn('user_id', $operatorUserIds);
            } else {
                $query->whereRaw('1 = 0'); // لا يوجد سجلات
            }
        }
        // السوبر أدمن يرى كل شيء (لا فلترة)

        $auditLogs = $query->latest()->paginate(20);

        return view('admin.permission-audit-logs.index', compact('auditLogs'));
    }

    public function show(PermissionAuditLog $permissionAuditLog): View
    {
        // التحقق من الصلاحية باستخدام Policy
        $this->authorize('view', $permissionAuditLog);

        $permissionAuditLog->load(['user', 'performedBy', 'permission']);

        return view('admin.permission-audit-logs.show', compact('permissionAuditLog'));
    }
}
