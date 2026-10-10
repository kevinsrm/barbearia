<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Support\EnvEditor;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DiagnosticoCommand extends Command
{
    protected $signature = 'barbearia:diagnostico {--sem-rotas : Não simular as rotas do sistema}';

    protected $description = 'Verifica ambiente, banco, migrations e as principais telas do sistema';

    /** Colunas que o sistema usa em `usuarios`; qualquer outra veio de outra versão. */
    private const COLUNAS_USUARIOS = [
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

    protected $erros = 0;

    public function handle(): int
    {
        $this->erros = 0;

        $this->components->info('Ambiente');
        $this->verificar('PHP '.PHP_VERSION.' (mínimo 8.2)', version_compare(PHP_VERSION, '8.2.0', '>='));
        foreach (['pdo', 'mbstring', 'openssl', 'ctype', 'json', 'tokenizer', 'xml', 'fileinfo'] as $ext) {
            $this->verificar('extensão '.$ext, extension_loaded($ext));
        }
        $this->verificar('.env presente', EnvEditor::exists());
        $this->verificar('APP_KEY definida', (bool) config('app.key'));
        $this->verificar('storage/ gravável', is_writable(storage_path()));
        $this->verificar('bootstrap/cache/ gravável', is_writable(base_path('bootstrap/cache')));

        $this->newLine();
        $this->components->info('Banco de dados');
        $this->verificar('conexão '.config('database.default'), $this->testaBanco());

        $tabelasEsperadas = [
            'pessoas', 'usuarios', 'cargos', 'funcionarios', 'clientes', 'fornecedores',
            'servico_categorias', 'servicos', 'produto_categorias', 'produtos',
            'agendamentos', 'agendamento_servicos', 'vendas', 'venda_itens',
            'compras', 'compra_itens', 'estoque_movimentacoes',
            'contas_pagar', 'contas_receber', 'comissoes',
        ];

        foreach ($tabelasEsperadas as $tabela) {
            $this->verificar('tabela '.$tabela, Schema::hasTable($tabela));
        }

        $this->verificarColunasDeUsuarios();

        Artisan::call('migrate:status');
        $status = Artisan::output();
        $pendentes = substr_count($status, 'Pending');
        $this->verificar('migrations em dia ('.$pendentes.' pendentes)', $pendentes === 0);

        $this->verificar('usuário administrador existe', Usuario::where('role', Usuario::ROLE_ADMIN)->exists());

        if (! $this->option('sem-rotas')) {
            $this->newLine();
            $this->components->info('Telas do sistema');
            $this->testarRotas();
        }

        $this->newLine();

        if ($this->erros === 0) {
            $this->components->info('Diagnóstico concluído sem erros.');

            return self::SUCCESS;
        }

        $this->components->error($this->erros.' verificação(ões) falharam.');

        return self::FAILURE;
    }

    protected function verificar(string $item, bool $ok): void
    {
        if ($ok) {
            $this->line('  <fg=green>✔</> '.$item);

            return;
        }

        $this->line('  <fg=red>✘</> '.$item);
        $this->erros = ($this->erros ?? 0) + 1;
    }

    protected function testaBanco(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable $e) {
            $this->line('    <fg=yellow>'.$e->getMessage().'</>');

            return false;
        }
    }

    /**
     * Bancos criados antes do sistema (ou à mão, no phpMyAdmin) podem ter a tabela
     * `usuarios` com colunas de outra versão — a clássica `name` obrigatória e sem
     * valor padrão. O sistema grava `nome`, então o MySQL derruba todo INSERT com o
     * erro 1364 ("Field 'name' doesn't have a default value").
     */
    protected function verificarColunasDeUsuarios(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        try {
            $colunas = Schema::getColumns('usuarios');
        } catch (Throwable $e) {
            $this->line('  <fg=yellow>!</> não consegui ler as colunas de usuarios: '.$e->getMessage().'</>');

            return;
        }

        $bloqueiam = [];
        $extras = [];

        foreach ($colunas as $coluna) {
            $nome = (string) ($coluna['name'] ?? '');

            if ($nome === '' || in_array(strtolower($nome), self::COLUNAS_USUARIOS, true)) {
                continue;
            }

            // Chave primária, auto incremento e coluna gerada não bloqueiam o INSERT.
            $obrigatoria = empty($coluna['nullable'])
                && ($coluna['default'] ?? null) === null
                && empty($coluna['auto_increment'])
                && empty($coluna['generation']['type']);

            if ($obrigatoria) {
                $bloqueiam[] = $nome;
            } else {
                $extras[] = $nome;
            }
        }

        // Colunas extras que aceitam NULL não atrapalham o sistema: são só aviso.
        $this->verificar('tabela usuarios sem colunas antigas que impedem novos cadastros', $bloqueiam === []);

        if ($bloqueiam !== []) {
            $this->line('    <fg=red>'.implode(', ', $bloqueiam).'</> é obrigatória e não tem valor padrão: nenhum cadastro, login ou seed funciona (erro 1364).');
            $this->line('    <fg=yellow>Atualize os arquivos e rode: php artisan migrate --force</>');
        }

        if ($extras !== []) {
            $this->line('    <fg=yellow>!</> colunas que o sistema não usa em usuarios: '.implode(', ', $extras).'</>');
        }
    }

    protected function testarRotas(): void
    {
        $kernel = app(HttpKernel::class);

        $rotasPublicas = [
            '/' => 200,
            '/servicos' => 200,
            '/sobre' => 200,
            '/agendar' => 200,
            '/login' => 200,
            '/cadastro' => 200,
        ];

        foreach ($rotasPublicas as $rota => $esperado) {
            $codigo = $this->codigoResposta($kernel, $rota);
            $ok = $codigo === $esperado;
            $this->line('  '.($ok ? '<fg=green>✔</>' : '<fg=red>✘</>').' GET '.$rota.' → '.$codigo.' (esperado '.$esperado.')');
            $this->erros += $ok ? 0 : 1;
        }

        $admin = Usuario::where('role', Usuario::ROLE_ADMIN)->where('ativo', true)->first()
            ?? Usuario::where('role', Usuario::ROLE_ADMIN)->first();

        if (! $admin) {
            $this->line('  <fg=yellow>!</> Sem usuário administrador para testar o painel (rode: php artisan barbearia:admin admin@exemplo.com)');

            return;
        }

        Auth::login($admin);

        $rotasPainel = [
            '/admin' => 200,
            '/admin/pessoas' => 200,
            '/admin/pessoas/create' => 200,
            '/admin/usuarios' => 200,
            '/admin/usuarios/create' => 200,
            '/admin/servicos' => 200,
            '/admin/servicos/create' => 200,
            '/admin/produtos' => 200,
            '/admin/produtos/create' => 200,
            '/admin/agendamentos' => 200,
            '/admin/agendamentos/create' => 200,
            '/admin/vendas' => 200,
            '/admin/vendas/nova' => 200,
            '/admin/compras' => 200,
            '/admin/compras/nova' => 200,
            '/admin/financeiro' => 200,
            '/admin/financeiro/contas' => 200,
            '/admin/financeiro/comissoes' => 200,
            '/admin/relatorios' => 200,
            '/admin/relatorios/produtos' => 200,
            '/admin/relatorios/financeiro' => 200,
            '/admin/relatorios/comissoes' => 200,
            '/admin/relatorios/clientes' => 200,
            '/admin/relatorios/aniversariantes' => 200,
            '/admin/relatorios/movimentacoes' => 200,
            '/admin/configuracoes' => 200,
            // O administrador não é um profissional cadastrado: a área do barbeiro o devolve ao painel.
            '/admin/barbeiro' => 302,
            '/install' => 200,
        ];

        foreach ($rotasPainel as $rota => $esperado) {
            $codigo = $this->codigoResposta($kernel, $rota);
            $ok = $codigo === $esperado;
            $this->line('  '.($ok ? '<fg=green>✔</>' : '<fg=red>✘</>').' GET '.$rota.' → '.$codigo.' (esperado '.$esperado.')');
            $this->erros += $ok ? 0 : 1;
        }

        Auth::logout();
    }

    protected function codigoResposta(HttpKernel $kernel, string $rota): int
    {
        try {
            return $kernel->handle(Request::create($rota, 'GET'))->getStatusCode();
        } catch (Throwable $e) {
            $this->line('    <fg=yellow>'.get_class($e).': '.$e->getMessage().'</>');

            return 500;
        }
    }
}
