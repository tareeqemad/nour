<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $user = auth()->user();
        $query = Role::withCount(['users', 'permissions'])->with(['operator', 'creator']);

        // Filter roles based on user authority
        // Super Admin and Admin can see general roles (operator_id = null) that they or Super Admin created
        // Energy Authority and Company Owner cannot see general roles - they only see roles they created themselves
        if ($user->isAdmin()) {
            // Admin can see system roles (except super_admin) and general roles (operator_id = null) created by Admin or Super Admin
            // Admin cannot see roles specific to operators
            $query->where(function ($q) {
                $q->where(function($q1) {
                    // System roles except super_admin
                    $q1->where('is_system', true)
                       ->where('name', '!=', 'super_admin');
                })
                  ->orWhere(function($q2) {
                      // General roles (operator_id = null) created by Admin or Super Admin
                      $q2->whereNull('operator_id')
                         ->where(function($q3) {
                             $q3->whereNull('created_by') // System roles or legacy roles
                                ->orWhereHas('creator', function($q4) {
                                    $q4->whereIn('role', ['super_admin', 'admin']);
                                });
                         });
                  });
            });
        } elseif ($user->isEnergyAuthority()) {
            // Energy Authority can see:
            // 1. System roles (except super_admin)
            // 2. General roles created by Energy Authority or Super Admin/Admin (for reference)
            // 3. Operator-specific roles created by Energy Authority
            $query->where(function ($q) use ($user) {
                $q->where(function($q1) {
                    // System roles except super_admin
                    $q1->where('is_system', true)
                       ->where('name', '!=', 'super_admin');
                })
                  ->orWhere('created_by', $user->id) // Roles created by this Energy Authority (general or operator-specific)
                  ->orWhere(function($q2) {
                      // General roles (operator_id = null) created by Super Admin or Admin (for reference only)
                      $q2->whereNull('operator_id')
                         ->where('is_system', false)
                         ->where(function($q3) {
                             $q3->whereNull('created_by')
                                ->orWhereHas('creator', function($q4) {
                                    $q4->whereIn('role', ['super_admin', 'admin']);
                                });
                         });
                  });
            });
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Company Owner can ONLY see roles they created for their operator
                // No system roles, no general roles, no roles from Energy Authority, no roles from other operators
                $query->where('operator_id', $operator->id)
                      ->where('created_by', $user->id)
                      ->where('is_system', false); // Only custom roles they created
            } else {
                // If no operator, no roles (cannot create users without operator)
                $query->whereRaw('1 = 0'); // Empty result
            }
        } elseif (!$user->isSuperAdmin()) {
            // Unauthorized
            abort(403);
        }
        // Super Admin sees all roles (no filter needed)

        // البحث من Session (من AJAX POST)
        $filters = session('roles_filter', []);
        
        // البحث في اسم الدور
        if (!empty($filters['name'])) {
            $name = $this->sanitizeSearchInput($filters['name']);
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        }

        // البحث في التسمية
        if (!empty($filters['label'])) {
            $label = $this->sanitizeSearchInput($filters['label']);
            if (!empty($label)) {
                $query->where('label', 'like', "%{$label}%");
            }
        }

        // البحث في الوصف
        if (!empty($filters['description'])) {
            $description = $this->sanitizeSearchInput($filters['description']);
            if (!empty($description)) {
                $query->where('description', 'like', "%{$description}%");
            }
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('order')
            ->orderBy('label')
            ->paginate(15);

        // إذا كان AJAX request، إرجاع JSON مع HTML partial
        if ($request->wantsJson() || $request->boolean('ajax')) {
            $isSuperAdmin = auth()->user()->isSuperAdmin();
            $isCompanyOwner = auth()->user()->isCompanyOwner();
            
            return response()->json([
                'ok' => true,
                'html' => [
                    'tbody' => view('admin.roles.partials.tbody', compact('roles', 'isSuperAdmin', 'isCompanyOwner'))->render(),
                    'pagination' => view('admin.roles.partials.pagination', compact('roles'))->render(),
                ],
                'meta' => [
                    'total' => $roles->total(),
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                ]
            ]);
        }

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * حفظ الفلاتر في Session وإرجاع النتائج عبر AJAX
     */
    public function filter(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        // إذا كان reset، امسح الفلاتر
        if ($request->boolean('reset')) {
            session()->forget('roles_filter');
        } else {
            // حفظ الفلاتر في Session
            $filters = [];
            if ($request->filled('name')) {
                $filters['name'] = $this->sanitizeSearchInput($request->input('name'));
            }
            if ($request->filled('label')) {
                $filters['label'] = $this->sanitizeSearchInput($request->input('label'));
            }
            if ($request->filled('description')) {
                $filters['description'] = $this->sanitizeSearchInput($request->input('description'));
            }

            session(['roles_filter' => $filters]);
        }

        // إعادة تحميل البيانات عبر index مع ajax flag
        $user = auth()->user();
        $query = Role::withCount(['users', 'permissions'])->with(['operator', 'creator']);

        // Filter roles based on user authority (نفس منطق index)
        if ($user->isAdmin()) {
            $query->where(function ($q) {
                $q->where(function($q1) {
                    $q1->where('is_system', true)
                       ->where('name', '!=', 'super_admin');
                })
                  ->orWhere(function($q2) {
                      $q2->whereNull('operator_id')
                         ->where(function($q3) {
                             $q3->whereNull('created_by')
                                ->orWhereHas('creator', function($q4) {
                                    $q4->whereIn('role', ['super_admin', 'admin']);
                                });
                         });
                  });
            });
        } elseif ($user->isEnergyAuthority()) {
            $query->where(function ($q) use ($user) {
                $q->where(function($q1) {
                    $q1->where('is_system', true)
                       ->where('name', '!=', 'super_admin');
                })
                  ->orWhere('created_by', $user->id)
                  ->orWhere(function($q2) {
                      $q2->whereNull('operator_id')
                         ->where('is_system', false)
                         ->where(function($q3) {
                             $q3->whereNull('created_by')
                                ->orWhereHas('creator', function($q4) {
                                    $q4->whereIn('role', ['super_admin', 'admin']);
                                });
                         });
                  });
            });
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id)
                      ->where('created_by', $user->id)
                      ->where('is_system', false);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (!$user->isSuperAdmin()) {
            abort(403);
        }

        // البحث من Session
        $filters = session('roles_filter', []);
        
        if (!empty($filters['name'])) {
            $name = $this->sanitizeSearchInput($filters['name']);
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        }

        if (!empty($filters['label'])) {
            $label = $this->sanitizeSearchInput($filters['label']);
            if (!empty($label)) {
                $query->where('label', 'like', "%{$label}%");
            }
        }

        if (!empty($filters['description'])) {
            $description = $this->sanitizeSearchInput($filters['description']);
            if (!empty($description)) {
                $query->where('description', 'like', "%{$description}%");
            }
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('order')
            ->orderBy('label')
            ->paginate(15);

        $isSuperAdmin = $user->isSuperAdmin();
        $isCompanyOwner = $user->isCompanyOwner();

        return response()->json([
            'ok' => true,
            'html' => [
                'tbody' => view('admin.roles.partials.tbody', compact('roles', 'isSuperAdmin', 'isCompanyOwner'))->render(),
                'pagination' => view('admin.roles.partials.pagination', compact('roles'))->render(),
            ],
            'meta' => [
                'total' => $roles->total(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $user = auth()->user();
        
        // Get available permissions
        $permissions = Permission::orderBy('group')->orderBy('order')->get();
        
        if ($user->isAdmin()) {
            // Admin: cannot grant system permissions (users, operators, permissions, settings, constants, logs)
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        } elseif ($user->isEnergyAuthority() || $user->isCompanyOwner()) {
            // Energy Authority and Company Owner: filter system permissions
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        }
        
        $permissions = $permissions->groupBy('group');

        // Get operators list
        // Super Admin and Energy Authority can create general roles or operator-specific roles (can select operator or leave null)
        // Admin can only create general roles (no operator selection needed)
        // Company Owner can only create roles for their own operator (no selection needed)
        $operators = collect();
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            $operators = \App\Models\Operator::orderBy('name')->get(['id', 'name']);
        }

        return view('admin.roles.create', compact('permissions', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $operatorId = null;
        
        // Determine operator_id based on user authority
        // Super Admin, Admin, and Energy Authority can create general roles (operator_id = null)
        // Energy Authority can also create operator-specific roles (operator_id = specific operator)
        // Company Owner can only create operator-specific roles (for their own operator)
        if ($user->isAdmin()) {
            // Admin can only create general roles (operator_id = null)
            $operatorId = null;
        } elseif ($user->isEnergyAuthority()) {
            // Energy Authority can create:
            // 1. General roles (operator_id = null) - for the entire system
            // 2. Operator-specific roles (operator_id = specific operator) - for specific operators
            $operatorId = $request->validated('operator_id');
            if ($operatorId === '' || $operatorId === null) {
                $operatorId = null; // General role for the entire system
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if (!$operator) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يوجد مشغل مرتبط بحسابك. أكمل ملف المشغل أولاً.');
            }
            // Company Owner can only create roles for their own operator
            $operatorId = $operator->id;
        } elseif ($user->isSuperAdmin()) {
            // Super Admin can choose operator or leave null (general role)
            $operatorId = $request->validated('operator_id');
            if ($operatorId === '' || $operatorId === null) {
                $operatorId = null;
            }
        } else {
            abort(403);
        }

        // التحقق من الصلاحيات - منع منح صلاحيات النظام
        $permissionIds = $request->validated('permissions', []);
        if (($user->isAdmin() || $user->isCompanyOwner()) && !empty($permissionIds)) {
            $systemPermissions = $this->getSystemPermissions();
            $systemPermissionIds = Permission::whereIn('name', $systemPermissions)->pluck('id')->toArray();
            $permissionIds = array_diff($permissionIds, $systemPermissionIds);
        }

        $role = Role::create([
            'name' => $request->validated('name'),
            'label' => $request->validated('label'),
            'description' => $request->validated('description'),
            'is_system' => false,
            'order' => $request->validated('order', 0),
            'operator_id' => $operatorId,
            'created_by' => $user->id, // Track who created this role
        ]);

        // ربط الصلاحيات بالدور
        if (!empty($permissionIds)) {
            $role->permissions()->attach($permissionIds);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load(['permissions', 'users']);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $user = auth()->user();
        
        // Get available permissions
        $permissions = Permission::orderBy('group')->orderBy('order')->get();
        
        if ($user->isAdmin()) {
            // Admin: cannot grant system permissions
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        } elseif ($user->isEnergyAuthority() || $user->isCompanyOwner()) {
            // Energy Authority and Company Owner: filter system permissions
            $systemPermissions = $this->getSystemPermissions();
            $permissions = $permissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });
        }
        
        $permissions = $permissions->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        // Get operators list (for Super Admin and Energy Authority to change operator)
        // Admin cannot change operator (general roles only)
        // Company Owner cannot change operator (their own operator only)
        $operators = collect();
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // Super Admin and Energy Authority can change operator (can set to null for general role or specific operator)
            $operators = \App\Models\Operator::orderBy('name')->get(['id', 'name']);
        }

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $user = auth()->user();
        
        // منع تحديث الأدوار النظامية (name و is_system)
        if ($role->is_system) {
            $updateData = [
                'label' => $request->validated('label'),
                'description' => $request->validated('description'),
                'order' => $request->validated('order', $role->order),
            ];
        } else {
            $updateData = [
                'name' => $request->validated('name'),
                'label' => $request->validated('label'),
                'description' => $request->validated('description'),
                'order' => $request->validated('order', $role->order),
            ];
            
            // Super Admin and Energy Authority can change operator
            // Admin cannot change operator (general roles only)
            // Company Owner cannot change operator (their own operator only)
            if (($user->isSuperAdmin() || $user->isEnergyAuthority()) && $request->has('operator_id')) {
                $operatorId = $request->validated('operator_id');
                // Both Super Admin and Energy Authority can create general roles (operator_id = null)
                // or operator-specific roles (operator_id = specific operator)
                $updateData['operator_id'] = $operatorId === '' || $operatorId === null ? null : $operatorId;
            }
        }

        $role->update($updateData);

        // تحديث الصلاحيات
        if ($request->has('permissions')) {
            $permissionIds = $request->validated('permissions');
            
            // التحقق من الصلاحيات - منع منح صلاحيات النظام
            if (($user->isAdmin() || $user->isCompanyOwner()) && !empty($permissionIds)) {
                $systemPermissions = $this->getSystemPermissions();
                $systemPermissionIds = Permission::whereIn('name', $systemPermissions)->pluck('id')->toArray();
                $permissionIds = array_diff($permissionIds, $systemPermissionIds);
            }
            
            // Admin لا يمكنه تحديث الأدوار النظامية
            if ($user->isAdmin() && $role->is_system) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'لا يمكنك تحديث الأدوار النظامية.');
            }
            
            $role->permissions()->sync($permissionIds);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم تحديث الدور بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $role);

        // منع حذف الأدوار النظامية
        if ($role->is_system) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الدور النظامي.',
                ], 403);
            }

            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف الدور النظامي.');
        }

        // التحقق من وجود مستخدمين بهذا الدور
        if ($role->users()->count() > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين.',
                ], 403);
            }

            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين.');
        }

        $role->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الدور بنجاح.',
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم حذف الدور بنجاح.');
    }

    /**
     * Get list of system permissions that cannot be granted by Admin, Energy Authority, or Company Owner
     * Note: operators.approve is not included here because Admin and Energy Authority can have it
     */
    private function getSystemPermissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'operators.view',
            'operators.create',
            'operators.update',
            'operators.delete',
            // Note: operators.approve is allowed for Admin and Energy Authority, so not included here
            'permissions.manage',
            'settings.view',
            'settings.update',
            'constants.view',
            'constants.create',
            'constants.update',
            'constants.delete',
            'logs.view',
            'logs.clear',
            'logs.download',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ];
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
}
