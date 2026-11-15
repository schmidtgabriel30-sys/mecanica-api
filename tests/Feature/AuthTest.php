<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_usuario_e_retorna_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Maria Souza',
            'email' => 'maria@email.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', ['email' => 'maria@email.com', 'is_admin' => false]);
    }

    public function test_cadastro_nao_permite_virar_admin(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Espertinho',
            'email' => 'esperto@email.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'is_admin' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'esperto@email.com', 'is_admin' => false]);
    }

    public function test_cadastro_valida_campos(): void
    {
        $this->postJson('/api/register', ['email' => 'invalido'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_com_credenciais_corretas(): void
    {
        User::factory()->create(['email' => 'joao@email.com', 'password' => '12345678']);

        $this->postJson('/api/login', ['email' => 'joao@email.com', 'password' => '12345678'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_admin_fixo_entra_com_usuario_e_senha_admina(): void
    {
        $this->seed(AdminSeeder::class);

        $this->postJson('/api/login', ['login' => 'AdminA', 'password' => 'AdminA'])
            ->assertOk()
            ->assertJsonPath('user.is_admin', true)
            ->assertJsonPath('user.username', 'AdminA');
    }

    public function test_admin_seeder_pode_rodar_de_novo_sem_duplicar(): void
    {
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_aceita_o_campo_login_com_email(): void
    {
        User::factory()->create(['email' => 'joao@email.com', 'password' => '12345678']);

        $this->postJson('/api/login', ['login' => 'joao@email.com', 'password' => '12345678'])
            ->assertOk()->assertJsonPath('user.is_admin', false);
    }

    public function test_login_com_senha_errada_retorna_401(): void
    {
        User::factory()->create(['email' => 'joao@email.com', 'password' => '12345678']);

        $this->postJson('/api/login', ['email' => 'joao@email.com', 'password' => 'errada123'])
            ->assertUnauthorized();
    }

    public function test_rotas_protegidas_exigem_token(): void
    {
        $this->getJson('/api/veiculos')->assertUnauthorized();
        $this->getJson('/api/agendamentos')->assertUnauthorized();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_logout_revoga_o_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
