<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $user = auth()->user();
        $query = AuditLog::with('user');

        // فلترة حسب المستخدم
        if ($user->isCompanyOwner()) {
            // المشغل يشوف نشاطات المستخدمين التابعين له فقط
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operatorUserIds = User::where(function ($q) use ($operator) {
                    $q->whereHas('operators', function ($qq) use ($operator) {
                        $qq->where('operators.id', $operator->id);
                    })->orWhere('id', $operator->owner_id);
                })->pluck('id')->toArray();
                
                $query->whereIn('user_id', $operatorUserIds);
            } else {
                // إذا لم يكن لديه مشغل، يشوف نشاطاته فقط
                $query->where('user_id', $user->id);
            }
        }
        // السوبر أدمن يشوف كل شيء (لا فلترة)

        // فلترة بالبحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // فلترة بالإجراء
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // فلترة بالمستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // فلترة بنوع الموديل
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->input('model_type'));
        }

        // فلترة بالتاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // AJAX request
        if ($request->ajax() || $request->has('ajax')) {
            $logs = $query->orderBy('created_at', 'desc')->paginate(20);
            
            $html = view('admin.audit-logs.partials.tbody-rows', ['logs' => $logs])->render();
            $pagination = view('admin.audit-logs.partials.pagination', ['logs' => $logs])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $logs->total(),
            ]);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // جلب المستخدمين للفلترة (للسوبر أدمن)
        $users = collect();
        if ($user->isSuperAdmin()) {
            $users = User::orderBy('name')->get(['id', 'name', 'username']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $users = User::whereHas('operators', function ($q) use ($operator) {
                    $q->where('operators.id', $operator->id);
                })->orWhere('id', $operator->owner_id)->orderBy('name')->get(['id', 'name', 'username']);
            }
        }

        // أنواع الإجراءات
        $actions = ['create', 'update', 'delete', 'view', 'login', 'logout'];

        // أنواع الموديلات الموجودة
        $modelTypes = AuditLog::distinct()->pluck('model_type')->filter()->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'modelTypes'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): View
    {
        $this->authorize('view', $auditLog);

        $auditLog->load('user');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}

