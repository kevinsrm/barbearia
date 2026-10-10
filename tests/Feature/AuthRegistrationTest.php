<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_person_client_and_user_and_logs_them_in(): void
    {
        $response = $this->post(route('register.post'), [
            'nome' => 'Cliente de Teste',
            'email' => 'CLIENTE@EXEMPLO.COM',
            'telefone' => '11999999999',
            'cpf' => '123.456.789-01',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertRedirect(route('site.agendamento'));
        $this->assertDatabaseHas('pessoas', [
            'nome' => 'Cliente de Teste',
            'email' => 'cliente@exemplo.com',
            'cpf' => '12345678901',
        ]);
        $this->assertDatabaseHas('clientes', ['status' => 'ativo']);

        $usuario = Usuario::with('pessoa')->where('email', 'cliente@exemplo.com')->firstOrFail();

        $this->assertSame(Usuario::ROLE_CLIENTE, $usuario->role);
        $this->assertTrue($usuario->ativo);
        $this->assertSame('Cliente de Teste', $usuario->pessoa->nome);
        $this->assertTrue(Hash::check('senha123', $usuario->password));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_registration_reuses_a_person_and_client_left_by_a_previous_failed_attempt(): void
    {
        $pessoa = Pessoa::create([
            'nome' => 'Cliente de Teste',
            'tipo_pessoa' => 'cliente',
            'telefone' => '11999999999',
            'email' => 'cliente@exemplo.com',
            'ativo' => true,
        ]);

        Cliente::create(['pessoa_id' => $pessoa->id, 'status' => 'ativo']);

        $response = $this->post(route('register.post'), [
            'nome' => 'Cliente de Teste',
            'email' => 'CLIENTE@EXEMPLO.COM',
            'telefone' => '11999999999',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertRedirect(route('site.agendamento'));
        $this->assertDatabaseCount('pessoas', 1);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('usuarios', 1);
        $this->assertDatabaseHas('usuarios', [
            'email' => 'cliente@exemplo.com',
            'pessoa_id' => $pessoa->id,
        ]);
    }

    public function test_legacy_users_table_gets_missing_columns_and_names_are_backfilled(): void
    {
        $pessoa = Pessoa::create([
            'nome' => 'Nome legado',
            'tipo_pessoa' => 'usuario',
            'email' => 'legado@exemplo.com',
            'ativo' => true,
        ]);

        $usuario = Usuario::create([
            'pessoa_id' => $pessoa->id,
            'nome' => 'Nome legado',
            'email' => 'legado@exemplo.com',
            'password' => 'senha-antiga',
            'role' => Usuario::ROLE_ADMIN,
            'ativo' => true,
        ]);

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('nome');
            $table->dropColumn('ultimo_acesso');
            $table->dropColumn('remember_token');
        });

        $migration = require database_path('migrations/2026_10_10_000002_repair_usuarios_legacy_schema.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('usuarios', 'nome'));
        $this->assertTrue(Schema::hasColumn('usuarios', 'ultimo_acesso'));
        $this->assertTrue(Schema::hasColumn('usuarios', 'remember_token'));
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nome' => 'Nome legado',
        ]);
    }
}
