<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Cria ou redefine explicitamente a conta administrativa de desenvolvimento.
 * Não é chamado automaticamente pelo DatabaseSeeder para evitar uma conta
 * com credenciais fixas sendo criada em instalações de produção.
 */
class AdminUserSeeder extends Seeder
{
    private const EMAIL = 'admin@eu.com';
    private const NOME = 'Administrador';
    private const SENHA = '12345678';

    public function run(): void
    {
        DB::transaction(function () {
            $pessoa = Pessoa::firstOrCreate(
                ['email' => self::EMAIL],
                [
                    'nome' => self::NOME,
                    'tipo_pessoa' => 'usuario',
                    'ativo' => true,
                ]
            );

            $pessoa->update([
                'nome' => self::NOME,
                'tipo_pessoa' => 'usuario',
                'ativo' => true,
            ]);

            Usuario::updateOrCreate(
                ['email' => self::EMAIL],
                [
                    'pessoa_id' => $pessoa->id,
                    'nome' => self::NOME,
                    'password' => self::SENHA,
                    'role' => Usuario::ROLE_ADMIN,
                    'ativo' => true,
                ]
            );
        });

        $this->command?->info('Administrador criado/atualizado: '.self::EMAIL);
    }
}
