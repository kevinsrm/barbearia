<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Instalações antigas podiam já ter a tabela `usuarios` quando a migration
     * inicial foi executada. Como ela só cria a tabela quando ainda não existe,
     * essas bases não receberam todas as colunas esperadas pela aplicação.
     */
    public function up(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        $adicionar = [
            'nome' => ! Schema::hasColumn('usuarios', 'nome'),
            'role' => ! Schema::hasColumn('usuarios', 'role'),
            'ativo' => ! Schema::hasColumn('usuarios', 'ativo'),
            'email_verified_at' => ! Schema::hasColumn('usuarios', 'email_verified_at'),
            'ultimo_acesso' => ! Schema::hasColumn('usuarios', 'ultimo_acesso'),
            'remember_token' => ! Schema::hasColumn('usuarios', 'remember_token'),
        ];

        if (in_array(true, $adicionar, true)) {
            Schema::table('usuarios', function (Blueprint $table) use ($adicionar) {
                if ($adicionar['nome']) {
                    // Nullable para permitir a alteração em tabelas que já têm dados.
                    $table->string('nome')->nullable();
                }

                if ($adicionar['role']) {
                    $table->string('role', 30)->default('cliente')->index();
                }

                if ($adicionar['ativo']) {
                    $table->boolean('ativo')->default(true);
                }

                if ($adicionar['email_verified_at']) {
                    $table->timestamp('email_verified_at')->nullable();
                }

                if ($adicionar['ultimo_acesso']) {
                    $table->timestamp('ultimo_acesso')->nullable();
                }

                if ($adicionar['remember_token']) {
                    $table->rememberToken();
                }
            });
        }

        $this->preencherNomesAusentes();
    }

    /** Preenche o nome dos usuários antigos com a pessoa vinculada ou com o e-mail. */
    protected function preencherNomesAusentes(): void
    {
        if (! Schema::hasColumn('usuarios', 'nome')) {
            return;
        }

        $temPessoaId = Schema::hasColumn('usuarios', 'pessoa_id');
        $temNomeLegado = Schema::hasColumn('usuarios', 'name');
        $temEmail = Schema::hasColumn('usuarios', 'email');
        $temNomePessoa = Schema::hasTable('pessoas') && Schema::hasColumn('pessoas', 'nome');

        $colunas = ['id', 'nome'];

        if ($temPessoaId) {
            $colunas[] = 'pessoa_id';
        }

        if ($temEmail) {
            $colunas[] = 'email';
        }

        if ($temNomeLegado) {
            $colunas[] = 'name';
        }

        DB::table('usuarios')
            ->select(array_values(array_unique($colunas)))
            ->where(function ($query) {
                $query->whereNull('nome')->orWhere('nome', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($usuarios) use ($temPessoaId, $temNomeLegado, $temEmail, $temNomePessoa) {
                foreach ($usuarios as $usuario) {
                    $nome = $temNomeLegado ? trim((string) ($usuario->name ?? '')) : '';

                    if ($nome === '' && $temPessoaId && $temNomePessoa && $usuario->pessoa_id) {
                        $nome = trim((string) (DB::table('pessoas')
                            ->where('id', $usuario->pessoa_id)
                            ->value('nome') ?? ''));
                    }

                    if ($nome === '' && $temEmail) {
                        $nome = trim((string) explode('@', (string) ($usuario->email ?? ''), 2)[0]);
                    }

                    if ($nome === '') {
                        $nome = 'Usuário '.$usuario->id;
                    }

                    DB::table('usuarios')
                        ->where('id', $usuario->id)
                        ->update(['nome' => mb_substr($nome, 0, 255)]);
                }
            });
    }

    public function down(): void
    {
        // Não removemos as colunas no rollback: esta correção também é aplicada
        // em bases onde algumas delas já existiam, e removê-las poderia causar
        // perda de dados.
    }
};
