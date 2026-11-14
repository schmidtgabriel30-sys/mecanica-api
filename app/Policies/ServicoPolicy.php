<?php

namespace App\Policies;

use App\Models\Servico;
use App\Models\User;

class ServicoPolicy
{
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Servico $servico): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Servico $servico): bool
    {
        return (bool) $user->is_admin;
    }
}
