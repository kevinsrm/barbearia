<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Bases criadas antes deste sistema (ou manualmente, pelo phpMyAdmin/cPanel) podem
 * já ter a tabela `usuarios` com a coluna `name` obrigatória e sem valor padrão —
 * o formato clássico do Laravel em inglês. O sistema grava `nome`, nunca `name`, e
 * o MySQL em modo estrito recusa todo INSERT com:
 *
 *     SQLSTATE[HY000]: General error: 1364 Field 'name' doesn't have a default value
 *
 * A migration de reparo anterior acrescentava as colunas que faltavam, mas mantinha
 * a `name` antiga intocada: o cadastro, o login e o instalador continuavam quebrados.
 * Aqui os nomes são copiados para `nome` e a coluna legada é removida.
 */
return new class extends Migration
{
    /**
     * Colunas que o sistema conhece em `usuarios`. Qualquer outra coluna obrigatória
     * e sem valor padrão que exista numa base antiga impede todos os INSERTs com o
     * mesmo erro 1364, então ela perde a obrigatoriedade mais abaixo.
     */
    private const COLUNAS_DO_SISTEMA = [
        'id',
        'pessoa_id',
        'nome',
        'email',
        'password',
        'role',
        'ativo',
        'email_verified_at',
        'ultimo_acesso',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        // A migration de reparo já cria `nome`; repetimos a garantia para que esta
        // correção funcione sozinha em bases onde ela não chegou a rodar.
        $nomeCriadoAqui = false;

        if (! Schema::hasColumn('usuarios', 'nome')) {
            Schema::table('usuarios', function (Blueprint $table) {
                // Nullable para permitir a alteração em tabelas que já têm dados.
                $table->string('nome')->nullable();
            });

            $nomeCriadoAqui = true;
        }

        $this->copiarNomesLegados($nomeCriadoAqui);
        $this->removerColunaNameLegada();
        $this->relaxarColunasLegadasObrigatorias();
    }

    /**
     * Copia o conteúdo da coluna legada `name` para `nome` antes de removê-la, para
     * que nenhum cadastro antigo perca o nome de exibição.
     */
    protected function copiarNomesLegados(bool $nomeCriadoAqui): void
    {
        if (! Schema::hasColumn('usuarios', 'name')) {
            // Sem coluna legada só há o que fazer se `nome` acabou de ser criada:
            // nesse caso os registros existentes ficaram sem nome.
            if ($nomeCriadoAqui) {
                $this->preencherNomesVazios();
            }

            return;
        }

        $temEmail = Schema::hasColumn('usuarios', 'email');
        $temPessoaId = Schema::hasColumn('usuarios', 'pessoa_id');
        $temNomePessoa = Schema::hasTable('pessoas') && Schema::hasColumn('pessoas', 'nome');

        $colunas = array_values(array_unique(array_merge(
            ['id', 'nome', 'name'],
            $temEmail ? ['email'] : [],
            $temPessoaId ? ['pessoa_id'] : [],
        )));

        DB::table('usuarios')
            ->select($colunas)
            ->where(function ($query) {
                $query->whereNull('nome')->orWhere('nome', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($usuarios) use ($temEmail, $temPessoaId, $temNomePessoa) {
                foreach ($usuarios as $usuario) {
                    $nome = trim((string) ($usuario->name ?? ''));

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

    /** Dá um nome utilizável aos registros que ficaram sem `nome` após a criação da coluna. */
    protected function preencherNomesVazios(): void
    {
        $temEmail = Schema::hasColumn('usuarios', 'email');

        DB::table('usuarios')
            ->select(array_values(array_unique(array_merge(['id', 'nome'], $temEmail ? ['email'] : []))))
            ->where(function ($query) {
                $query->whereNull('nome')->orWhere('nome', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($usuarios) use ($temEmail) {
                foreach ($usuarios as $usuario) {
                    $nome = $temEmail
                        ? trim((string) explode('@', (string) ($usuario->email ?? ''), 2)[0])
                        : '';

                    DB::table('usuarios')
                        ->where('id', $usuario->id)
                        ->update(['nome' => mb_substr($nome !== '' ? $nome : 'Usuário '.$usuario->id, 0, 255)]);
                }
            });
    }

    /**
     * Remove a coluna `name` legada. Se o banco não permitir removê-la (índice ou
     * chave estrangeira criada à mão), ao menos tiramos a obrigatoriedade — é ela
     * que impedia os INSERTs.
     */
    protected function removerColunaNameLegada(): void
    {
        if (! Schema::hasColumn('usuarios', 'name')) {
            return;
        }

        try {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        } catch (Throwable $e) {
            Log::warning('Não foi possível remover a coluna legada usuarios.name; ela será tornada opcional.', [
                'mensagem' => $e->getMessage(),
            ]);

            $this->tornarColunaOpcional('name', 'varchar(255)');
        }
    }

    /**
     * Rede de segurança: colunas que sobraram de outra versão e são obrigatórias sem
     * valor padrão continuam derrubando qualquer INSERT com o erro 1364. Como o
     * sistema não as preenche, elas passam a aceitar NULL. Best-effort: nada aqui
     * pode interromper a instalação.
     */
    protected function relaxarColunasLegadasObrigatorias(): void
    {
        // Só o MySQL aplica o erro 1364; nos outros bancos não há como (nem por que)
        // reescrever a tabela inteira aqui.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        try {
            $colunas = Schema::getColumns('usuarios');
        } catch (Throwable $e) {
            return;
        }

        foreach ($colunas as $coluna) {
            $nome = (string) ($coluna['name'] ?? '');

            if ($nome === '' || in_array(strtolower($nome), self::COLUNAS_DO_SISTEMA, true)) {
                continue;
            }

            // Já aceita NULL ou já tem valor padrão: o INSERT funciona.
            if (! empty($coluna['nullable']) || ($coluna['default'] ?? null) !== null) {
                continue;
            }

            // Chaves primárias, auto incremento e colunas geradas não podem (ou não
            // precisam) ser alteradas por aqui.
            if (! empty($coluna['auto_increment']) || ! empty($coluna['generation']['type'])) {
                continue;
            }

            $tipo = trim((string) ($coluna['type'] ?? ''));

            if ($tipo === '' || ! $this->tipoSeguroParaAlterar($tipo)) {
                continue;
            }

            $this->tornarColunaOpcional($nome, $tipo);
        }
    }

    /** Altera a coluna para aceitar NULL mantendo exatamente o tipo que ela já tem. */
    protected function tornarColunaOpcional(string $coluna, string $tipo): void
    {
        try {
            DB::statement(sprintf(
                'alter table %s modify %s %s null',
                $this->envolverIdentificador($this->nomeRealDaTabela('usuarios')),
                $this->envolverIdentificador($coluna),
                $tipo,
            ));
        } catch (Throwable $e) {
            Log::warning('Coluna legada de usuarios não pôde ser tornada opcional.', [
                'coluna' => $coluna,
                'mensagem' => $e->getMessage(),
            ]);
        }
    }

    /** Nome da tabela já com o prefixo configurado na conexão. */
    protected function nomeRealDaTabela(string $tabela): string
    {
        return DB::getTablePrefix().$tabela;
    }

    /** Escapa um identificador MySQL com crases. */
    protected function envolverIdentificador(string $identificador): string
    {
        return '`'.str_replace('`', '``', $identificador).'`';
    }

    /**
     * O tipo vem do information_schema, mas ainda assim só aceitamos definições de
     * coluna convencionais antes de montar o ALTER.
     */
    protected function tipoSeguroParaAlterar(string $tipo): bool
    {
        return ! preg_match('/[;`]|--|\/\*|\bunion\b|\bselect\b/i', $tipo);
    }

    public function down(): void
    {
        // A coluna legada é removida depois de ter o conteúdo copiado para `nome`, e
        // as demais apenas deixam de ser obrigatórias. Recriar nada disso traria os
        // dados de volta, então o rollback é intencionalmente vazio.
    }
};
