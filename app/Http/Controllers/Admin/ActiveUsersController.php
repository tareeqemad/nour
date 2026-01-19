<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiveUsersController extends Controller
{
    /**
     * عرض قائمة المستخدمين النشطين
     */
    public function index(Request $request): View
    {
        $authUser = auth()->user();

        // التحقق من الصلاحيات: SuperAdmin, Admin, EnergyAuthority فقط
        if (!$authUser->isSuperAdmin() && !$authUser->isAdmin() && !$authUser->isEnergyAuthority()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        // تحديد الوقت للنشاط (آخر 15 دقيقة)
        $activeThreshold = now()->subMinutes(15);

        // جلب المستخدمين النشطين
        $query = User::where('last_activity', '>=', $activeThreshold)
            ->where('status', 'active')
            ->orderBy('last_activity', 'desc');

        // Energy Authority: لا يرى SuperAdmin
        if ($authUser->isEnergyAuthority()) {
            $query->where('role', '!=', \App\Enums\Role::SuperAdmin->value);
        }

        $activeUsers = $query->get();

        // إحصائيات
        $stats = [
            'total_active' => $activeUsers->count(),
            'by_role' => $activeUsers->groupBy('role')->map->count(),
        ];

        return view('admin.active-users.index', compact('activeUsers', 'stats'));
    }
}
