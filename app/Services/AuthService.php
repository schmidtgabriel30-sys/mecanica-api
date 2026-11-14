<?php

namespace App\Services;

use App\Exceptions\RegraDeNegocioException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Cadastro, login e logout com tokens do Laravel Sanctum.
 */
class AuthService
{
    private const NOME_DO_TOKEN = 'app';

    public function __construct(private readonly UserRepositoryInterface $users) {}

    /**
     * @return array{token: string, user: User}
     */
    public function registrar(array $dados): array
    {
        /** @var User $user */
        $user = $this->users->create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
        ]);

        return $this->sessao($user);
    }

    /**
     * @return array{token: string, user: User}
     */
    public function login(string $email, string $senha): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($senha, $user->password)) {
            throw new RegraDeNegocioException('Credenciais inválidas', 401);
        }

        return $this->sessao($user);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * @return array{token: string, user: User}
     */
    private function sessao(User $user): array
    {
        return [
            'token' => $user->createToken(self::NOME_DO_TOKEN)->plainTextToken,
            'user' => $user,
        ];
    }
}
