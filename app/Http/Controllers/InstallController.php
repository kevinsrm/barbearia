<?php

namespace App\Http\Controllers;

use App\Support\EnvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Instalador web: o dono da barbearia envia os arquivos para a hospedagem,
 * abre /install no navegador e responde um formulário. Nada de SSH ou composer.
 */
class InstallController extends Controller
{
    public const LOCK_FILE = 'installed.lock';

    public function index(Request $request)
    {
        if ($this->jaInstalado()) {
            return view('install.instalado', ['lock' => $this->lockInfo()]);
        }

        return view('install.index', [
            'requisitos' => $this->requisitos(),
            'problemas' => $this->problemas(),
            'valores' => $this->valoresPadrao($request),
            'drivers' => ['mysql' => 'MySQL / MariaDB (recomendado)', 'sqlite' => 'SQLite (arquivo local, bom para testar)'],
        ]);
    }

    public function store(Request $request)
    {
        if ($this->jaInstalado()) {
            return redirect()->route('install.index');
        }

        $dados = $request->validate([
            'db_driver' => 'required|in:mysql,sqlite',
            'db_host' => 'nullable|string|max:120',
            'db_port' => 'nullable|integer|min:1|max:65535',
            'db_database' => 'nullable|string|max:120',
            'db_username' => 'nullable|string|max:120',
            'db_password' => 'nullable|string|max:190',
            'app_name' => 'required|string|max:80',
            'app_url' => 'nullable|url|max:180',
            'admin_nome' => 'required|string|min:3|max:120',
            'admin_email' => 'required|email|max:180',
            'admin_senha' => 'required|string|min:6|max:100|confirmed',
            'dados_demo' => 'nullable|boolean',
            'recriar' => 'nullable|boolean',
        ], [], [
            'db_driver' => 'tipo de banco',
            'admin_senha' => 'senha do administrador',
        ]);

        $problemas = $this->problemas();

        if (! empty($problemas)) {
            return back()->withInput()->withErrors(['ambiente' => implode(' ', $problemas)]);
        }

        // Guarda o .env atual: se a instalação falhar (senha de banco errada, por
        // exemplo), devolvemos o arquivo e o sistema continua funcionando como antes.
        $envAnterior = EnvEditor::exists() ? (string) file_get_contents(EnvEditor::path()) : null;
        $bancoAnterior = (array) config('database');

        try {
            $env = $this->montarEnv($dados, $request);

            if (! EnvEditor::setValues($env)) {
                $this->restaurarEnv($envAnterior);

                return back()->withInput()->withErrors([
                    'ambiente' => 'Não consegui gravar o arquivo .env. Ajuste a permissão de escrita da pasta do sistema (chmod 755/775) e tente novamente.',
                ]);
            }

            Artisan::call('config:clear');
            $this->aplicarConfiguracaoBanco($dados);

            // No SQLite o arquivo precisa existir antes de conectar.
            if ($dados['db_driver'] === 'sqlite') {
                $this->garantirArquivoSqlite((string) config('database.connections.sqlite.database'));
            }

            // Testa a conexão antes de mexer nas tabelas.
            DB::connection()->getPdo();

            $comando = $request->boolean('recriar') ? 'migrate:fresh' : 'migrate';

            Artisan::call($comando, ['--force' => true, '--seed' => true]);
            $saidaMigrate = Artisan::output();

            if (str_contains($saidaMigrate, 'SQLSTATE') || str_contains($saidaMigrate, 'ERROR')) {
                $this->restaurarEnv($envAnterior);
                $this->restaurarBanco($bancoAnterior);

                return back()->withInput()->withErrors(['ambiente' => trim(strip_tags($saidaMigrate))]);
            }

            $this->criarAdministrador($dados);

            // Tabelas prontas: a sessão passa a morar no banco (mais confiável em
            // hospedagem compartilhada, onde a pasta storage/ pode estar em NFS).
            if (Schema::hasTable('sessions') && EnvEditor::setValues(['SESSION_DRIVER' => 'database'])) {
                Artisan::call('config:clear');
            }

            $this->gravarLock($dados);

            return redirect()->route('install.concluido')->with('instalacao', [
                'email' => $dados['admin_email'],
            ]);
        } catch (Throwable $e) {
            report($e);

            $this->restaurarEnv($envAnterior);
            $this->restaurarBanco($bancoAnterior);

            return back()->withInput()->withErrors([
                'ambiente' => 'Falha na instalação: '.$e->getMessage(),
            ])->with('saida_migrate', Artisan::output());
        }
    }

