<?php

namespace App\Policies;

use App\Models\Agendamento;
use App\Models\User;

class AgendamentoPolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function view(User $user, Agendamento $agendamento): bool
    {
        return (int) $user->id === (int) $agendamento->user_id;
    }

    public function update(User $user, Agendamento $agendamento): bool
    {
        return $this->view($user, $agendamento);
    }

    public function delete(User $user, Agendamento $agendamento): bool
    {
        return $this->view($user, $agendamento);
    }
}
