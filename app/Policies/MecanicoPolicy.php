<?php

namespace App\Policies;

use App\Models\Mecanico;
use App\Models\User;

class MecanicoPolicy
{
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Mecanico $mecanico): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Mecanico $mecanico): bool
    {
        return (bool) $user->is_admin;
    }
}
