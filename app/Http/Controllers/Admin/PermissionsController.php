<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\Permission;
use App\Models\PermissionAuditLog;
use App\Models\Role;
use App\Models\User;
use App\Enums\Role as RoleEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PermissionsController extends Controller
{
    /**
     * تنظيف مدخلات البحث لمنع SQL Injection
     * 
     * @param string|null $input
     * @return string
     */
    protected function sanitizeSearchInput(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        // إزالة المسافات الزائدة
        $input = trim($input);
        
        // إزالة HTML tags
        $input = strip_tags($input);
        
        // إزالة الأحرف الخاصة التي قد تستخدم في SQL Injection
        // لكن نترك % و _ لأنها مفيدة في LIKE queries
        $input = preg_replace('/[;\'"\\\]/', '', $input);
        
        // تحديد طول أقصى للبحث (255 حرف)
        $input = mb_substr($input, 0, 255);
        
        return $input;
    }
    private ?array $cachedTenantAssignablePermissionIds = null;

    /**
     * صلاحيات Tenant اللي مسموح للمشغل يوزعها (حتى لو عنده غيرها بالغلط).
     * عدّل القائمة حسب Modules اللي بدك المشغل يتحكم فيها فقط.
     * 
     * ملاحظات مهمة:
     * - صلاحيات welcome_messages و sms_templates غير متاحة للمشغلين (مخصصة فقط للـ Admin و SuperAdmin)
     * - صلاحيات settings و constants و logs غير متاحة للمشغلين
     * - صلاحيات users و roles و permissions محدودة حسب السقف (companyOwnerCeiling)
     */
    private function tenantAssignablePermissionIds(): array
    {
        if ($this->cachedTenantAssignablePermissionIds !== null) {
            return $this->cachedTenantAssignablePermissionIds;
        }

        $ids = Permission::query()
            ->where(function ($q) {
                // Operators: نخليها View فقط (لو بدك)
                $q->where('name', '=', 'operators.view')

                    // Tenant Modules
                    ->orWhere('name', 'like', 'generators.%')
                    ->orWhere('name', 'like', 'generation_units.%')
                    ->orWhere('name', 'like', 'operation_logs.%')
                    ->orWhere('name', 'like', 'fuel_efficiencies.%')
                    ->orWhere('name', 'like', 'maintenance_records.%')
                    ->orWhere('name', 'like', 'compliance_safeties.%')
                    ->orWhere('name', 'like', 'electricity_tariff_prices.%');
                    
                    // ملاحظة: welcome_messages.* و sms_templates.* غير موجودة هنا عمداً
                    // لأنها مخصصة فقط للـ Admin و SuperAdmin
            })
            ->pluck('id')
            ->toArray();

        $this->cachedTenantAssignablePermissionIds = $ids;

        return $ids;
    }

    /**
     * الصلاحيات الفعلية للمستخدم (Role + Direct - Revoked)
     */
    /**
     * الصلاحيات الحصرية لـ SuperAdmin فقط (مثل إعدادات الموقع)
     */
    private function superAdminOnlyPermissionIds(): array
    {
        return Permission::whereIn('name', [
            'settings.view',
            'settings.update',
            'constants.view',
            'constants.create',
            'constants.update',
            'constants.delete',
            'logs.view',
            'logs.clear',
            'logs.download',
            'welcome_messages.view',
            'welcome_messages.update',
        ])->pluck('id')->toArray();
    }

    private function effectivePermissionIdsFor(User $actor): array
    {
        // SuperAdmin فقط يرَى جميع الصلاحيات
        if ($actor->isSuperAdmin()) {
            return Permission::pluck('id')->toArray();
        }

        // EnergyAuthority: جميع الصلاحيات ما عدا الصلاحيات الحصرية لـ SuperAdmin
        if ($actor->isEnergyAuthority()) {
            $superAdminOnlyIds = $this->superAdminOnlyPermissionIds();
            return Permission::whereNotIn('id', $superAdminOnlyIds)->pluck('id')->toArray();
        }

        $actor->loadMissing(['permissions', 'revokedPermissions', 'roleModel.permissions']);

        $rolePermissionIds = $actor->roleModel?->permissions->pluck('id')->toArray() ?? [];
        $directPermissionIds = $actor->permissions->pluck('id')->toArray();
        $revokedPermissionIds = $actor->revokedPermissions->pluck('id')->toArray();

        $effective = array_values(array_unique(array_merge($rolePermissionIds, $directPermissionIds)));

        return array_values(array_diff($effective, $revokedPermissionIds));
    }

    /**
     * سقف المشغل: (Effective للمشغل) ∩ (TenantAssignable)
     * هذا هو "القفل الحديدي" اللي يمنع constants/users/roles/permissions… إلخ.
     */
    private function companyOwnerCeilingPermissionIds(User $actor): array
    {
        $effective = $this->effectivePermissionIdsFor($actor);
        $assignable = $this->tenantAssignablePermissionIds();

        return array_values(array_intersect($effective, $assignable));
    }

    private function assertActorCanOpenTree(User $actor): void
    {
        if (! $actor->isSuperAdmin() && ! $actor->isAdmin() && ! $actor->isEnergyAuthority() && ! $actor->isCompanyOwner()) {
            abort(403);
        }
    }

    private function assertActorCanManageTarget(User $actor, User $target): void
    {
        // SuperAdmin يمسك الكل
        if ($actor->isSuperAdmin()) {
            return;
        }

        // Admin: لا يعطي صلاحية لموظف تابع لمشغل
        if ($actor->isAdmin()) {
            if ($target->isSuperAdmin()) {
                abort(403, 'لا يمكنك تعديل صلاحيات مدير النظام.');
            }
            // لا يعطي صلاحية لموظف تابع لمشغل
            if ($target->isEmployee() || $target->isTechnician()) {
                abort(403, 'لا يمكنك إعطاء صلاحيات لموظف تابع لمشغل.');
            }
            return;
        }

        // EnergyAuthority: يمكنه إدارة جميع المستخدمين ما عدا SuperAdmin
        if ($actor->isEnergyAuthority()) {
            if ($target->isSuperAdmin()) {
                abort(403, 'لا يمكنك تعديل صلاحيات مدير النظام.');
            }
            return;
        }

        // CompanyOwner: فقط موظفين/فنيين تابعين لمشغله (لا يستطيع منح نفسه صلاحيات)
        if ($actor->isCompanyOwner()) {
            $operator = $actor->ownedOperators()->first();
            if (! $operator) {
                abort(403, 'لا يوجد مشغل مرتبط بك.');
            }

            // المشغل لا يستطيع منح نفسه صلاحيات
            if ($target->id === $actor->id) {
                abort(403, 'لا يمكنك منح نفسك صلاحيات.');
            }

            if (! $target->isEmployee() && ! $target->isTechnician()) {
                abort(403, 'يمكنك منح صلاحيات للموظفين والفنيين فقط.');
            }

            // لازم يكون تابع لنفس المشغل
            $isUnderSameOperator = $target->operators()
                ->where('operators.id', $operator->id)
                ->exists();

            if (! $isUnderSameOperator) {
                abort(403, 'لا يمكنك إدارة صلاحيات مستخدم خارج مشغلك.');
            }

            // ممنوع العبث بالسوبر أدمن
            if ($target->isSuperAdmin()) {
                abort(403, 'لا يمكنك تعديل صلاحيات مدير النظام.');
            }

            return;
        }

        abort(403);
    }

    public function index(Request $request): View
    {
        $authUser = auth()->user();
        
        // Log for debugging
        \Log::info('PermissionsController@index', [
            'user_id' => $authUser->id,
            'role' => $authUser->role?->value ?? null,
            'role_id' => $authUser->role_id,
            'isSuperAdmin' => $authUser->isSuperAdmin(),
            'isEnergyAuthority' => $authUser->isEnergyAuthority(),
            'isCompanyOwner' => $authUser->isCompanyOwner(),
        ]);
        
        $this->assertActorCanOpenTree($authUser);

        $search = trim((string) $request->input('search', ''));

        // ✅ لازم يتعرّف دائماً
        $operator = null;
        if ($authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
        }

        // Permissions query
        $query = Permission::query()->orderBy('group')->orderBy('order');

        // SuperAdmin: يرى جميع الصلاحيات (لا حاجة لفلترة)
        // EnergyAuthority: يرى جميع الصلاحيات ما عدا الصلاحيات الحصرية لـ SuperAdmin
        if ($authUser->isEnergyAuthority()) {
            $superAdminOnlyIds = $this->superAdminOnlyPermissionIds();
            $query->whereNotIn('id', $superAdminOnlyIds);
        }
        // CompanyOwner: فلترة بالسقف (TenantAssignable permissions)
        elseif ($authUser->isCompanyOwner()) {
            // المشغل المعتمد يرى جميع الصلاحيات المتاحة للتوزيع (TenantAssignable)
            // حتى لو لم يكن لديه هذه الصلاحيات في roleModel
            $assignableIds = $this->tenantAssignablePermissionIds();
            $query->whereIn('id', $assignableIds);
        }

        if ($search !== '') {
            // تنظيف المدخلات لمنع SQL Injection
            $search = $this->sanitizeSearchInput($search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('group_label', 'like', "%{$search}%");
                });
            }
        }

        $permissions = $query->get()->groupBy('group');

        // Users list (Legacy blade)
        $users = collect();
        $groupedUsers = [
            'company_owners' => collect(),
            'employees' => collect(),
            'technicians' => collect(),
        ];

        if ($authUser->isSuperAdmin()) {
            $users = User::query()
                ->where('id', '!=', $authUser->id)
                ->where('role', RoleEnum::CompanyOwner)
                ->latest()
                ->get();

            $groupedUsers['company_owners'] = $users;

        } elseif ($authUser->isCompanyOwner()) {

            if ($operator) {
                $users = User::query()
                    ->whereHas('operators', function ($q) use ($operator) {
                        $q->where('operators.id', $operator->id);
                    })
                    ->whereIn('role', [RoleEnum::Employee, RoleEnum::Technician])
                    ->orderBy('name')
                    ->get();

                $groupedUsers['employees'] = $users->filter(fn ($u) => $u->isEmployee())->values();
                $groupedUsers['technicians'] = $users->filter(fn ($u) => $u->isTechnician())->values();
            }
        }

        // (قد يكون مستخدم بالـ blade القديم)
        $roles = [
            'company_owner' => [
                'name' => 'مشغل',
                'color' => 'primary',
                'icon' => 'bi-building',
                'description' => 'مشغل (Tenant Admin)',
            ],
            'employee' => [
                'name' => 'موظف',
                'color' => 'success',
                'icon' => 'bi-person',
                'description' => 'تابع للمشغل',
            ],
            'technician' => [
                'name' => 'فني',
                'color' => 'warning',
                'icon' => 'bi-tools',
                'description' => 'تابع للمشغل',
            ],
        ];

        // ✅ هذا اللي ناقصك (للـ JS / badges)
        $rolesMeta = [
            'super_admin' => ['label' => 'مدير النظام', 'color' => 'danger', 'icon' => 'bi-shield-check'],
            'admin'       => ['label' => 'مدير', 'color' => 'info', 'icon' => 'bi-person-badge'],
            'energy_authority' => ['label' => 'سلطة الطاقة', 'color' => 'info', 'icon' => 'bi-bank2'],
            'company_owner' => ['label' => 'مشغل', 'color' => 'primary', 'icon' => 'bi-building'],
            'employee'      => ['label' => 'موظف', 'color' => 'success', 'icon' => 'bi-person'],
            'technician'    => ['label' => 'فني', 'color' => 'warning', 'icon' => 'bi-tools'],
        ];

        // جلب الأدوار النظامية للـ partials
        $systemRoles = Role::where('is_system', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'label']);

        return view('admin.permissions.index', compact(
            'permissions',
            'roles',
            'rolesMeta',     // ✅ مهم
            'users',
            'groupedUsers',
            'search',
            'operator',      // ✅ مهم
            'systemRoles'    // ✅ للأدوار النظامية
        ));
    }



    public function search(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        $search = trim((string) $request->input('search', ''));

        $query = Permission::query()->orderBy('group')->orderBy('order');

        // SuperAdmin: يرى جميع الصلاحيات (لا حاجة لفلترة)
        // EnergyAuthority: يرى جميع الصلاحيات ما عدا الصلاحيات الحصرية لـ SuperAdmin
        if ($authUser->isEnergyAuthority()) {
            $superAdminOnlyIds = $this->superAdminOnlyPermissionIds();
            $query->whereNotIn('id', $superAdminOnlyIds);
        }
        // CompanyOwner: فلترة بالسقف
        elseif ($authUser->isCompanyOwner()) {
            $ceilingIds = $this->companyOwnerCeilingPermissionIds($authUser);
            $query->whereIn('id', $ceilingIds);
        }

        if ($search !== '') {
            // تنظيف المدخلات لمنع SQL Injection
            $search = $this->sanitizeSearchInput($search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('group_label', 'like', "%{$search}%");
                });
            }
        }

        $permissions = $query->get()->groupBy('group');

        $html = view('admin.permissions.partials.permissions-tree', [
            'permissions' => $permissions,
            'search' => $search,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $permissions->flatten()->count(),
        ]);
    }

    public function getUserPermissions(User $user): JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);
        $this->assertActorCanManageTarget($authUser, $user);

        $user->load(['permissions', 'revokedPermissions', 'roleModel.permissions']);

        $direct = $user->permissions->pluck('id')->toArray();
        $role = $user->roleModel?->permissions->pluck('id')->toArray() ?? [];
        $revoked = $user->revokedPermissions->pluck('id')->toArray();

        // CompanyOwner: رجّع فقط اللي ضمن السقف
        if ($authUser->isCompanyOwner()) {
            $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

            $direct = array_values(array_intersect($direct, $ceiling));
            $role = array_values(array_intersect($role, $ceiling));
            $revoked = array_values(array_intersect($revoked, $ceiling));
        }

        // جلب operator_id للمستخدم (للسوبر أدمن)
        $operatorId = null;
        if ($authUser->isSuperAdmin()) {
            // جلب أول مشغل مرتبط بالمستخدم
            $operator = $user->operators()->first();
            if (!$operator && $user->isCompanyOwner()) {
                // إذا كان المستخدم هو owner، جلب المشغل من ownedOperators
                $operator = $user->ownedOperators()->first();
            }
            $operatorId = $operator?->id;
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role?->value ?? null,
                'role_id' => $user->role_id,
                'operator_id' => $operatorId,
                'direct_permissions' => $direct,
                'role_permissions' => $role,
                'revoked_permissions' => $revoked,
            ],
        ]);
    }

    public function assignPermissions(Request $request): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        try {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
                'revoked_permissions' => ['nullable', 'array'],
                'revoked_permissions.*' => ['integer', 'exists:permissions,id'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $target = User::with(['permissions', 'revokedPermissions', 'roleModel.permissions'])
            ->findOrFail((int) $request->input('user_id'));

        $this->assertActorCanManageTarget($authUser, $target);

        $newDirect = array_map('intval', (array) $request->input('permissions', []));
        $newRevoked = array_map('intval', (array) $request->input('revoked_permissions', []));

        // EnergyAuthority: منع منح الصلاحيات الحصرية لـ SuperAdmin
        if ($authUser->isEnergyAuthority()) {
            $superAdminOnlyIds = $this->superAdminOnlyPermissionIds();
            $invalidDirect = array_intersect($newDirect, $superAdminOnlyIds);
            $invalidRevoked = array_intersect($newRevoked, $superAdminOnlyIds);

            if (! empty($invalidDirect) || ! empty($invalidRevoked)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن منح الصلاحيات الحصرية لمدير النظام.',
                ], 403);
            }
        }

        // CompanyOwner: قفل السقف + Partial Sync (ما يلمس خارج السقف)
        if ($authUser->isCompanyOwner()) {
            $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

            $invalidDirect = array_diff($newDirect, $ceiling);
            $invalidRevoked = array_diff($newRevoked, $ceiling);

            if (! empty($invalidDirect) || ! empty($invalidRevoked)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم رفض الطلب: محاولة منح صلاحيات خارج صلاحيات المشغل المسموحة.',
                ], 403);
            }

            $keepDirectOutsideCeiling = array_diff($target->permissions->pluck('id')->toArray(), $ceiling);
            $keepRevokedOutsideCeiling = array_diff($target->revokedPermissions->pluck('id')->toArray(), $ceiling);

            $finalDirect = array_values(array_unique(array_merge($keepDirectOutsideCeiling, $newDirect)));
            $finalRevoked = array_values(array_unique(array_merge($keepRevokedOutsideCeiling, $newRevoked)));

            $newDirect = $finalDirect;
            $newRevoked = $finalRevoked;
        }

        // Audit: snapshot قبل
        $oldDirect = $target->permissions->pluck('id')->toArray();
        $oldRevoked = $target->revokedPermissions->pluck('id')->toArray();

        DB::transaction(function () use ($target, $newDirect, $newRevoked, $authUser, $oldDirect, $oldRevoked) {
            // Direct
            $target->permissions()->sync($newDirect);
            // Revoked (منع)
            $target->revokedPermissions()->sync($newRevoked);

            // Audit direct changes
            $grantedDirect = array_diff($newDirect, $oldDirect);
            $revokedDirect = array_diff($oldDirect, $newDirect);

            foreach ($grantedDirect as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'granted',
                    'notes' => "تم منح صلاحية مباشرة بواسطة {$authUser->name}",
                ]);
            }

            foreach ($revokedDirect as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'revoked',
                    'notes' => "تم إلغاء صلاحية مباشرة بواسطة {$authUser->name}",
                ]);
            }

            // Audit revoked-table changes (deny)
            $addedToRevoked = array_diff($newRevoked, $oldRevoked);
            $removedFromRevoked = array_diff($oldRevoked, $newRevoked);

            foreach ($addedToRevoked as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'revoked',
                    'notes' => "تم منع الصلاحية (Override) بواسطة {$authUser->name}",
                ]);
            }

            foreach ($removedFromRevoked as $pid) {
                PermissionAuditLog::create([
                    'user_id' => $target->id,
                    'performed_by' => $authUser->id,
                    'permission_id' => $pid,
                    'action' => 'granted',
                    'notes' => "تم رفع المنع عن الصلاحية (Override) بواسطة {$authUser->name}",
                ]);
            }
        });

        // Response
        if ($request->ajax()) {
            $target->refresh()->load(['permissions', 'revokedPermissions', 'roleModel.permissions']);

            $direct = $target->permissions->pluck('id')->toArray();
            $role = $target->roleModel?->permissions->pluck('id')->toArray() ?? [];
            $revoked = $target->revokedPermissions->pluck('id')->toArray();

            // CompanyOwner: رجّع بس السقف
            if ($authUser->isCompanyOwner()) {
                $ceiling = $this->companyOwnerCeilingPermissionIds($authUser);

                $direct = array_values(array_intersect($direct, $ceiling));
                $role = array_values(array_intersect($role, $ceiling));
                $revoked = array_values(array_intersect($revoked, $ceiling));
            }

            return response()->json([
                'success' => true,
                'message' => "تم تحديث صلاحيات المستخدم {$target->name} بنجاح.",
                'user' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'direct_permissions' => $direct,
                    'role_permissions' => $role,
                    'revoked_permissions' => $revoked,
                ],
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "تم تحديث صلاحيات المستخدم {$target->name} بنجاح.");
    }

    /**
     * Select2: Operators (SuperAdmin/Admin) + CompanyOwner (يرجع مشغله فقط)
     */
    public function select2Operators(Request $request): JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isAdmin() && ! $authUser->isEnergyAuthority() && ! $authUser->isCompanyOwner()) {
            abort(403);
        }

        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $operatorId = (int) $request->input('operator_id', 0);

        $query = Operator::query()->select(['id', 'name']);

        if ($authUser->isCompanyOwner()) {
            $query->where('owner_id', $authUser->id);
        }

        // إذا تم تحديد operator_id، أضفه مباشرة
        if ($operatorId > 0) {
            $query->where('id', $operatorId);
        } elseif ($term !== '') {
            $query->where('name', 'like', "%{$term}%");
        }

        $p = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        $results = $p->getCollection()->map(function ($op) {
            return ['id' => $op->id, 'text' => $op->name];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }

    /**
     * Select2: Users by role
     * - SuperAdmin: البحث بالدور النظامي - يعرض جميع المستخدمين في النظام
     * - CompanyOwner: البحث بالدور (مشغل أو دور مخصص) - يعرض الموظفين/الفنيين التابعين للمشغل
     */
    public function select2Users(Request $request): JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isAdmin() && ! $authUser->isEnergyAuthority() && ! $authUser->isCompanyOwner()) {
            abort(403);
        }

        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $role = trim((string) $request->input('role', '')); // role name (enum) or role_id (custom role)
        $roleId = (int) $request->input('role_id', 0); // for custom roles
        $operatorId = (int) $request->input('operator_id', 0); // for operator filtering

        $query = User::query()
            ->select(['id', 'name', 'username', 'email', 'role', 'role_id'])
            ->where('id', '!=', $authUser->id)
            ->where('username', '!=', 'platform_rased'); // Exclude system user

        if ($authUser->isSuperAdmin()) {
            // SuperAdmin: البحث بالدور النظامي
            if ($role !== '') {
                // Check if it's a system role (enum)
                $allowedSystemRoles = array_map(fn (RoleEnum $r) => $r->value, RoleEnum::cases());
                if (in_array($role, $allowedSystemRoles, true)) {
                    $query->where('role', $role);
                    
                    // إذا اختار company_owner وتم تحديد operator_id، يظهر المشغل فقط (بدون موظفين)
                    if ($role === 'company_owner' && $operatorId > 0) {
                        $query->whereHas('ownedOperators', function ($q) use ($operatorId) {
                            $q->where('operators.id', $operatorId);
                        });
                        // استثناء الموظفين والفنيين التابعين للمشغل
                        $query->whereNotIn('role', [RoleEnum::Employee, RoleEnum::Technician]);
                    }
                } elseif ($roleId > 0) {
                    // Custom role
                    $query->where('role_id', $roleId);
                }
            }
            // إذا لم يتم تحديد دور، يعرض جميع المستخدمين

        } elseif ($authUser->isAdmin()) {
            // Admin: البحث بالدور النظامي (ما عدا SuperAdmin)
            $query->where('role', '!=', RoleEnum::SuperAdmin->value);
            
            // لا يعطي صلاحية لموظف تابع لمشغل
            $query->whereNotIn('role', [RoleEnum::Employee, RoleEnum::Technician]);
            
            if ($role !== '') {
                // Check if it's a system role (enum)
                $allowedSystemRoles = array_map(fn (RoleEnum $r) => $r->value, RoleEnum::cases());
                if (in_array($role, $allowedSystemRoles, true) && $role !== 'super_admin') {
                    $query->where('role', $role);
                } elseif ($roleId > 0) {
                    // Custom role
                    $query->where('role_id', $roleId);
                }
            }

        } elseif ($authUser->isEnergyAuthority()) {
            // EnergyAuthority: البحث بالدور النظامي (ما عدا SuperAdmin)
            $query->where('role', '!=', RoleEnum::SuperAdmin->value);
            
            if ($role !== '') {
                // Check if it's a system role (enum)
                $allowedSystemRoles = array_map(fn (RoleEnum $r) => $r->value, RoleEnum::cases());
                if (in_array($role, $allowedSystemRoles, true) && $role !== 'super_admin') {
                    $query->where('role', $role);
                    
                    // إذا اختار company_owner وتم تحديد operator_id، يظهر المشغل والمستخدمين تحت المشغل
                    if ($role === 'company_owner' && $operatorId > 0) {
                        $query->where(function ($q) use ($operatorId) {
                            // المشغل (owner)
                            $q->whereHas('ownedOperators', function ($qq) use ($operatorId) {
                                $qq->where('operators.id', $operatorId);
                            })
                            // أو الموظفين/الفنيين التابعين للمشغل
                            ->orWhereHas('operators', function ($qq) use ($operatorId) {
                                $qq->where('operators.id', $operatorId);
                            });
                        });
                    }
                } elseif ($roleId > 0) {
                    // Custom role
                    $query->where('role_id', $roleId);
                }
            }
            // إذا لم يتم تحديد دور، يعرض جميع المستخدمين

        } elseif ($authUser->isCompanyOwner()) {
            // CompanyOwner: البحث بالدور (مشغل أو دور مخصص) - يعرض الموظفين/الفنيين التابعين للمشغل فقط
            $operator = $authUser->ownedOperators()->first();
            if (! $operator) {
                return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }

            // استثناء المشغل نفسه (لا يستطيع منح نفسه صلاحيات)
            $query->where('id', '!=', $authUser->id);

            // Filter by operator - فقط الموظفين/الفنيين التابعين للمشغل
            $query->whereHas('operators', function ($qq) use ($operator) {
                $qq->where('operators.id', $operator->id);
            });

            if ($role === 'company_owner') {
                // المشغل: لا يعرض المشغل نفسه، فقط الموظفين/الفنيين
                // لكن في الواقع، المشغل لا يستطيع منح نفسه صلاحيات
                // لذلك نعرض فقط الموظفين/الفنيين التابعين للمشغل
                $query->whereIn('role', [RoleEnum::Employee, RoleEnum::Technician]);
            } elseif ($roleId > 0) {
                // دور مخصص
                $customRole = Role::find($roleId);
                if ($customRole && $customRole->operator_id === $operator->id && $customRole->created_by === $authUser->id) {
                    $query->where('role_id', $roleId);
                } else {
                    return response()->json(['results' => [], 'pagination' => ['more' => false]]);
                }
            } else {
                // Default: فقط موظفين/فنيين (بدون المشغل نفسه)
                $query->whereIn('role', [RoleEnum::Employee, RoleEnum::Technician]);
            }
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $p = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        $results = $p->getCollection()->map(function ($u) {
            $extra = $u->username ? " ({$u->username})" : '';
            return [
                'id' => $u->id,
                'text' => $u->name . $extra,
                'role' => $u->role?->value ?? null,
                'role_id' => $u->role_id,
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }

    /**
     * Select2: Roles
     * - SuperAdmin: الأدوار النظامية فقط (من enum)
     */
    public function select2Roles(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        
        if (!$authUser->isSuperAdmin() && !$authUser->isEnergyAuthority()) {
            abort(403);
        }

        $term = trim((string) $request->input('q', ''));

        // SuperAdmin و EnergyAuthority: عرض الأدوار النظامية فقط (من enum)
        $systemRoles = Role::where('is_system', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'label']);
        
        // EnergyAuthority: لا يرى SuperAdmin role
        if ($authUser->isEnergyAuthority()) {
            $systemRoles = $systemRoles->filter(fn($role) => $role->name !== 'super_admin');
        }

        $results = $systemRoles->map(function ($role) {
            return [
                'id' => $role->name, // Use role name (enum value) as ID
                'text' => $role->label ?: $role->name,
            ];
        })->values();

        // Filter by term if provided
        if ($term !== '') {
            $results = $results->filter(function ($role) use ($term) {
                return stripos($role['text'], $term) !== false || stripos($role['id'], $term) !== false;
            })->values();
        }

        return response()->json([
            'results' => $results->toArray(),
            'pagination' => ['more' => false], // System roles are limited, no pagination needed
        ]);
    }

    /**
     * Select2: Custom Roles for Operator
     */
    public function select2CustomRoles(Operator $operator): JsonResponse
    {
        $authUser = auth()->user();
        
        if (!$authUser->isSuperAdmin() && !$authUser->isEnergyAuthority()) {
            abort(403);
        }

        // Get custom roles for this operator
        $customRoles = Role::getCustomRolesForOperator($operator->id);

        $results = $customRoles->map(function ($role) {
            return [
                'id' => $role->id,
                'text' => $role->label ?: $role->name,
            ];
        })->values();

        return response()->json([
            'results' => $results->toArray(),
            'pagination' => ['more' => false],
        ]);
    }

    /**
     * Get permissions for a role
     */
    public function getRolePermissions(Role $role): JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        // Check if user can view this role
        if (!$authUser->can('view', $role)) {
            abort(403);
        }

        $role->load('permissions');

        $permissions = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $role->label,
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * Assign permissions to a role
     */
    public function assignRolePermissions(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();
        $this->assertActorCanOpenTree($authUser);

        // Check if user can update this role
        if (!$authUser->can('update', $role)) {
            abort(403);
        }

        try {
            $request->validate([
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['integer', 'exists:permissions,id'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $permissionIds = array_map('intval', (array) $request->input('permissions', []));

        // Filter system permissions if user is not SuperAdmin
        if ($authUser->isAdmin() || $authUser->isCompanyOwner()) {
            $systemPermissions = Permission::whereIn('name', [
                'users.*', 'operators.*', 'permissions.*', 'settings.*', 'constants.*', 'logs.*'
            ])->pluck('id')->toArray();
            $permissionIds = array_diff($permissionIds, $systemPermissions);
        }

        // Update role permissions
        $role->permissions()->sync($permissionIds);

        $role->refresh()->load('permissions');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "تم تحديث صلاحيات الدور {$role->label} بنجاح.",
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                    'permissions' => $role->permissions->pluck('id')->toArray(),
                ],
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "تم تحديث صلاحيات الدور {$role->label} بنجاح.");
    }
}
