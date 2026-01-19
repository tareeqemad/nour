<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'label',
        'group',
        'group_label',
        'description',
        'order',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission')
            ->withTimestamps();
    }

    public function revokedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission_revoked')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withTimestamps();
    }
}
