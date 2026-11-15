<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Busca pelo e-mail ou pelo nome de usuário.
     */
    public function findByLogin(string $login): ?User;
}
