<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Package;

class PackagePolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function view(User $user, Package $package)
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Package $package)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Package $package)
    {
        return $user->role === 'admin';
    }
}
