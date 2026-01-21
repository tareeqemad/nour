<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Notification;
use App\Models\Operator;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================
 * UserController - User Management Controller
 * ============================================
 *
 * This controller is responsible for managing all users in the system.
 *
 * Note: Roles are not fixed. Energy Authority and Company Owners can define custom roles
 * and assign permissions as needed. The system supports both system roles (super_admin,
 * admin, energy_authority, company_owner) and custom roles defined by operators or energy authority.
 *
 * Main Roles:
 * ------------------
 * 1. Super Admin (SuperAdmin):
 *    - Can create: SuperAdmin, Admin, CompanyOwner, and custom roles
 *    - Has full control over all users
 *
 * 2. Energy Authority (EnergyAuthority) - Main role in the system:
 *    - Can create: Admin, EnergyAuthority, CompanyOwner, and custom roles
 *    - Can define custom roles with specific permissions
 *    - Can add operators through authorized phone numbers
 *    - Has access to roles and permissions definition
 *    - Can define users under their authority
 *    - Has control over users and operators
 *
 * 3. Company Owner (CompanyOwner):
 *    - Can create: Users with custom roles defined by Energy Authority or their own custom roles
 *    - Can manage their own users
 *
 * SMS Notification:
 * ------------------
 * When a user is created, if a phone number is provided, SMS is automatically sent with:
 * - Welcome message
 * - Role name (from database - supports custom roles)
 * - Username
 * - Password
 * - Login link
 *
 * ============================================
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();

        // ✅ New UI expects JSON on ajax=1 / wantsJson
        if ($request->wantsJson() || $request->boolean('ajax')) {
            return $this->ajaxIndex($request, $actor);
        }

        // ✅ Normal page load - Get available roles for filter
        $availableRoles = $this->getAvailableRolesForFilter($actor);

        // Get available roles for create modal
        $rolesForCreate = $this->getRolesForCreate($actor);
        $operators = collect();
        $operatorLocked = null;

        if ($actor->isCompanyOwner()) {
            $operatorLocked = $actor->ownedOperators()->first();
        } elseif ($actor->isSuperAdmin() || $actor->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        }

        return view('admin.users.index', compact('availableRoles', 'rolesForCreate', 'operators', 'operatorLocked'));
    }

    /**
     * Get available roles for filter (system roles + custom roles based on user permissions)
     */
    private function getAvailableRolesForFilter(User $user): array
    {
        $roles = [];

        // Get system roles dynamically from database (is_system = true)
        $systemRoles = \App\Models\Role::where('is_system', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // Add system roles based on user permissions
        if ($user->isSuperAdmin()) {
            // Super Admin can see all system roles
            foreach ($systemRoles as $systemRole) {
                $roles[$systemRole->name] = [
                    'label' => $systemRole->label,
                    'badge' => $this->getRoleBadge($systemRole->name),
                    'is_custom' => false,
                ];
            }
        } elseif ($user->isEnergyAuthority() || $user->isAdmin()) {
            // Energy Authority and Admin can see system roles except super_admin
            foreach ($systemRoles as $systemRole) {
                if ($systemRole->name !== 'super_admin') {
                    $roles[$systemRole->name] = [
                        'label' => $systemRole->label,
                        'badge' => $this->getRoleBadge($systemRole->name),
                        'is_custom' => false,
                    ];
                }
            }
        }
        // Company Owner doesn't see system roles in filter (only custom roles)

        // Add custom roles based on user permissions
        $customRoles = \App\Models\Role::getAvailableCustomRoles($user);

        foreach ($customRoles as $customRole) {
            $roles[$customRole->name] = [
                'label' => $customRole->label,
                'badge' => 'badge-role-custom',
                'is_custom' => true,
            ];
        }

        return $roles;
    }

    /**
     * Get available roles for create modal (system roles + custom roles based on user authority)
     * 
     * Rules:
     * - SuperAdmin: يرى الأدوار النظامية فقط (super_admin, admin, energy_authority, company_owner, employee, technician, civil_defense)
     * - Admin: يرى جميع الأدوار النظامية ما عدا SuperAdmin (admin, energy_authority, company_owner, employee, technician, civil_defense)
     * - EnergyAuthority: يرى الأدوار النظامية ما عدا SuperAdmin و Admin (energy_authority, company_owner, employee, technician, civil_defense)
     * - CompanyOwner: يرى فقط الأدوار المخصصة التابعة لمشغله
     */
    private function getRolesForCreate(User $user): array
    {
        $roles = [];
        $customRoles = collect();

        // Get system roles from database (is_system = true)
        $systemRoles = \App\Models\Role::where('is_system', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if ($user->isCompanyOwner()) {
            // المشغل: يرى فقط الأدوار المخصصة التي أنشأها
            // لا يرى أي أدوار نظامية (Employee, Technician, إلخ)
            $customRoles = \App\Models\Role::getAvailableCustomRoles($user);
            // لا أدوار نظامية للمشغل - فقط الأدوار المخصصة
            $systemRoles = collect();
        } elseif ($user->isSuperAdmin()) {
            // السوبر أدمن: يرى الأدوار النظامية فقط (بدون الأدوار المخصصة)
            // جميع الأدوار النظامية: super_admin, admin, energy_authority, company_owner, employee, technician, civil_defense
            $customRoles = collect(); // لا أدوار مخصصة للسوبر أدمن
        } elseif ($user->isAdmin()) {
            // الأدمن: يرى جميع الأدوار النظامية ما عدا SuperAdmin
            // admin, energy_authority, company_owner, employee, technician, civil_defense
            $systemRoles = $systemRoles->filter(fn($role) => $role->name !== 'super_admin');
            $customRoles = collect(); // Admin لا يرى الأدوار المخصصة في مودال الإنشاء
        } elseif ($user->isEnergyAuthority()) {
            // سلطة الطاقة: يرى الأدوار النظامية ما عدا SuperAdmin و Admin
            // energy_authority, company_owner, employee, technician, civil_defense
            $systemRoles = $systemRoles->filter(fn($role) => !in_array($role->name, ['super_admin', 'admin'], true));
            $customRoles = collect(); // EnergyAuthority لا يرى الأدوار المخصصة في مودال الإنشاء
        }

        // Add system roles to array
        foreach ($systemRoles as $systemRole) {
            $roles[] = [
                'value' => $systemRole->name,
                'label' => $systemRole->label,
                'is_custom' => false,
            ];
        }

        // Add custom roles to array
        foreach ($customRoles as $customRole) {
            $roles[] = [
                'value' => $customRole->name,
                'label' => $customRole->label,
                'is_custom' => true,
            ];
        }

        return $roles;
    }

    /**
     * Get badge class for role
     */
    private function getRoleBadge(string $roleName): string
    {
        return match ($roleName) {
            'super_admin' => 'badge-role-sa',
            'admin' => 'badge-role-admin',
            'energy_authority' => 'badge-role-admin',
            'company_owner' => 'badge-role-owner',
            default => 'badge-role-custom',
        };
    }

    private function ajaxIndex(Request $request, User $actor): JsonResponse
    {
        $name = trim((string) $request->query('name', ''));
        $username = trim((string) $request->query('username', ''));
        $email = trim((string) $request->query('email', ''));
        $role = trim((string) $request->query('role', ''));
        $operatorId = (int) $request->query('operator_id', 0);

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(50, $perPage));

        // Validate role: allow system roles (enum) and custom roles (from roles table)
        // We'll validate custom roles later in the query if needed
        $allowedSystemRoles = array_map(fn (\App\Enums\Role $r) => $r->value, \App\Enums\Role::cases());
        if ($role !== '' && ! in_array($role, $allowedSystemRoles, true)) {
            // Check if it's a valid custom role name (from roles table)
            $customRoleExists = \App\Models\Role::where('name', $role)
                ->where('is_system', false)
                ->exists();

            if (! $customRoleExists) {
                $role = ''; // Invalid role, reset to empty
            }
        }

        // -----------------------------
        // Base scope (filter by user permissions)
        // -----------------------------
        $base = User::query()
            ->where('username', '!=', 'platform_rased'); // Exclude system user (منصة نور) from lists

        if ($actor->isCompanyOwner()) {
            $operator = $actor->ownedOperators()->select('id')->first();

            if (! $operator) {
                return response()->json([
                    'ok' => true,
                    'data' => [],
                    'meta' => ['current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0, 'total' => 0],
                    'stats' => ['total' => 0, 'company_owners' => 0, 'admins' => 0, 'custom_roles' => 0],
                    'message' => 'لا يوجد Operator مرتبط بهذا المشغل.',
                ]);
            }

            // Company Owner can see:
            // 1. Himself (the operator owner)
            // 2. Employees and Technicians linked to his operator
            // 3. Users with custom roles linked to his operator
            $customRoleIds = \App\Models\Role::getCustomRolesForOperator($operator->id)->pluck('id')->toArray();

            $base->where(function (Builder $q) use ($operator, $customRoleIds) {
                // The operator owner (himself)
                $q->where('id', $operator->owner_id)
                    // Employees linked to this operator
                    ->orWhere(function (Builder $sub) use ($operator) {
                        $sub->where('role', \App\Enums\Role::Employee->value)
                            ->whereHas('operators', function (Builder $op) use ($operator) {
                                $op->where('operators.id', $operator->id);
                            });
                    })
                    // Technicians linked to this operator
                    ->orWhere(function (Builder $sub) use ($operator) {
                        $sub->where('role', \App\Enums\Role::Technician->value)
                            ->whereHas('operators', function (Builder $op) use ($operator) {
                                $op->where('operators.id', $operator->id);
                            });
                    });

                // Users with custom roles linked to this operator (if any)
                if (!empty($customRoleIds)) {
                    $q->orWhere(function (Builder $sub) use ($operator, $customRoleIds) {
                        $sub->whereIn('role_id', $customRoleIds)
                            ->whereHas('operators', function (Builder $op) use ($operator) {
                                $op->where('operators.id', $operator->id);
                            });
                    });
                }
            });

            // Ignore operator filter from UI (Company Owner only sees their own operator)
            $operatorId = 0;

            // Company Owner can only filter by custom roles (not system roles)
            // Check if role is a system role from database
            $isSystemRole = \App\Models\Role::where('name', $role)
                ->where('is_system', true)
                ->exists();
            if ($isSystemRole) {
                $role = '';
            }
        } elseif (! ($actor->isSuperAdmin() || $actor->isEnergyAuthority())) {
            return response()->json(['ok' => false, 'message' => 'غير مصرح.'], 403);
        }

        // Energy Authority cannot see SuperAdmin users
        if ($actor->isEnergyAuthority()) {
            $base->where(function (Builder $q) {
                // Exclude users with super_admin role (enum)
                $q->where('role', '!=', \App\Enums\Role::SuperAdmin->value)
                  // Exclude users with super_admin role (from roles table)
                  ->whereDoesntHave('roleModel', function ($subQ) {
                      $subQ->where('name', 'super_admin');
                  });
            });
        }

        // -----------------------------
        // Search
        // -----------------------------
        if ($name !== '' || $username !== '' || $email !== '') {
            // تنظيف المدخلات لمنع SQL Injection
            $name = $this->sanitizeSearchInput($name);
            $username = $this->sanitizeSearchInput($username);
            $email = $this->sanitizeSearchInput($email);
            
            $base->where(function (Builder $qb) use ($name, $username, $email) {
                if ($name !== '') {
                    $qb->where('users.name', 'like', "%{$name}%");
                }
                if ($username !== '') {
                    $qb->where('users.username', 'like', "%{$username}%");
                }
                if ($email !== '') {
                    $qb->where('users.email', 'like', "%{$email}%");
                }
            });
        }

        // -----------------------------
        // Operator filter (for SuperAdmin/EnergyAuthority only)
        // -----------------------------
        // If a specific operator is selected: show operator + their users (with custom roles)
        // Note: When operator is selected, we ignore role filter to show operator + all their users
        if (($actor->isSuperAdmin() || $actor->isEnergyAuthority()) && $operatorId > 0) {
            $operator = Operator::find($operatorId);
            if ($operator) {
                // The operator (parent/owner)
                $operatorOwnerId = $operator->owner_id;

                // Get custom roles linked to this operator
                $customRoleIds = \App\Models\Role::getCustomRolesForOperator($operatorId)->pluck('id')->toArray();

                // Build query to include: operator + users with custom roles linked to this operator
                $base->where(function (Builder $q) use ($operatorId, $operatorOwnerId, $customRoleIds) {
                    // The operator owner
                    $q->where('id', $operatorOwnerId);

                    // Users with custom roles linked to this operator
                    if (! empty($customRoleIds)) {
                        $q->orWhere(function (Builder $sub) use ($operatorId, $customRoleIds) {
                            $sub->whereIn('role_id', $customRoleIds)
                                ->whereHas('operators', fn (Builder $op) => $op->where('operators.id', $operatorId));
                        });
                    }
                });

                // Ignore role filter when operator is selected
                $role = '';
            }
        }

        // -----------------------------
        // Stats (without role filter)
        // -----------------------------
        $counts = (clone $base)->toBase()
            ->select('role', DB::raw('COUNT(*) as c'))
            ->groupBy('role')
            ->pluck('c', 'role')
            ->all();

        // Count custom roles (non-system roles)
        $customRolesCount = (clone $base)->whereHas('roleModel', function ($q) {
            $q->where('is_system', false);
        })->count();

        $stats = [
            'total' => array_sum($counts),
            'company_owners' => (int) ($counts[\App\Enums\Role::CompanyOwner->value] ?? 0),
            'admins' => (int) ($counts[\App\Enums\Role::Admin->value] ?? 0) + (int) ($counts[\App\Enums\Role::SuperAdmin->value] ?? 0),
            'custom_roles' => $customRolesCount,
        ];

        // -----------------------------
        // List query (with eager loading)
        // -----------------------------
        $list = (clone $base)->with([
            'operators:id,name',
            'roleModel:id,name,label',
            'permissions:id,name,label',
            'revokedPermissions:id,name',
            'roleModel.permissions:id,name',
            'ownedOperators' => function ($q) {
                $q->select('id', 'owner_id', 'name')
                    ->withCount([
                        // Count all employees (Employee, Technician, and custom roles) linked to this operator
                        'users as employees_count' => function ($uq) {
                            $uq->where(function ($sub) {
                                $sub->where('role', \App\Enums\Role::Employee->value)
                                    ->orWhere('role', \App\Enums\Role::Technician->value)
                                    ->orWhereHas('roleModel', function ($roleQ) {
                                        $roleQ->where('is_system', false);
                                    });
                            });
                        },
                    ]);
            },
        ]);

        // Apply role filter only if operator is not selected
        // (because selecting operator means showing operator + all their users regardless of role)
        if ($role !== '' && $operatorId === 0) {
            // Support both enum roles and custom roles (role_id)
            // Check if it's a system role (from database)
            $isSystemRole = \App\Models\Role::where('name', $role)
                ->where('is_system', true)
                ->exists();

            if ($isSystemRole) {
                // System role: search in enum field
                $list->where('role', $role);
            } else {
                // Custom role: search in roleModel.name
                $list->whereHas('roleModel', function (Builder $q) use ($role) {
                    $q->where('name', $role);
                });
            }
        }

        $p = $list->orderByDesc('created_at')->paginate($perPage);

        $data = $p->getCollection()->map(function (User $u) use ($actor) {
            $operatorName = null;
            $employeesCount = null;

            if ($u->isCompanyOwner()) {
                $op = $u->ownedOperators->first();
                $operatorName = $op?->name;
                $employeesCount = $op ? (int) ($op->employees_count ?? 0) : 0;
            }

            // For users with custom roles: get operator from roleModel or operators relationship
            if ($u->hasCustomRole() && ! $operatorName) {
                if ($u->roleModel && $u->roleModel->operator_id) {
                    $operatorName = $u->roleModel->operator?->name;
                } else {
                    $operatorName = $u->operators->first()?->name;
                }
            }

            // Get operator_id
            $operatorId = null;
            if ($u->isCompanyOwner()) {
                $op = $u->ownedOperators->first();
                $operatorId = $op?->id;
            } elseif ($u->hasCustomRole()) {
                // User with custom role: get operator from roleModel or operators relationship
                if ($u->roleModel && $u->roleModel->operator_id) {
                    $operatorId = $u->roleModel->operator_id;
                } else {
                    $operatorId = $u->operators->first()?->id;
                }
            }

            // حساب عدد الصلاحيات
            $permissionsCount = 0;
            $permissionsInfo = [];

            if ($u->isSuperAdmin()) {
                // السوبر أدمن لديه جميع الصلاحيات
                $permissionsCount = 'الكل';
                $permissionsInfo = ['type' => 'all', 'count' => 'الكل'];
            } else {
                // حساب الصلاحيات من الدور + الصلاحيات المباشرة - الصلاحيات الملغاة
                $rolePermissions = $u->roleModel?->permissions ?? collect();
                $directPermissions = $u->permissions ?? collect();
                $revokedPermissions = $u->revokedPermissions ?? collect();

                // دمج صلاحيات الدور والصلاحيات المباشرة
                $allPermissions = $rolePermissions->merge($directPermissions)->unique('id');

                // إزالة الصلاحيات الملغاة
                $finalPermissions = $allPermissions->reject(function ($perm) use ($revokedPermissions) {
                    return $revokedPermissions->contains('id', $perm->id);
                });

                $permissionsCount = $finalPermissions->count();
                $permissionsInfo = [
                    'type' => 'custom',
                    'count' => $permissionsCount,
                    'role_count' => $rolePermissions->count(),
                    'direct_count' => $directPermissions->count(),
                    'revoked_count' => $revokedPermissions->count(),
                ];
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'phone' => $u->phone ?? null,
                'role' => $u->role?->value ?? (string) $u->getRawOriginal('role'),
                'status' => $u->status ?? 'active',
                'operator' => $operatorName,
                'operator_id' => $operatorId,
                'employees_count' => $employeesCount,
                'permissions_count' => $permissionsCount,
                'permissions_info' => $permissionsInfo,
                'created_at' => optional($u->created_at)->format('Y-m-d'),
                'can_edit' => $actor->can('update', $u),
                'can_delete' => $actor->can('delete', $u),
                'urls' => [
                    'show' => route('admin.users.show', $u),
                    'edit' => route('admin.users.edit', $u),
                    'permissions' => route('admin.permissions.index', ['user_id' => $u->id]),
                ],
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'from' => $p->firstItem() ?? 0,
                'to' => $p->lastItem() ?? 0,
                'total' => $p->total(),
            ],
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        // تم إلغاء هذه الصفحة - إرجاع 404
        abort(404, 'الصفحة غير موجودة');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        $authUser = auth()->user();

        // Get available roles based on user authority
        // Company Owner can ONLY see and use custom roles they created (no system roles at all)
        $roles = collect(Role::cases());
        $customRoles = collect();

        if ($authUser->isCompanyOwner()) {
            // Company Owner can use ONLY custom roles they created
            // Cannot use any system roles (Employee, Technician, Admin, etc.)
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
            // لا أدوار نظامية للمشغل - فقط الأدوار المخصصة
            $roles = collect();
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            // SuperAdmin and Energy Authority can see all system roles and custom roles
            $customRoles = \App\Models\Role::getAvailableCustomRoles($authUser);
        }

        $operatorLocked = null;
        $operators = collect();
        if ($authUser->isCompanyOwner()) {
            $operatorLocked = $authUser->ownedOperators()->first();
        } elseif ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        }

        $userOperators = $user->operators->pluck('id')->toArray();
        $selectedOperator = $user->operators->first();

        if ($request->ajax() || $request->boolean('modal')) {
            return view('admin.users.partials.modal-form', [
                'mode' => 'edit',
                'user' => $user,
                'roles' => $roles,
                'defaultRole' => '',
                'operatorLocked' => $operatorLocked,
                'operators' => $operators,
                'userOperators' => $userOperators,
                'selectedOperator' => $selectedOperator,
            ]);
        }

        return view('admin.users.edit', compact('user', 'roles', 'operators', 'operatorLocked', 'userOperators'));
    }

    public function show(User $user): View
    {
        // Prevent viewing system user (منصة نور)
        if ($user->isSystemUser()) {
            abort(404, 'User not found');
        }

        $this->authorize('view', $user);

        // Load required relationships
        $user->load([
            'ownedOperators',
            'operators',
            'permissions',
            'roleModel',
        ]);

        // تحميل بيانات المشغل إذا كان المستخدم مشغل أو إذا كان السوبر أدمن يشوف ملف مشغل
        $operator = null;
        $authUser = auth()->user();

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->with([
                'cityDetail',
                'generationUnits' => function ($q) {
                    $q->withCount('generators');
                },
            ])->first();
        } elseif (($authUser->isSuperAdmin() || $authUser->isEnergyAuthority()) && $user->isCompanyOwner()) {
            // السوبر أدمن يمكنه رؤية ملف المشغل من صفحة المستخدم
            $operator = $user->ownedOperators()->with([
                'cityDetail',
                'generationUnits' => function ($q) {
                    $q->withCount('generators');
                },
            ])->first();
        }

        return view('admin.users.show', compact('user', 'operator'));
    }

    /**
     * Create a new user in the system
     *
     * ============================================
     * User Creation Policy by Role:
     * ============================================
     *
     * Note: Roles are not fixed. Energy Authority and Company Owners can define custom roles
     * and assign permissions as needed. The system supports both system roles (super_admin,
     * admin, energy_authority, company_owner) and custom roles defined by operators or energy authority.
     *
     * 1. Super Admin (SuperAdmin):
     *    - Can create: SuperAdmin, Admin, CompanyOwner, and users with custom roles (defined by Energy Authority)
     *    - When creating SuperAdmin, Admin, or CompanyOwner:
     *      * Input: Name, Name (English), Phone, Email, Role
     *      * Username auto-generated: sp_ + first_char + last_name (for SuperAdmin)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *
     * 2. Energy Authority (EnergyAuthority):
     *    - Can create: Admin, EnergyAuthority, CompanyOwner, and custom roles
     *    - Can define custom roles with specific permissions
     *    - When creating Admin or CompanyOwner:
     *      * Input: Name, Name (English), Phone, Email, Role
     *      * Username auto-generated: ad_ + first_char + last_name (for Admin)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *    - When creating user for a specific operator (custom role):
     *      * Username auto-generated: operator_username_user_name (to show operator affiliation)
     *      * Example: co_ababa_ahmad_mohammed (clear that user belongs to operator co_ababa)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *    - Can add operators through authorized phone numbers
     *    - Has full control over roles and permissions definition
     *
     * 3. Company Owner (CompanyOwner):
     *    - Can create: Users with custom roles defined by Energy Authority or their own custom roles
     *    - When creating user for their operator:
     *      * Input: Name only (phone optional)
     *      * Username auto-generated: operator_username_user_name
     *      *   Example: co_ababa_ahmad_mohammed (clear that user belongs to operator co_ababa)
     *      * Password auto-generated (8 random characters)
     *      * SMS sent automatically with credentials and login link (if phone provided)
     *
     * ============================================
     */
    public function store(StoreUserRequest $request)
    {
        $authUser = auth()->user();
        $roleValue = $request->validated('role');
        $operator = null; // Initialize operator variable

        // ============================================
        // Step 1: Determine if role is custom role or system role
        // ============================================
        // Check if role value is a valid enum value
        $allowedSystemRoles = array_map(fn (Role $r) => $r->value, Role::cases());
        $isSystemRoleValue = in_array($roleValue, $allowedSystemRoles, true);
        
        // Try to convert to enum only if it's a system role
        $role = null;
        if ($isSystemRoleValue) {
            $role = Role::from($roleValue);
        }
        
        // Determine role type
        $adminSystemRoles = [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value];
        $isAdminSystemRole = $isSystemRoleValue && in_array($roleValue, $adminSystemRoles, true);
        $isEmployeeOrTechnician = $isSystemRoleValue && in_array($roleValue, [Role::Employee->value, Role::Technician->value], true);
        $isSystemRole = $isSystemRoleValue && ($isAdminSystemRole || $isEmployeeOrTechnician);
        $isCustomRole = ! $isSystemRoleValue; // If not a system role enum value, it's a custom role

        // Check permissions: Company Owner can create Employee, Technician, or custom roles they created
        // Company Owner cannot create admin roles (SuperAdmin, Admin, EnergyAuthority, CompanyOwner)
        if ($authUser->isCompanyOwner() && $isAdminSystemRole) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك إنشاء مستخدمين بأدوار إدارية. يمكنك فقط إضافة موظفين وفنيين.');
        }

        // ============================================
        // Step 2: Get role_id from roles table (for system roles) or from request (for custom roles)
        // ============================================
        $roleModel = null;
        if ($isSystemRole) {
            $roleModel = \App\Models\Role::findByName($role->value);
        } else {
            // Custom role: try to get role_id from request first, otherwise find by name
            $roleId = (int) $request->input('role_id');
            if ($roleId) {
                $roleModel = \App\Models\Role::find($roleId);
            } else {
                // Try to find custom role by name (from role select value)
                $roleModel = \App\Models\Role::where('name', $roleValue)
                    ->where('is_system', false)
                    ->first();
            }
            
            if (! $roleModel) {
                return $this->jsonOrRedirect($request, false, 'الدور المحدد غير موجود أو غير صالح.');
            }
            
            if ($roleModel->is_system) {
                return $this->jsonOrRedirect($request, false, 'الدور المحدد ليس دوراً مخصصاً.');
            }

            // Check if Company Owner is trying to use a role that doesn't belong to them
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if ($operator && $roleModel->operator_id && $roleModel->operator_id !== $operator->id) {
                    return $this->jsonOrRedirect($request, false, 'لا يمكنك استخدام دور يخص مشغل آخر.');
                }
            }
        }

        // ============================================
        // Step 3: Determine operator for user (if applicable)
        // Employee, Technician, and Custom roles need to be linked to an operator
        // ============================================
        
        // For Employee and Technician created by CompanyOwner: use CompanyOwner's operator
        if ($isEmployeeOrTechnician && $authUser->isCompanyOwner()) {
            $operator = $authUser->ownedOperators()->first();
            if (! $operator) {
                return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
            }
        } elseif ($isCustomRole) {
            // Custom role: check if it's linked to an operator or needs operator selection
            if ($roleModel && $roleModel->operator_id) {
                // Role is already linked to an operator
                $operator = $roleModel->operator;
            } elseif ($authUser->isCompanyOwner()) {
                // Company Owner creating user: use their own operator
                $operator = $authUser->ownedOperators()->first();
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
                }
            } else {
                // Energy Authority or SuperAdmin creating user with custom role: need to select operator
                $operatorId = (int) $request->input('operator_id');
                if (! $operatorId) {
                    return $this->jsonOrRedirect($request, false, 'اختر المشغل لربط المستخدم.');
                }
                $operator = Operator::find($operatorId);
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                }
            }
        }

        // ============================================
        // Step 3: Auto-generate username and password (always)
        // ============================================
        $name = trim($request->validated('name'));
        $nameEn = trim($request->validated('name_en', ''));
        
        // For CompanyOwner: verify phone is in authorized_phones before creating
        if ($roleValue === Role::CompanyOwner->value) {
            $phone = $request->validated('phone');
            if ($phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                if (!\App\Models\AuthorizedPhone::isAuthorized($cleanPhone)) {
                    return $this->jsonOrRedirect($request, false, 'رقم الجوال غير موجود في الأرقام المصرح بها. يرجى إضافة الرقم أولاً في صفحة الأرقام المصرح بها.');
                }
            }
        }

        // Generate username using name_en (English name) for proper username generation
        $nameToUse = $nameEn ?: $name;
        // For custom roles, use a fallback role enum value for username generation
        $roleForUsername = $role ?? Role::Employee; // Use Employee as fallback for custom roles
        $username = $this->generateUsername($roleForUsername, $nameToUse);

        // Auto-generate password (8 random characters)
        $password = \Illuminate\Support\Str::random(8);
        $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
        if (strlen($password) < 6) {
            $password = \Illuminate\Support\Str::random(8);
            $password = preg_replace('/[^a-zA-Z0-9]/', '', $password);
        }
        if (strlen($password) < 8) {
            $password = str_pad($password, 8, \Illuminate\Support\Str::random(1), STR_PAD_RIGHT);
        }

        $plainPassword = $password;

        // ============================================
        // Step 4: Generate unique email if not provided
        // ============================================
        $email = $request->validated('email');
        if (! $email) {
            $email = $username.'@gazarased.com';
            $counter = 1;
            while (User::where('email', $email)->whereNull('deleted_at')->exists()) {
                $email = $username.'_'.$counter.'@gazarased.com';
                $counter++;
            }
        }

        // ============================================
        // Step 5: Create user in database
        // ============================================
        // For custom roles, we need a Role enum value for the role column
        // Use Employee as fallback for custom roles (will be stored as enum value in DB)
        $roleForDb = $role ?? Role::Employee;
        
        $user = User::create([
            'name' => $request->validated('name'),
            'name_en' => $request->validated('name_en'),
            'phone' => $request->validated('phone'),
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role' => $roleForDb, // Use enum value (Employee as fallback for custom roles)
            'role_id' => $roleModel?->id, // Custom role ID (null for system roles)
        ]);

        // ============================================
        // Step 6: Link user to operator (if applicable)
        // ============================================
        if (($isEmployeeOrTechnician || $isCustomRole) && $operator) {
            // Employee, Technician, or user with custom role: link to operator
            $user->operators()->sync([$operator->id]);
        } elseif ($user->isCompanyOwner() && ($authUser->isSuperAdmin() || $authUser->isEnergyAuthority())) {
            // If SuperAdmin/EnergyAuthority adds CompanyOwner, link to operator
            $operatorId = (int) $request->input('operator_id');
            if ($operatorId) {
                $operator = Operator::find($operatorId);
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                }

                // Verify that operator phone exists in authorized phones
                $operatorPhone = $operator->phone;
                if ($operatorPhone) {
                    $authorizedPhone = \App\Models\AuthorizedPhone::where('phone', $operatorPhone)
                        ->where('status', 'active')
                        ->first();

                    if (! $authorizedPhone) {
                        return $this->jsonOrRedirect($request, false, 'لا يمكن إضافة مشغل: رقم المشغل غير موجود في الأرقام المصرح بها. يرجى إضافة الرقم أولاً.');
                    }
                }

                // Link user to operator
                $user->operators()->sync([$operator->id]);
                
                // Update operator owner if not set
                if (!$operator->owner_id) {
                    $operator->owner_id = $user->id;
                    $operator->save();
                }

                // Note: If this is a new operator (created via storeJoinRequest), Operator::boot() will send notification
                // But if this is an existing operator that was just linked to a new owner, we don't send notification here
                // because the operator already exists and notifications were sent when it was first created
                // Only send notification if operator owner was just updated and operator is not approved
                // (This handles the case where SuperAdmin/EnergyAuthority creates a CompanyOwner and links to an existing unapproved operator)
                if (! $operator->is_approved && $operator->owner_id === $user->id) {
                    // Check if operator was just created (within last 5 seconds) - if so, boot() already sent notification
                    $recentlyCreated = $operator->created_at && $operator->created_at->gt(now()->subSeconds(5));
                    if (! $recentlyCreated) {
                        // Operator exists but not approved and owner just linked - send notification
                        \App\Models\Notification::notifyOperatorApprovers(
                            'operator_pending_approval',
                            'مشغل يحتاج للاعتماد',
                            "تم ربط مشغل ({$operator->name}) بمشغل جديد ({$user->name}) - يحتاج للاعتماد والتفعيل",
                            route('admin.operators.show', $operator)
                        );
                    }
                }
            } else {
                // Create new operator (same as PublicHomeController::storeJoinRequest)
                $phone = $request->validated('phone');
                if (!$phone) {
                    return $this->jsonOrRedirect($request, false, 'يجب إدخال رقم الجوال لإنشاء مشغل جديد.');
                }
                
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                
                // Verify phone is in authorized phones (already checked above, but double-check)
                if (!\App\Models\AuthorizedPhone::isAuthorized($cleanPhone)) {
                    return $this->jsonOrRedirect($request, false, 'رقم الجوال غير موجود في الأرقام المصرح بها. يرجى إضافة الرقم أولاً في صفحة الأرقام المصرح بها.');
                }
                
                // Check if operator already exists with this phone
                $existingOperator = Operator::whereHas('owner', function($query) use ($cleanPhone) {
                    $query->where('phone', $cleanPhone);
                })->first();
                if ($existingOperator) {
                    return $this->jsonOrRedirect($request, false, 'يوجد مشغل مسجل بهذا الرقم. يرجى اختيار المشغل من القائمة.');
                }
                
                // Create new operator
                $operator = Operator::create([
                    'name' => $request->validated('name'),
                    'owner_id' => $user->id,
                    'is_approved' => false,
                    'status' => 'active',
                ]);
                
                // Link user to operator
                $user->operators()->sync([$operator->id]);
                
                // Operator::boot() will send notification automatically
            }
        }

        // ============================================
        // Step 7: Send login credentials via SMS
        // SMS is automatically sent when user is created (if phone is provided)
        // Contains: Welcome message, Role name, Username, Password, Login link
        // ============================================
        if ($user->phone) {
            try {
                $this->sendUserCredentialsSMS($user->phone, $user->name, $username, $plainPassword, $roleForDb, $roleModel);
            } catch (\Exception $e) {
                \Log::error('Failed to send SMS to user', [
                    'phone' => $user->phone,
                    'username' => $username,
                    'role' => $roleValue,
                    'role_model_id' => $roleModel?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ============================================
        // Step 8: Assign default permissions automatically
        // ============================================
        try {
            $user->assignDefaultPermissions();
        } catch (\Exception $e) {
            \Log::error('Failed to assign default permissions for user: '.$e->getMessage());
        }

        // ============================================
        // Step 9: Create 3 default welcome messages for new user
        // ============================================
        try {
            $user->createDefaultMessages();
        } catch (\Exception $e) {
            \Log::error('Failed to create default messages for user: '.$e->getMessage());
        }

        // Notify super admins when a new user is created (except if creator is SuperAdmin)
        if (! auth()->user()->isSuperAdmin()) {
            Notification::notifySuperAdmins(
                'user_added',
                'تم إضافة مستخدم جديد',
                "تم إضافة المستخدم: {$user->name} ({$user->role_name})",
                route('admin.users.show', $user)
            );
        }

        // Return message with login credentials (always auto-generated now)
        $message = "تم إنشاء المستخدم بنجاح ✅\n";
        $message .= "اسم المستخدم: {$username}\n";
        $message .= "كلمة المرور: {$plainPassword}";
        if ($user->phone) {
            $message .= "\n(تم إرسالها على رقم الجوال)";
        }

        return $this->jsonOrRedirect($request, true, $message, [
            'username' => $username,
            'password' => $plainPassword,
            'phone' => $user->phone,
        ]);

        return $this->jsonOrRedirect($request, true, 'تم إنشاء المستخدم بنجاح.');
    }

    /**
     * Send login credentials to user via SMS
     * SMS is automatically sent when user is created with phone number
     * Contains: Welcome message, Role name (from database), Username, Password, Login link
     *
     * @param  string  $phone  User phone number
     * @param  string  $name  User name
     * @param  string  $username  Generated username
     * @param  string  $password  Generated password
     * @param  Role  $role  User role enum
     * @param  \App\Models\Role|null  $roleModel  Role model from database (contains custom role label)
     */
    private function sendUserCredentialsSMS(string $phone, string $name, string $username, string $password, Role $role, ?\App\Models\Role $roleModel = null, bool $isPasswordReset = false): void
    {
        // استخدام رابط كامل (full URL) لرسائل SMS
        // url() helper يولد رابط كامل بناءً على APP_URL
        $loginUrl = url('/login');

        // Use role label from database if available (for custom roles defined by Energy Authority or Company Owner)
        // Otherwise, use default labels for system roles
        if ($roleModel && $roleModel->label) {
            $roleName = $roleModel->label;
        } else {
            // Fallback to default labels for system roles only
            // Custom roles should always use roleModel->label (should not reach here)
            $roleName = match ($role) {
                Role::SuperAdmin => 'مدير النظام',
                Role::Admin => 'مدير',
                Role::EnergyAuthority => 'سلطة الطاقة',
                Role::CompanyOwner => 'مشغل',
                default => $role->value, // Fallback for custom roles (should not happen if roleModel exists)
            };
        }

        // Get SMS template from database - use different template for password reset
        $templateKey = $isPasswordReset ? 'password_reset' : 'user_credentials';
        $smsTemplate = \App\Models\SmsTemplate::getByKey($templateKey);

        if ($smsTemplate) {
            // Use template from database
            $message = $smsTemplate->render([
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'role' => $roleName,
                'login_url' => $loginUrl,
            ]);
        } else {
            // Get site name from settings
            $siteName = \App\Models\Setting::get('site_name', 'نور');
            
            // Fallback to default template if no template exists in database (max 160 characters)
            if ($isPasswordReset) {
                $message = "مرحباً {$name}،\nتم إعادة تعيين كلمة المرور لحسابك على منصة {$siteName}.\nاسم المستخدم: {$username}\nكلمة المرور الجديدة: {$password}\nرابط الدخول: {$loginUrl}";
            } else {
                $message = "مرحباً {$name}،\nتم تسجيلك على منصة {$siteName}.\nالدور: {$roleName}\nاسم المستخدم: {$username}\nكلمة المرور: {$password}\nرابط الدخول: {$loginUrl}";
            }

            // Ensure message does not exceed 160 characters
            if (mb_strlen($message) > 160) {
                $message = mb_substr($message, 0, 157).'...';
            }
        }

        try {
            $smsService = new \App\Services\HotSMSService;
            $result = $smsService->sendSMS($phone, $message, 2);

            if ($result['success']) {
                \Log::info('SMS sent to user successfully', [
                    'phone' => $phone,
                    'name' => $name,
                    'username' => $username,
                    'role' => $role->value,
                    'role_label' => $roleName,
                    'message_id' => $result['message_id'] ?? null,
                ]);
            } else {
                \Log::error('Failed to send SMS to user', [
                    'phone' => $phone,
                    'name' => $name,
                    'username' => $username,
                    'role' => $role->value,
                    'error_code' => $result['code'],
                    'error_message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('SMS service exception for user', [
                'phone' => $phone,
                'name' => $name,
                'username' => $username,
                'role' => $role->value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $authUser = auth()->user();

        // Energy Authority: يمكنه تعديل المشغلين فقط ولا يمكنه تغيير دورهم
        if ($authUser->isEnergyAuthority()) {
            if (! $user->isCompanyOwner()) {
                abort(403, 'لا يمكنك تعديل هذا المستخدم. يمكنك تعديل المشغلين فقط.');
            }
            // منع تغيير الدور - يجب أن يبقى مشغل
            $request->merge(['role' => Role::CompanyOwner->value]);
        }

        // Get role_id from roles table (system role or custom role)
        $roleValue = $request->validated('role');
        $adminSystemRoles = [Role::SuperAdmin->value, Role::Admin->value, Role::EnergyAuthority->value, Role::CompanyOwner->value];
        $isAdminSystemRole = in_array($roleValue, $adminSystemRoles, true);
        $isEmployeeOrTechnician = in_array($roleValue, [Role::Employee->value, Role::Technician->value], true);
        $isSystemRole = $isAdminSystemRole || $isEmployeeOrTechnician;

        $newRole = null;
        $roleModel = null;

        // Check permissions: Company Owner cannot assign admin roles
        if ($authUser->isCompanyOwner() && $isAdminSystemRole) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك تعيين أدوار إدارية. يمكنك فقط تعيين موظفين وفنيين.');
        }

        if ($isSystemRole) {
            $newRole = Role::from($roleValue);
            $roleModel = \App\Models\Role::findByName($newRole->value);
        } else {
            // Custom role: get role_id from request
            $roleId = (int) $request->input('role_id');
            if ($roleId) {
                $roleModel = \App\Models\Role::find($roleId);
                if (! $roleModel || $roleModel->is_system) {
                    return $this->jsonOrRedirect($request, false, 'الدور المحدد غير موجود أو غير صالح.');
                }
                // For custom roles, we still need a Role enum value for backward compatibility
                // Use the role name as the enum value
                try {
                    $newRole = Role::from($roleModel->name);
                } catch (\ValueError $e) {
                    // Custom role name doesn't match enum, use a fallback
                    $newRole = Role::CompanyOwner; // Fallback
                }
            } else {
                return $this->jsonOrRedirect($request, false, 'يجب تحديد الدور.');
            }
        }

        // تحديث name_en
        $nameEn = $request->validated('name_en', '');
        $originalNameEn = $user->name_en ?? '';
        $originalName = $user->name ?? '';
        
        $newName = $request->validated('name');
        $nameEnChanged = $nameEn !== $originalNameEn;
        $nameChanged = $newName !== $originalName;

        // إذا تغير name_en أو name، يجب تحديث username تلقائياً
        $newUsername = $request->validated('username'); // القيمة الحالية من النموذج
        if ($nameEnChanged || $nameChanged) {
            // استخدام name_en إذا كان موجوداً، وإلا استخدام name (العربي)
            $nameForUsername = $nameEn ?: $newName;
            // توليد username جديد من الاسم
            $newUsername = $this->generateUsername($newRole, $nameForUsername);
        } else {
            // إذا لم يتغير الاسم، نستخدم username الحالي (لا نغيره)
            $newUsername = $user->username;
        }

        $data = [
            'name' => $newName,
            'name_en' => $nameEn,
            'username' => $newUsername,
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'role' => $newRole,
            'role_id' => $roleModel?->id,
        ];

        // لا نسمح بتغيير كلمة المرور من هنا - يجب استخدام resetPassword
        // if ($request->filled('password')) {
        //     $plainPassword = $request->validated('password');
        //     $data['password'] = Hash::make($plainPassword);
        // }

        $user->update($data);

        // Update operator relationship for users with custom roles
        if ($user->hasCustomRole()) {
            if ($authUser->isCompanyOwner()) {
                $operator = $authUser->ownedOperators()->first();
                if (! $operator) {
                    return $this->jsonOrRedirect($request, false, 'لا يوجد مشغل مرتبط بحسابك.');
                }
                $user->operators()->sync([$operator->id]);
            } else {
                // التعامل مع operator_id كـ integer أو array
                $operatorIdInput = $request->input('operator_id');
                $operatorId = 0;
                
                if (is_array($operatorIdInput)) {
                    // إذا كان array، نأخذ أول قيمة
                    $operatorId = !empty($operatorIdInput) ? (int) ($operatorIdInput[0] ?? 0) : 0;
                } else {
                    $operatorId = (int) ($operatorIdInput ?? 0);
                }
                
                if ($operatorId) {
                    $operator = Operator::find($operatorId);
                    if (! $operator) {
                        return $this->jsonOrRedirect($request, false, 'المشغل المحدد غير موجود.');
                    }
                    $user->operators()->sync([$operatorId]);
                } elseif ($user->roleModel && $user->roleModel->operator_id) {
                    // If role is linked to operator, keep the relationship
                    $user->operators()->sync([$user->roleModel->operator_id]);
                }
            }
        } elseif (! $user->isCompanyOwner()) {
            // System roles (SuperAdmin, Admin, EnergyAuthority) don't need operator relationship
            $user->operators()->detach();
        }

        return $this->jsonOrRedirect($request, true, 'تم تحديث المستخدم بنجاح.');
    }

    /**
     * إعادة تعيين كلمة المرور
     * كلمة المرور الجديدة = username_123
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $authUser = auth()->user();

        // التحقق من الصلاحيات
        $this->authorize('update', $user);

        // التحقق من وجود رقم الجوال
        if (! $user->phone) {
            return response()->json([
                'ok' => false,
                'message' => 'لا يمكن إعادة تعيين كلمة المرور: المستخدم لا يملك رقم جوال.',
            ], 400);
        }

        // توليد كلمة المرور الجديدة = username_123
        $newPassword = $user->username . '_123';
        $hashedPassword = Hash::make($newPassword);

        // تحديث كلمة المرور
        $user->update([
            'password' => $hashedPassword,
        ]);

        // إرسال SMS بكلمة المرور الجديدة
        try {
            $roleModel = $user->roleModel;
            $this->sendUserCredentialsSMS(
                $user->phone,
                $user->name,
                $user->username,
                $newPassword,
                $user->role,
                $roleModel,
                true // isPasswordReset = true
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS after password reset', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // لا نعيد خطأ - كلمة المرور تم تحديثها بنجاح حتى لو فشل إرسال SMS
        }

        return response()->json([
            'ok' => true,
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح. تم إرسال كلمة المرور الجديدة عبر SMS.',
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حذف حسابك الخاص.');
        }

        $user->delete();

        return $this->jsonOrRedirect($request, true, 'تم حذف المستخدم بنجاح.');
    }

    /**
     * Toggle user status (active/inactive)
     */
    /**
     * Toggle user status (active/inactive) - not for suspension/banning
     * For suspension/banning, use suspend/unsuspend methods
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        // Check authorization (update policy)
        $this->authorize('update', $user);

        // Super Admin or Company Owner can change status
        // (Policy checks relationship for Company Owner)
        if (! $authUser->isSuperAdmin() && ! $authUser->isCompanyOwner()) {
            abort(403, 'لا تملك صلاحية لتغيير حالة المستخدم');
        }

        // Prevent deactivating yourself
        if ($user->id === $authUser->id) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك إيقاف حسابك الخاص.');
        }

        // If user is suspended, they can only be unsuspended via suspend/unsuspend method
        if ($user->isSuspended()) {
            return $this->jsonOrRedirect($request, false, 'المستخدم محظور/معطل. يجب رفع الحظر أولاً.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $statusLabel = $user->status === 'active' ? 'تفعيل' : 'إيقاف';
        $message = "تم {$statusLabel} المستخدم بنجاح";

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Suspend/ban a user who causes problems
     */
    public function suspend(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->hasPermission('users.suspend')) {
            abort(403, 'لا تملك صلاحية لتعطيل/حظر المستخدمين');
        }

        if ($user->id === $authUser->id) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حظر حسابك الخاص.');
        }

        if ($user->isSuperAdmin() && ! $authUser->isSuperAdmin()) {
            return $this->jsonOrRedirect($request, false, 'لا يمكنك حظر السوبر أدمن.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'يجب إدخال سبب التعطيل/الحظر.',
            'reason.max' => 'سبب التعطيل/الحظر يجب أن لا يتجاوز 1000 حرف.',
        ]);

        $reason = $request->input('reason');

        // Suspend the user
        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $reason,
            'suspended_by' => $authUser->id,
        ]);

        $operator = null;
        $staffCount = 0;

        // If Company Owner, suspend operator and staff
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operator->update(['status' => 'inactive']);

                // Suspend staff users
                $staffUsers = $operator->users()
                    ->whereHas('roleModel', function ($q) use ($operator) {
                        $q->where('is_system', false)->where('operator_id', $operator->id);
                    })
                    ->where('id', '!=', $user->id)
                    ->get();

                $staffCount = $staffUsers->count();

                foreach ($staffUsers as $staffUser) {
                    $staffUser->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                        'suspended_reason' => "تم تعطيل حسابك بسبب تعطيل المشغل ({$user->name})",
                        'suspended_by' => $authUser->id,
                    ]);

                    Notification::createNotification(
                        $staffUser->id,
                        'user_suspended',
                        'تم حظر/تعطيل حسابك',
                        "تم حظر/تعطيل حسابك بسبب تعطيل المشغل ({$user->name}). يرجى التواصل مع الإدارة.",
                        null
                    );
                }

                Notification::createNotification(
                    $user->id,
                    'operator_suspended',
                    'تم تعطيل المشغل',
                    "تم تعطيل/حظر حسابك ({$user->name}) وبالتالي تم تعطيل المشغل ({$operator->name}) و{$staffCount} من الموظفين. السبب: {$reason}",
                    null
                );
            } else {
                // Company Owner but no operator found
                Notification::createNotification(
                    $user->id,
                    'user_suspended',
                    'تم حظر/تعطيل حسابك',
                    "تم حظر/تعطيل حسابك. السبب: {$reason}. يرجى التواصل مع الإدارة.",
                    null
                );
            }
        } else {
            // Regular user (not Company Owner)
            Notification::createNotification(
                $user->id,
                'user_suspended',
                'تم حظر/تعطيل حسابك',
                "تم حظر/تعطيل حسابك. السبب: {$reason}. يرجى التواصل مع الإدارة.",
                null
            );
        }

        $message = "تم حظر/تعطيل المستخدم بنجاح. السبب: {$reason}";
        if ($operator) {
            $message .= " (تم أيضاً تعطيل المشغل ({$operator->name}) و{$staffCount} من الموظفين)";
        }

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Unsuspend/unban a user
     */
    public function unsuspend(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $authUser = auth()->user();

        if (! $authUser->hasPermission('users.suspend')) {
            abort(403, 'لا تملك صلاحية لرفع الحظر عن المستخدمين');
        }

        if (! $user->isSuspended()) {
            return $this->jsonOrRedirect($request, false, 'المستخدم غير محظور/معطل.');
        }

        // Unsuspend the user
        $user->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
            'suspended_by' => null,
        ]);

        $operator = null;
        $unsuspendedStaffCount = 0;

        // If Company Owner, reactivate operator and staff
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operator->update(['status' => 'active']);

                // Reactivate staff users that were suspended due to operator suspension
                $staffUsers = $operator->users()
                    ->whereHas('roleModel', function ($q) use ($operator) {
                        $q->where('is_system', false)->where('operator_id', $operator->id);
                    })
                    ->where('id', '!=', $user->id)
                    ->where('status', 'suspended')
                    ->whereNotNull('suspended_by')
                    ->get();

                foreach ($staffUsers as $staffUser) {
                    if (str_contains($staffUser->suspended_reason ?? '', 'تعطيل المشغل')) {
                        $staffUser->update([
                            'status' => 'active',
                            'suspended_at' => null,
                            'suspended_reason' => null,
                            'suspended_by' => null,
                        ]);

                        $unsuspendedStaffCount++;

                        Notification::createNotification(
                            $staffUser->id,
                            'user_unsuspended',
                            'تم رفع الحظر عن حسابك',
                            "تم رفع الحظر عن حسابك بسبب إعادة تفعيل المشغل ({$user->name}). يمكنك الآن تسجيل الدخول مرة أخرى.",
                            route('login')
                        );
                    }
                }

                Notification::createNotification(
                    $user->id,
                    'operator_reactivated',
                    'تم إعادة تفعيل المشغل',
                    "تم رفع الحظر عن حسابك ({$user->name}) وبالتالي تم إعادة تفعيل المشغل ({$operator->name}) و{$unsuspendedStaffCount} من الموظفين.",
                    route('admin.operators.profile')
                );
            } else {
                // Company Owner but no operator found
                Notification::createNotification(
                    $user->id,
                    'user_unsuspended',
                    'تم رفع الحظر عن حسابك',
                    'تم رفع الحظر عن حسابك. يمكنك الآن تسجيل الدخول مرة أخرى.',
                    route('login')
                );
            }
        } else {
            // Regular user (not Company Owner)
            Notification::createNotification(
                $user->id,
                'user_unsuspended',
                'تم رفع الحظر عن حسابك',
                'تم رفع الحظر عن حسابك. يمكنك الآن تسجيل الدخول مرة أخرى.',
                route('login')
            );
        }

        $message = 'تم رفع الحظر عن المستخدم بنجاح.';
        if ($operator) {
            $message .= " (تم أيضاً إعادة تفعيل المشغل ({$operator->name}) و{$unsuspendedStaffCount} من الموظفين)";
        }

        return $this->jsonOrRedirect($request, true, $message);
    }

    /**
     * Select2 operators (server-side)
     */
    public function ajaxOperators(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser || (! $authUser->isSuperAdmin() && ! $authUser->isEnergyAuthority())) {
            abort(403);
        }

        $term = trim((string) $request->query('q', $request->query('term', '')));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        $query = Operator::query()->orderBy('name');

        if ($term !== '') {
            // تنظيف المدخلات لمنع SQL Injection
            $term = $this->sanitizeSearchInput($term);
            if (!empty($term)) {
                $query->where(function ($x) use ($term) {
                    $x->where('name', 'like', "%{$term}%")
                        ->orWhereHas('owner', function ($q) use ($term) {
                            $q->where('name', 'like', "%{$term}%")
                                ->orWhere('username', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                });
            }
        }

        $p = $query->paginate($perPage, ['id', 'name'], 'page', $page);

        $results = $p->getCollection()->map(function ($op) {
            return ['id' => $op->id, 'text' => $op->name];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $p->hasMorePages()],
        ]);
    }

    /**
     * الدخول بحساب مستخدم آخر (للسوبر أدمن فقط)
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        // التحقق من أن المستخدم الحالي هو سوبر أدمن
        if (! $currentUser->isSuperAdmin()) {
            return redirect()->back()->with('error', 'غير مصرح لك بالدخول بحساب مستخدم آخر.');
        }

        // منع الدخول بحساب نفسه
        if ($currentUser->id === $user->id) {
            return redirect()->back()->with('error', 'لا يمكنك الدخول بحسابك الخاص.');
        }

        // منع الدخول بحساب system user (منصة نور)
        if ($user->isSystemUser()) {
            $siteName = \App\Models\Setting::get('site_name', 'نور');
            return redirect()->back()->with('error', "لا يمكن الدخول بحساب منصة {$siteName}.");
        }

        // حفظ معلومات المستخدم الأصلي في Session
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_name', $currentUser->name);

        // تسجيل الدخول بحساب المستخدم المطلوب
        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', "تم الدخول بحساب {$user->name} بنجاح.");
    }

    /**
     * الخروج من حساب المستخدم والعودة للحساب الأصلي
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $impersonatorId = session()->get('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('admin.dashboard')->with('error', 'لا يوجد حساب أصلي للعودة إليه.');
        }

        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            session()->forget(['impersonator_id', 'impersonator_name']);

            return redirect()->route('login')->with('error', 'الحساب الأصلي غير موجود.');
        }

        // حذف معلومات الـ impersonation من Session
        session()->forget(['impersonator_id', 'impersonator_name']);

        // تسجيل الدخول بالحساب الأصلي
        Auth::login($impersonator);

        return redirect()->route('admin.users.index')->with('success', 'تم العودة لحسابك الأصلي بنجاح.');
    }

    /**
     * Generate username based on role and name (same logic as PublicHomeController)
     */
    private function generateUsername(\App\Enums\Role $role, string $nameEn, ?string $fallbackIdNumber = null): string
    {
        // تحديد البادئة حسب الدور
        $prefix = match($role) {
            \App\Enums\Role::SuperAdmin => 'sp_',
            \App\Enums\Role::Admin => 'a_',
            \App\Enums\Role::EnergyAuthority => 'ea_',
            \App\Enums\Role::CompanyOwner => 'op_',
            \App\Enums\Role::Technician => 't_',
            \App\Enums\Role::CivilDefense => 'cd_',
            default => 'user_',
        };
        
        // تنظيف الاسم من الأحرف العربية والأحرف الخاصة
        $cleanedName = $this->cleanString($nameEn);
        
        // إذا كان الاسم فارغاً أو يحتوي على أحرف عربية فقط، نحاول تحويله
        // إزالة الأحرف غير اللاتينية أولاً للتحقق
        $latinOnly = preg_replace('/[^a-z0-9\s]/i', '', $cleanedName);
        if (empty(trim($latinOnly)) && !empty($cleanedName)) {
            // الاسم يحتوي على أحرف عربية فقط، نحاول تحويله باستخدام transliteration بسيط
            $cleanedName = $this->transliterateArabicToLatin($cleanedName);
        }
        
        $nameParts = explode(' ', trim($cleanedName));
        $nameParts = array_filter($nameParts, function($part) {
            return !empty(trim($part));
        });
        $nameParts = array_values($nameParts); // إعادة ترقيم المصفوفة
        
        // استخراج أول حرف من الاسم الأول + الاسم الأخير كاملاً
        if (count($nameParts) >= 2) {
            // أول حرف من الاسم الأول
            $firstChar = strtolower(substr(trim($nameParts[0]), 0, 1));
            // الاسم الأخير كاملاً (اسم العائلة)
            $lastName = strtolower(trim($nameParts[count($nameParts) - 1]));
            // إزالة الأحرف الخاصة والمسافات من اسم العائلة (يحافظ على a-z0-9 فقط)
            $lastName = preg_replace('/[^a-z0-9]/', '', $lastName);
            $usernameBase = $firstChar . $lastName;
        } else {
            // إذا كان اسم واحد فقط، استخدم أول 8 أحرف
            $usernameBase = strtolower(preg_replace('/[^a-z0-9]/', '', $cleanedName));
            $usernameBase = substr($usernameBase, 0, 8);
        }
        
        // التأكد من أن usernameBase ليس فارغاً
        if (empty($usernameBase)) {
            // استخدام رقم الهوية كبديل
            $usernameBase = $fallbackIdNumber ? substr($fallbackIdNumber, -4) : 'user';
        }
        
        // إضافة البادئة
        $username = $prefix . $usernameBase;
        
        // التأكد من أن username فريد
        $counter = 1;
        $originalUsername = $username;
        while (User::where('username', $username)->whereNull('deleted_at')->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

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

    /**
     * Clean UTF-8 string - uses AppServiceProvider's static method for consistency
     */
    private function cleanString(?string $value): string
    {
        return \App\Providers\AppServiceProvider::cleanStringStatic($value);
    }

    /**
     * تحويل الأحرف العربية إلى لاتينية (transliteration بسيط)
     * يستخدم لتحويل الأسماء العربية إلى أسماء لاتينية لتوليد username
     */
    private function transliterateArabicToLatin(string $arabicText): string
    {
        // خريطة بسيطة للأحرف العربية الشائعة إلى لاتينية
        $transliterationMap = [
            'أ' => 'a', 'ا' => 'a', 'إ' => 'i', 'آ' => 'aa',
            'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
            'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'th',
            'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh',
            'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z',
            'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
            'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a',
            'ة' => 'h', 'ئ' => 'y', 'ء' => '', 'ؤ' => 'w',
        ];

        $result = '';
        $text = mb_strtolower($arabicText, 'UTF-8');
        
        for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            if (isset($transliterationMap[$char])) {
                $result .= $transliterationMap[$char];
            } elseif (preg_match('/[a-z0-9\s]/', $char)) {
                // الاحتفاظ بالأحرف اللاتينية والأرقام والمسافات
                $result .= $char;
            }
            // تجاهل الأحرف الأخرى
        }
        
        return trim($result);
    }

    private function jsonOrRedirect(Request $request, bool $ok, string $message, array $extraData = [])
    {
        // Clean message to ensure valid UTF-8 encoding
        $message = \App\Providers\AppServiceProvider::cleanStringStatic($message);
        
        // Clean extra data if it contains strings
        if (!empty($extraData)) {
            $extraData = \App\Providers\AppServiceProvider::cleanInputArrayStatic($extraData);
        }
        
        if ($request->wantsJson() || $request->ajax()) {
            $response = [
                'ok' => $ok,
                'message' => $message,
            ];
            if (! empty($extraData)) {
                $response = array_merge($response, $extraData);
            }

            return response()->json($response, $ok ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $ok
            ? redirect()->route('admin.users.index')->with('success', $message)->with($extraData)
            : redirect()->back()->withInput()->with('error', $message);
    }
}
