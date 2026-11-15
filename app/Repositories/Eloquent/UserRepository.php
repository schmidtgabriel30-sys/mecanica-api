<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByLogin(string $login): ?User
    {
        return User::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }
}