    public function concluido()
    {
        if (! $this->jaInstalado()) {
            return redirect()->route('install.index');
        }

        return view('install.concluido', ['lock' => $this->lockInfo()]);
    }

    /* ------------------------------------------------------------------ */

    public function jaInstalado(): bool
    {
        return is_file(storage_path('app/'.self::LOCK_FILE));
    }

    protected function lockInfo(): array
    {
        $conteudo = (string) @file_get_contents(storage_path('app/'.self::LOCK_FILE));
        $dados = json_decode($conteudo, true);

        return is_array($dados) ? $dados : ['instalado_em' => $conteudo];
    }

    protected function montarEnv(array $dados, Request $request): array
    {
        $env = [
            'APP_NAME' => $dados['app_name'],
            'APP_ENV' => 'production',
            'APP_DEBUG' => $request->boolean('modo_debug') ? 'true' : 'false',
            'APP_URL' => ($dados['app_url'] ?? null) ?: rtrim((string) $request->getSchemeAndHttpHost(), '/'),
            'APP_LOCALE' => 'pt_BR',
            'APP_FALLBACK_LOCALE' => 'en',
            'APP_FAKER_LOCALE' => 'pt_BR',
            'DB_CONNECTION' => $dados['db_driver'],
            // Durante a instalação o banco ainda não tem a tabela "sessions",
            // então a sessão fica em arquivo. Depois de migrar, troca para database.
            'SESSION_DRIVER' => 'file',
            'SESSION_COOKIE' => 'barbearia_session',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'log',
            'BARBEARIA_NOME' => $dados['app_name'],
            'ADMIN_NOME' => $dados['admin_nome'],
            'ADMIN_EMAIL' => $dados['admin_email'],
            'DEMO_DATA' => $request->boolean('dados_demo') ? 'true' : 'false',
        ];

        if (empty(EnvEditor::get('APP_KEY'))) {
            $env['APP_KEY'] = EnvEditor::generateKey();
        }

        if ($dados['db_driver'] === 'mysql') {
            $env['DB_HOST'] = ($dados['db_host'] ?? null) ?: 'localhost';
            $env['DB_PORT'] = (string) (($dados['db_port'] ?? null) ?: 3306);
            $env['DB_DATABASE'] = (string) ($dados['db_database'] ?? '');
            $env['DB_USERNAME'] = (string) ($dados['db_username'] ?? '');
            $env['DB_PASSWORD'] = $dados['db_password'] ?? '';
        } else {
            $env['DB_DATABASE'] = ($dados['db_database'] ?? null) ?: 'database/database.sqlite';
        }

        return $env;
    }

