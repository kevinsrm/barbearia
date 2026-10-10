<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * A `usuarios` table created outside this system (an older release, phpMyAdmin, a
 * tutorial dump) usually keeps the stock Laravel `name` column as NOT NULL with no
 * default. The application only writes `nome`, so MySQL rejects every INSERT with
 * "General error: 1364 Field 'name' doesn't have a default value" — exactly what the
 * public registration form, the seeder and the installer hit on such a database.
 */
class LegacyUsuariosSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = 'migrations/2026_10_10_000003_remove_legacy_name_column_from_usuarios_table.php';

    public function test_insert_without_name_works_once_the_legacy_column_is_repaired(): void
    {
        // The reported production state: the previous repair migration already added
        // `nome`, but the legacy mandatory `name` column is still there.
        $this->addLegacyNameColumn();

        $this->assertTrue(
            $this->insertFailsWithoutName(),
            'Expected the legacy NOT NULL name column to reject an insert before the repair.',
        );

        $this->runLegacyNameMigration();

        $usuario = Usuario::create([
            'nome' => 'admin',
            'email' => 'admin@barbearia.com',
            'password' => 'senha123',
            'role' => Usuario::ROLE_CLIENTE,
            'ativo' => true,
        ]);

        $this->assertFalse(Schema::hasColumn('usuarios', 'name'));
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nome' => 'admin',
            'email' => 'admin@barbearia.com',
        ]);
    }

    public function test_public_registration_succeeds_on_a_repaired_legacy_table(): void
    {
        $this->addLegacyNameColumn();
        $this->runLegacyNameMigration();

        $response = $this->post(route('register.post'), [
            'nome' => 'admin',
            'email' => 'admin@barbearia.com',
            'telefone' => '11999999999',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertRedirect(route('site.agendamento'));
        $this->assertDatabaseHas('usuarios', [
            'email' => 'admin@barbearia.com',
            'nome' => 'admin',
            'role' => Usuario::ROLE_CLIENTE,
        ]);
    }

    public function test_fully_legacy_table_gets_nome_created_and_backfilled_from_name(): void
    {
        $this->makeUsuariosFullyLegacy();

        DB::table('usuarios')->insert([
            'name' => 'Cliente antigo',
            'email' => 'antigo@exemplo.com',
            'password' => 'segredo',
            'role' => 'cliente',
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runLegacyNameMigration();

        $this->assertFalse(Schema::hasColumn('usuarios', 'name'));
        $this->assertTrue(Schema::hasColumn('usuarios', 'nome'));
        $this->assertDatabaseHas('usuarios', [
            'email' => 'antigo@exemplo.com',
            'nome' => 'Cliente antigo',
        ]);
    }

    public function test_existing_nome_is_kept_when_only_the_legacy_column_is_removed(): void
    {
        $this->addLegacyNameColumn();

        DB::table('usuarios')->insert([
            'nome' => 'Nome atual',
            'name' => 'Nome antigo',
            'email' => 'atual@exemplo.com',
            'password' => 'segredo',
            'role' => 'admin',
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runLegacyNameMigration();

        $this->assertFalse(Schema::hasColumn('usuarios', 'name'));
        $this->assertDatabaseHas('usuarios', [
            'email' => 'atual@exemplo.com',
            'nome' => 'Nome atual',
        ]);
    }

    public function test_migration_is_idempotent(): void
    {
        $this->makeUsuariosFullyLegacy();

        $this->runLegacyNameMigration();
        $this->runLegacyNameMigration();

        $this->assertFalse(Schema::hasColumn('usuarios', 'name'));
        $this->assertTrue(Schema::hasColumn('usuarios', 'nome'));
    }

    /** Leaves `usuarios` as an old installation would: legacy `name`, no `nome`. */
    private function makeUsuariosFullyLegacy(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('nome');
        });

        $this->addLegacyNameColumn();
    }

    /** Adds the mandatory legacy column that triggers MySQL error 1364. */
    private function addLegacyNameColumn(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('name');
        });
    }

    private function runLegacyNameMigration(): void
    {
        $migration = require database_path(self::MIGRATION);

        $migration->up();
    }

    /** True when the database refuses an insert that omits the legacy `name` column. */
    private function insertFailsWithoutName(): bool
    {
        try {
            DB::table('usuarios')->insert([
                'nome' => 'admin',
                'email' => 'admin@barbearia.com',
                'password' => 'segredo',
                'role' => 'cliente',
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return false;
        } catch (QueryException) {
            return true;
        }
    }
}
