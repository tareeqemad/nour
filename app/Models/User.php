<?php

namespace App\Models;

use App\Models\Role as RoleModel;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * الصلاحيات المقيدة (لا يمكن للمشغل الوصول إليها أبداً)
     */
    private const RESTRICTED_PERMISSIONS = [
        'settings.view',
        'settings.update',
        'constants.view',
        'constants.create',
        'constants.update',
        'constants.delete',
        'logs.view',
        'logs.clear',
        'logs.download',
    ];

    /**
     * الصلاحيات المسموح بها للمشغل قبل الموافقة
     */
    private const PRE_APPROVAL_PERMISSIONS = [
        'guide.view',
        'operators.view',
        'generators.view',
        'generators.create',
        'generation_units.view',
        'generation_units.create',
        'operation_logs.view',
        'fuel_efficiencies.view',
        'maintenance_records.view',
        'compliance_safeties.view',
        'electricity_tariff_prices.view',
    ];

    /**
     * الصلاحيات الافتراضية للأدمن (إذا لم يكن لديه roleModel)
     */
    private const ADMIN_FALLBACK_PERMISSIONS = [
        'operators.view',
        'generators.view',
        'generation_units.view',
        'operation_logs.view',
        'fuel_efficiencies.view',
        'maintenance_records.view',
        'compliance_safeties.view',
        'electricity_tariff_prices.view',
        'guide.view',
    ];

    /**
     * الصلاحيات المستثناة للأدمن عند تعيين الصلاحيات الافتراضية
     */
    private const ADMIN_EXCLUDED_PERMISSIONS = [
        'settings.view',
        'settings.update',
        'welcome_messages.view',
        'welcome_messages.update',
        'logs.view',
        'logs.clear',
        'logs.download',
        'constants.view',
        'constants.create',
        'constants.update',
        'constants.delete',
    ];

    /**
     * الصلاحيات المستثناة لسلطة الطاقة عند تعيين الصلاحيات الافتراضية
     */
    private const ENERGY_AUTHORITY_EXCLUDED_PERMISSIONS = [
        'settings.view',
        'settings.update',
        'logs.view',
        'logs.clear',
        'logs.download',
        'constants.view',
        'constants.create',
        'constants.update',
        'constants.delete',
        'welcome_messages.view',
        'welcome_messages.update',
    ];

    /**
     * صلاحيات المشغل بعد الموافقة
     */
    private const COMPANY_OWNER_PERMISSIONS = [
        'guide.view',
        'operators.view',
        'operators.update',
        'generators.view',
        'generators.create',
        'generators.update',
        'generation_units.view',
        'generation_units.create',
        'generation_units.update',
        'generation_units.delete',
        'operation_logs.view',
        'operation_logs.create',
        'operation_logs.update',
        'fuel_efficiencies.view',
        'fuel_efficiencies.create',
        'fuel_efficiencies.update',
        'maintenance_records.view',
        'maintenance_records.create',
        'maintenance_records.update',
        'compliance_safeties.view',
        'compliance_safeties.create',
        'compliance_safeties.update',
        'electricity_tariff_prices.view',
        'electricity_tariff_prices.create',
        'electricity_tariff_prices.update',
        'users.view',
        'users.create',
        'users.update',
        'permissions.manage',
        'roles.view',
        'roles.create',
        'roles.update',
    ];

    protected $fillable = [
        'name', // اسم المستخدم (العربي) - مثل: "أحمد محمد"
        'name_en', // اسم المستخدم (بالإنجليزية) - مثل: "Ahmad Mohammed" - يستخدم لتوليد username تلقائياً
        'email',
        'username', // اسم المستخدم للدخول - مثل: "sp_ahmad" أو "ad_mohammed"
        'password',
        'phone', // رقم جوال المستخدم
        'role',
        'role_id',
        'status', // active, inactive, suspended
        'suspended_at', // تاريخ التعطيل/الحظر
        'suspended_reason', // سبب التعطيل/الحظر
        'suspended_by', // المستخدم الذي قام بالتعطيل/الحظر
        'last_activity', // آخر نشاط للمستخدم
        'password_reset_at', // تاريخ ووقت آخر إعادة تعيين لكلمة المرور
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'suspended_at' => 'datetime',
            'last_activity' => 'datetime',
            'password_reset_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'status' => 'active',
    ];

    public function ownedOperators(): HasMany
    {
        return $this->hasMany(Operator::class, 'owner_id');
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'operator_user')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withTimestamps();
    }

    public function revokedPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission_revoked')
            ->withTimestamps();
    }

    public function permissionAuditLogs(): HasMany
    {
        return $this->hasMany(PermissionAuditLog::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function suspendedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    /**
     * Check if user is suspended/banned
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended' && $this->suspended_at !== null;
    }

    /**
     * Check if user can login (not suspended and status is active)
     */
    public function canLogin(): bool
    {
        // System user (platform_rased) cannot login
        if ($this->isSystemUser()) {
            return false;
        }

        if ($this->status === 'suspended') {
            return false;
        }

        return $this->status === 'active';
    }

    /**
     * Check if this is the system user (منصة نور) used for system messages
     */
    public function isSystemUser(): bool
    {
        return $this->username === 'platform_rased' || $this->email === 'platform@gazarased.com';
    }

    public function isSuperAdmin(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'super_admin';
        }

        return $this->role === Role::SuperAdmin;
    }

    public function isCompanyOwner(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'company_owner';
        }

        return $this->role === Role::CompanyOwner;
    }

    public function isEmployee(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'employee';
        }

        return $this->role === Role::Employee;
    }

    public function isTechnician(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'technician';
        }

        return $this->role === Role::Technician;
    }

    public function isCivilDefense(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'civil_defense';
        }

        return $this->role === Role::CivilDefense;
    }

    public function isAdmin(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'admin';
        }

        return $this->role === Role::Admin;
    }

    public function isEnergyAuthority(): bool
    {
        if ($this->role_id) {
            return $this->roleModel?->name === 'energy_authority';
        }

        return $this->role === Role::EnergyAuthority;
    }

    /**
     * Get the role label (Arabic name) for the user
     */
    public function getRoleLabel(): string
    {
        // If user has a custom role (roleModel), use its label
        if ($this->roleModel) {
            return $this->roleModel->label;
        }

        // Otherwise, use system role labels
        return match($this->role) {
            Role::SuperAdmin => 'مدير النظام',
            Role::Admin => 'مدير',
            Role::EnergyAuthority => 'سلطة الطاقة',
            Role::CompanyOwner => 'مشغل',
            Role::Employee => 'موظف',
            Role::Technician => 'فني',
            Role::CivilDefense => 'دفاع مدني',
            default => 'غير معروف',
        };
    }

    /**
     * الحصول على label الدور من value
     */
    public static function getRoleLabelFromValue(string $roleValue): string
    {
        return match($roleValue) {
            Role::SuperAdmin->value => 'مدير النظام',
            Role::Admin->value => 'مدير',
            Role::EnergyAuthority->value => 'سلطة الطاقة',
            Role::CompanyOwner->value => 'مشغل',
            Role::Employee->value => 'موظف',
            Role::Technician->value => 'فني',
            Role::CivilDefense->value => 'دفاع مدني',
            default => 'غير معروف',
        };
    }

    public function ownsOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin() || $this->ownedOperators()->where('id', $operator->id)->exists();
    }

    public function belongsToOperator(Operator $operator): bool
    {
        return $this->isSuperAdmin()
            || $this->ownsOperator($operator)
            || $this->operators()->where('operators.id', $operator->id)->exists();
    }

    /**
     * Check if user has a custom role (not a system role)
     * Custom roles are defined by Energy Authority or Company Owner
     */
    public function hasCustomRole(): bool
    {
        if (! $this->roleModel) {
            return false;
        }

        // System roles are: super_admin, admin, energy_authority, company_owner
        $systemRoles = ['super_admin', 'admin', 'energy_authority', 'company_owner'];

        return ! in_array($this->roleModel->name, $systemRoles, true);
    }

    /**
     * Check if user's custom role is linked to an operator
     */
    public function hasOperatorLinkedCustomRole(): bool
    {
        if (! $this->hasCustomRole() || ! $this->roleModel) {
            return false;
        }

        return $this->roleModel->operator_id !== null;
    }

    /**
     * Get the affiliated operator for this user
     * Works for: CompanyOwner, Employee, Technician, and Custom Roles linked to operator
     */
    public function getAffiliatedOperator(): ?Operator
    {
        // CompanyOwner - gets operator they own
        if ($this->isCompanyOwner()) {
            return $this->ownedOperators()->first();
        }

        // Employee or Technician - gets operator they're linked to
        if ($this->isEmployee() || $this->isTechnician()) {
            return $this->operators()->first();
        }

        // Custom role linked to operator - gets operator from role
        if ($this->hasOperatorLinkedCustomRole() && $this->roleModel) {
            return $this->roleModel->operator;
        }

        return null;
    }

    /**
     * Check if user is affiliated with an operator (any type)
     */
    public function isAffiliatedWithOperator(): bool
    {
        return $this->getAffiliatedOperator() !== null;
    }

    /**
     * Check if operator is approved and active
     * Required for Company Owner to have full access to all permissions
     */
    public function hasApprovedOperator(): bool
    {
        // SuperAdmin and Admin always have access
        if ($this->isSuperAdmin() || $this->isAdmin() || $this->isEnergyAuthority()) {
            return true;
        }

        // Company Owner needs approved operator
        if ($this->isCompanyOwner()) {
            $operator = $this->ownedOperators()->first();

            return $operator && $operator->is_approved && $operator->status === 'active';
        }

        // Users with custom roles linked to operator: check if operator is approved
        if ($this->hasOperatorLinkedCustomRole()) {
            $operator = $this->roleModel->operator;

            return $operator && $operator->is_approved && $operator->status === 'active';
        }

        // Users with custom roles not linked to operator (general roles from Energy Authority)
        if ($this->hasCustomRole() && ! $this->hasOperatorLinkedCustomRole()) {
            return true; // General custom roles don't need operator approval
        }

        return false;
    }

    /**
     * Check if user has a specific permission
     * 
     * BEST PRACTICE: Uses loaded relationships to avoid N+1 queries
     * Caching is handled at the application level if needed
     * 
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        // SuperAdmin has all permissions (including settings, constants, logs)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Company Owner: Check if operator is approved before granting full access
        // Settings, constants, and logs are always restricted for Company Owner
        if ($this->isCompanyOwner() && in_array($permissionName, self::RESTRICTED_PERMISSIONS, true)) {
            return false; // Company Owner never has access to settings, constants, logs
        }

        // Company Owner needs approved operator for full access to their permissions
        if ($this->isCompanyOwner() && ! $this->hasApprovedOperator()) {
            // Before approval, Company Owner has limited access
            return in_array($permissionName, self::PRE_APPROVAL_PERMISSIONS);
        }

        // Load relationships if not already loaded (eager loading optimization)
        $this->loadMissing(['permissions', 'revokedPermissions', 'roleModel.permissions']);

        // Check revoked permissions first (fastest check using loaded collection)
        $revokedPermissionNames = $this->revokedPermissions->pluck('name')->toArray();
        if (in_array($permissionName, $revokedPermissionNames, true)) {
            return false;
        }

        // Check direct user permissions (assigned individually) - using loaded collection
        $directPermissionNames = $this->permissions->pluck('name')->toArray();
        if (in_array($permissionName, $directPermissionNames, true)) {
            return true;
        }

        // If user has roleModel, use role permissions
        if ($this->roleModel) {
            $this->roleModel->loadMissing('permissions');
            if ($this->roleModel->hasPermission($permissionName)) {
                // Already checked revoked above, so if we reach here, permission is valid
                return true;
            }
        }

        // Fallback for Admin (if no roleModel)
        if ($this->isAdmin() && ! $this->roleModel) {
            return in_array($permissionName, self::ADMIN_FALLBACK_PERMISSIONS);
        }

        return false;
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        // SuperAdmin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Company Owner: Check if operator is approved and restrictions apply
        if ($this->isCompanyOwner()) {
            // Check if any requested permission is restricted
            $hasRestricted = ! empty(array_intersect($permissionNames, self::RESTRICTED_PERMISSIONS));
            if ($hasRestricted) {
                return false; // Company Owner never has access to settings, constants, logs
            }

            // Company Owner needs approved operator for full access
            if (! $this->hasApprovedOperator()) {
                return ! empty(array_intersect($permissionNames, self::PRE_APPROVAL_PERMISSIONS));
            }
        }

        // If user has roleModel, use role permissions first
        if ($this->roleModel) {
            foreach ($permissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    if (! $this->revokedPermissions()->where('name', $permissionName)->exists()) {
                        return true;
                    }
                }
            }
        }

        // Fallback for Admin (if no roleModel)
        if ($this->isAdmin() && ! $this->roleModel) {
            return ! empty(array_intersect($permissionNames, self::ADMIN_FALLBACK_PERMISSIONS));
        }

        $revokedPermissionNames = $this->revokedPermissions()
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->toArray();

        $availablePermissionNames = array_diff($permissionNames, $revokedPermissionNames);

        if (empty($availablePermissionNames)) {
            return false;
        }

        if ($this->permissions()->whereIn('name', $availablePermissionNames)->exists()) {
            return true;
        }

        if ($this->roleModel) {
            foreach ($availablePermissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissionNames): bool
    {
        // SuperAdmin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Company Owner: Check if operator is approved and restrictions apply
        if ($this->isCompanyOwner()) {
            // Check if any requested permission is restricted
            $hasRestricted = ! empty(array_intersect($permissionNames, self::RESTRICTED_PERMISSIONS));
            if ($hasRestricted) {
                return false; // Company Owner never has access to settings, constants, logs
            }

            // Company Owner needs approved operator for full access
            if (! $this->hasApprovedOperator()) {
                $intersection = array_intersect($permissionNames, self::PRE_APPROVAL_PERMISSIONS);
                return count($permissionNames) === count($intersection);
            }
        }

        // If user has roleModel, use role permissions first
        if ($this->roleModel) {
            $rolePermissions = [];
            foreach ($permissionNames as $permissionName) {
                if ($this->roleModel->hasPermission($permissionName)) {
                    if (! $this->revokedPermissions()->where('name', $permissionName)->exists()) {
                        $rolePermissions[] = $permissionName;
                    }
                }
            }

            return count($permissionNames) === count($rolePermissions);
        }

        // Fallback for Admin (if no roleModel)
        if ($this->isAdmin() && ! $this->roleModel) {
            $intersection = array_intersect($permissionNames, self::ADMIN_FALLBACK_PERMISSIONS);
            return count($permissionNames) === count($intersection);
        }

        $revokedPermissionNames = $this->revokedPermissions()
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->toArray();

        $availablePermissionNames = array_diff($permissionNames, $revokedPermissionNames);

        if (count($availablePermissionNames) !== count($permissionNames)) {
            return false;
        }

        $userPermissions = $this->permissions()
            ->whereIn('name', $availablePermissionNames)
            ->pluck('name')
            ->toArray();

        if ($this->roleModel) {
            $rolePermissions = $this->roleModel->permissions()
                ->whereIn('name', $availablePermissionNames)
                ->pluck('name')
                ->toArray();

            $userPermissions = array_unique(array_merge($userPermissions, $rolePermissions));
        }

        return count($availablePermissionNames) === count($userPermissions);
    }

    public function getAvatarUrlAttribute(): string
    {
        if (isset($this->attributes['avatar']) && $this->attributes['avatar']) {
            return asset('storage/'.$this->attributes['avatar']);
        }

        // Use default profile image from local assets
        return asset('assets/admin/images/profile/profile.png');
    }

    public function getRoleNameAttribute(): string
    {
        if ($this->roleModel) {
            return $this->roleModel->label;
        }

        return match ($this->role ?? null) {
            Role::SuperAdmin => 'مدير النظام',
            Role::Admin => 'مدير سلطة الطاقة',
            Role::CompanyOwner => 'صاحب مشغل',
            Role::Employee => 'موظف',
            Role::Technician => 'فني',
            Role::CivilDefense => 'دفاع مدني',
            default => 'بدون صلاحية',
        };
    }

    /**
     * إنشاء 3 رسائل افتراضية للمستخدم الجديد
     */
    public function createDefaultMessages(): void
    {
        // الحصول على Super Admin لإرسال الرسائل منه
        $superAdmin = User::where('role', Role::SuperAdmin)->first();

        if (! $superAdmin) {
            return;
        }

        // الحصول على المشغل المرتبط بالمستخدم (إن وجد)
        $operator = null;
        if ($this->isCompanyOwner()) {
            $operator = $this->ownedOperators()->first();
        } elseif ($this->isEmployee() || $this->isTechnician() || $this->isCivilDefense()) {
            $operator = $this->operators()->first();
        }

        // الحصول على الرسائل الترحيبية النشطة من قاعدة البيانات
        $welcomeMessages = \App\Models\WelcomeMessage::getActiveMessages();

        foreach ($welcomeMessages as $welcomeMessage) {
            // استبدال المتغيرات في الرسالة
            $body = str_replace('{name}', $this->name, $welcomeMessage->body);
            $subject = str_replace('{name}', $this->name, $welcomeMessage->subject);

            Message::create([
                'sender_id' => $superAdmin->id,
                'receiver_id' => $this->id,
                'operator_id' => $operator?->id,
                'subject' => $subject,
                'body' => $body,
                'type' => 'admin_to_operator',
                'is_read' => false,
                'read_at' => null,
            ]);
        }
    }

    /**
     * تعيين الصلاحيات تلقائياً عند إنشاء المستخدم حسب الدور
     * 
     * - SuperAdmin: كل الصلاحيات
     * - Admin: كل الصلاحيات ما عدا: settings.*, welcome_messages.*, logs.*, constants.*
     * - EnergyAuthority: كل الصلاحيات ما عدا: settings.*, logs.*, constants.*, welcome_messages.*
     * - CompanyOwner: ما يخصه + permissions.manage + roles.view/create/update
     */
    public function assignDefaultPermissions(): void
    {
        // إذا كان المستخدم لديه custom role، لا نعطيه صلاحيات افتراضية
        // الصلاحيات تأتي من الدور المخصص
        if ($this->hasCustomRole()) {
            return;
        }

        $allPermissions = Permission::all();
        $permissionIds = [];

        if ($this->isSuperAdmin()) {
            // SuperAdmin: كل الصلاحيات
            $permissionIds = $allPermissions->pluck('id')->toArray();
            
        } elseif ($this->isAdmin()) {
            // Admin: كل الصلاحيات ما عدا المستثناة
            $permissionIds = $allPermissions
                ->reject(fn ($permission) => in_array($permission->name, self::ADMIN_EXCLUDED_PERMISSIONS, true))
                ->pluck('id')
                ->toArray();
                
        } elseif ($this->isEnergyAuthority()) {
            // EnergyAuthority: كل الصلاحيات ما عدا المستثناة
            $permissionIds = $allPermissions
                ->reject(fn ($permission) => in_array($permission->name, self::ENERGY_AUTHORITY_EXCLUDED_PERMISSIONS, true))
                ->pluck('id')
                ->toArray();
                
        } elseif ($this->isCompanyOwner()) {
            // CompanyOwner: صلاحياته المحددة فقط
            $permissionIds = $allPermissions
                ->filter(fn ($permission) => in_array($permission->name, self::COMPANY_OWNER_PERMISSIONS, true))
                ->pluck('id')
                ->toArray();
        }

        // تعيين الصلاحيات مباشرة للمستخدم
        if (!empty($permissionIds)) {
            $this->permissions()->sync($permissionIds);
        }
    }
}
