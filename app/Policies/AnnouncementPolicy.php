<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('announcements.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('announcements.view');
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('announcements.create');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('announcements.update');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }
        return $user->hasPermission('announcements.delete');
    }
}