    protected function aplicarConfiguracaoBanco(array $dados): void
    {
        if ($dados['db_driver'] === 'mysql') {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => ($dados['db_host'] ?? null) ?: 'localhost',
                'database.connections.mysql.port' => ($dados['db_port'] ?? null) ?: 3306,
                'database.connections.mysql.database' => (string) ($dados['db_database'] ?? ''),
                'database.connections.mysql.username' => (string) ($dados['db_username'] ?? ''),
                'database.connections.mysql.password' => $dados['db_password'] ?? '',
            ]);
        } else {
            $caminho = $this->caminhoSqlite($dados['db_database'] ?? null);
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $caminho,
                'database.connections.sqlite.foreign_key_constraints' => true,
            ]);
        }

        DB::purge($dados['db_driver']);
    }

    protected function caminhoSqlite(?string $valor): string
    {
        if (empty($valor)) {
            return database_path('database.sqlite');
        }

        return str_starts_with($valor, '/') ? $valor : base_path($valor);
    }

    protected function garantirArquivoSqlite(string $caminho): void
    {
        if (is_file($caminho)) {
            return;
        }

        if (! is_dir(dirname($caminho))) {
            @mkdir(dirname($caminho), 0775, true);
        }

        touch($caminho);
        @chmod($caminho, 0664);
    }

    protected function criarAdministrador(array $dados): void
    {
        Artisan::call('barbearia:admin', [
            'email' => $dados['admin_email'],
            '--nome' => $dados['admin_nome'],
            '--password' => $dados['admin_senha'],
        ]);
    }

    /**
     * Devolve a configuração de banco em memória ao estado anterior. Sem isso, a
     * própria gravação da sessão no fim da requisição falharia e o comprador veria
     * uma tela de erro 500 em vez da mensagem explicando o que corrigir.
     *
     * @param  array<string, mixed>  $anterior
     */
    protected function restaurarBanco(array $anterior): void
    {
        foreach (array_keys((array) ($anterior['connections'] ?? [])) as $conexao) {
            DB::purge((string) $conexao);
        }

        config(['database' => $anterior]);
    }

    /** Devolve o .env ao estado anterior quando a instalação não conclui. */
    protected function restaurarEnv(?string $conteudoAnterior): void
    {
        if ($conteudoAnterior !== null) {
            @file_put_contents(EnvEditor::path(), $conteudoAnterior);
        } else {
            @unlink(EnvEditor::path());
        }

        Artisan::call('config:clear');
    }

    protected function gravarLock(array $dados): void
    {
        $conteudo = json_encode([
            'instalado_em' => now()->toDateTimeString(),
            'app_name' => $dados['app_name'],
            'db_driver' => $dados['db_driver'],
            'admin_email' => $dados['admin_email'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        @file_put_contents(storage_path('app/'.self::LOCK_FILE), $conteudo);
    }

    protected function requisitos(): array
    {
        $requisitos = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'extensão PDO' => extension_loaded('pdo'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'PDO SQLite' => extension_loaded('pdo_sqlite'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'ctype' => extension_loaded('ctype'),
            'json' => extension_loaded('json'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'fileinfo' => extension_loaded('fileinfo'),
            'curl' => extension_loaded('curl'),
        ];

        foreach (['storage', 'bootstrap/cache'] as $pasta) {
            $requisitos['Escrita em '.$pasta.'/'] = is_writable(base_path($pasta));
        }

        $requisitos['Escrita no .env / raiz do projeto'] = EnvEditor::isWritable();

        return $requisitos;
    }

    protected function problemas(): array
    {
        $problemas = [];

        foreach ($this->requisitos() as $rotulo => $ok) {
            // PDO MySQL e PDO SQLite são alternativas: só exige uma delas.
            if (in_array($rotulo, ['PDO MySQL', 'PDO SQLite'], true)) {
                continue;
            }

            if (! $ok) {
                $problemas[] = $rotulo.' não está disponível.';
            }
        }

        if (! extension_loaded('pdo_mysql') && ! extension_loaded('pdo_sqlite')) {
            $problemas[] = 'Nenhum driver de banco (pdo_mysql ou pdo_sqlite) está habilitado no PHP.';
        }

        return $problemas;
    }

    protected function valoresPadrao(Request $request): array
    {
        return [
            'app_name' => config('barbearia.nome'),
            'app_url' => $request->getSchemeAndHttpHost(),
            'db_driver' => extension_loaded('pdo_mysql') ? 'mysql' : 'sqlite',
            'db_host' => 'localhost',
            'db_port' => 3306,
            'db_database' => '',
            'db_username' => '',
            'db_password' => '',
            'admin_nome' => 'Administrador',
            'admin_email' => 'admin@'.preg_replace('/^www\./', '', $request->getHost()),
            'admin_senha' => '',
        ];
    }
}
