<?php

namespace Tests\Feature;

use App\Models\Pessoa;
use App\Models\Usuario;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_admin_seeder_creates_a_linked_admin_and_is_idempotent(): void
    {
        $this->seed(AdminUserSeeder::class);

        $usuario = Usuario::with('pessoa')->where('email', 'admin@eu.com')->firstOrFail();

        $this->assertSame(Usuario::ROLE_ADMIN, $usuario->role);
        $this->assertTrue($usuario->ativo);
        $this->assertSame($usuario->pessoa_id, $usuario->pessoa->id);
        $this->assertSame('Administrador', $usuario->pessoa->nome);
        $this->assertTrue(Hash::check('12345678', $usuario->password));

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('pessoas', 1);
        $this->assertDatabaseCount('usuarios', 1);
    }

    public function test_default_database_seeder_promotes_existing_configured_user_to_admin(): void
    {
        $pessoa = Pessoa::create([
            'nome' => 'Cliente anterior',
            'tipo_pessoa' => 'cliente',
            'email' => 'admin@barbearia.com',
            'ativo' => false,
        ]);

        $usuario = Usuario::create([
            'pessoa_id' => $pessoa->id,
            'nome' => 'Cliente anterior',
            'email' => 'admin@barbearia.com',
            'password' => 'senha-anterior',
            'role' => Usuario::ROLE_CLIENTE,
            'ativo' => false,
        ]);

        $this->runWithTemporaryEnvironment([
            'ADMIN_EMAIL' => 'admin@barbearia.com',
            'ADMIN_NOME' => 'Administrador',
            'ADMIN_PASSWORD' => 'admin123',
            'DEMO_DATA' => 'false',
        ], function () {
            $this->seed(DatabaseSeeder::class);
        });

        $usuario->refresh();

        $this->assertSame(Usuario::ROLE_ADMIN, $usuario->role);
        $this->assertTrue($usuario->ativo);
        $this->assertSame($pessoa->id, $usuario->pessoa_id);
        $this->assertTrue(Hash::check('senha-anterior', $usuario->password));
        $this->assertDatabaseCount('usuarios', 1);
    }

    /** Set test environment values and restore the process state afterwards. */
    private function runWithTemporaryEnvironment(array $variables, callable $callback): mixed
    {
        $previous = [];

        foreach ($variables as $name => $value) {
            $previous[$name] = [
                'process' => getenv($name),
                'env_exists' => array_key_exists($name, $_ENV),
                'env' => $_ENV[$name] ?? null,
                'server_exists' => array_key_exists($name, $_SERVER),
                'server' => $_SERVER[$name] ?? null,
            ];

            putenv($name.'='.$value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        try {
            return $callback();
        } finally {
            foreach ($previous as $name => $values) {
                if ($values['process'] === false) {
                    putenv($name);
                } else {
                    putenv($name.'='.$values['process']);
                }

                if ($values['env_exists']) {
                    $_ENV[$name] = $values['env'];
                } else {
                    unset($_ENV[$name]);
                }

                if ($values['server_exists']) {
                    $_SERVER[$name] = $values['server'];
                } else {
                    unset($_SERVER[$name]);
                }
            }
        }
    }
}
