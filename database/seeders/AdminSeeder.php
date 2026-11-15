<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cria (ou restaura) o administrador fixo do sistema.
 *
 * Usuário: AdminA · Senha: AdminA
 *
 * Usa updateOrCreate, então pode ser executado quantas vezes quiser:
 * php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public const USUARIO = 'AdminA';

    public const SENHA = 'AdminA';

    public function run(): void
    {
        User::updateOrCreate(
            ['username' => self::USUARIO],
            [
                'name' => 'Administrador',
                'email' => 'admin@aurum.com',
                'password' => Hash::make(self::SENHA),
            ],
        )->forceFill(['is_admin' => true])->save();
    }
}
