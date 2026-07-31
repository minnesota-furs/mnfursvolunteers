<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function manage(User $user, Department $department): bool
    {
        return $user->isAdmin()
            || $department->heads()->whereKey($user->getKey())->exists();
    }
}
