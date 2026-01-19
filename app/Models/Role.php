<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'label',
        'description',
        'is_system',
        'order',
        'operator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps();
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function operator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Get custom roles (non-system roles) for a specific operator
     */
    public static function getCustomRolesForOperator(int $operatorId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('operator_id', $operatorId)
            ->where('is_system', false)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get general custom roles (not linked to specific operator) - created by Energy Authority
     */
    public static function getGeneralCustomRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereNull('operator_id')
            ->where('is_system', false)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all custom roles available for a user (based on their authority)
     * Used when creating/editing users to show available roles
     */
    public static function getAvailableCustomRoles(\App\Models\User $user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->isSuperAdmin()) {
            // SuperAdmin can see all custom roles (general and operator-specific)
            return static::where('is_system', false)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        if ($user->isAdmin()) {
            // Admin can only see general roles (operator_id = null) created by Admin or Super Admin
            return static::where('is_system', false)
                ->whereNull('operator_id')
                ->where(function($q) {
                    $q->whereNull('created_by')
                      ->orWhereHas('creator', function($q2) {
                          $q2->whereIn('role', ['super_admin', 'admin']);
                      });
                })
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        if ($user->isEnergyAuthority()) {
            // Energy Authority can see:
            // 1. General roles they created (operator_id = null)
            // 2. Operator-specific roles they created (operator_id = specific operator)
            // 3. General roles created by Super Admin or Admin (for reference when creating users)
            return static::where('is_system', false)
                ->where(function($q) use ($user) {
                    // Roles created by Energy Authority (general or operator-specific)
                    $q->where('created_by', $user->id)
                      // General roles created by Super Admin or Admin (for reference)
                      ->orWhere(function($q2) {
                          $q2->whereNull('operator_id')
                             ->where(function($q3) {
                                 $q3->whereNull('created_by')
                                    ->orWhereHas('creator', function($q4) {
                                        $q4->whereIn('role', ['super_admin', 'admin']);
                                    });
                             });
                      });
                })
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Company Owner can ONLY see and assign roles they created for their operator
                // No system roles, no general roles, no roles from Energy Authority, no roles from other operators
                return static::where('is_system', false)
                    ->where('operator_id', $operator->id)
                    ->where('created_by', $user->id)
                    ->orderBy('order')
                    ->orderBy('name')
                    ->get();
            }
        }

        return collect();
    }
}
