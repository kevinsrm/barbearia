<?php

namespace App\Console\Commands;

use App\Support\EnvEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InstalarSistemaCommand extends Command
{
    protected $signature = 'barbearia:install
                            {--driver= : mysql ou sqlite}
                            {--host=localhost : Host do banco}
                            {--port=3306 : Porta do banco}
                            {--database= : Nome do banco (ou caminho do arquivo sqlite)}
                            {--username= : Usuário do banco}
                            {--password= : Senha do banco}
                            {--nome= : Nome da barbearia}
                            {--url= : URL pública do sistema}
                            {--admin-email= : E-mail do administrador}
                            {--admin-nome=Administrador : Nome do administrador}
                            {--admin-senha= : Senha do administrador}
                            {--demo : Instalar dados de demonstração}
                            {--fresh : Recriar todas as tabelas (apaga os dados)}
                            {--force : Não fazer perguntas (usa os padrões)}';

    protected $description = 'Instala o sistema via linha de comando (escreve o .env, migra o banco e cria o administrador)';

    public function handle(): int
    {
        $interativo = ! $this->option('force');

        $driver = $this->option('driver') ?: ($interativo
            ? $this->choice('Tipo de banco', ['mysql', 'sqlite'], extension_loaded('pdo_mysql') ? 0 : 1)
            : (extension_loaded('pdo_mysql') ? 'mysql' : 'sqlite'));

        $valores = ['DB_CONNECTION' => $driver, 'APP_ENV' => 'production', 'APP_DEBUG' => 'false'];

        if ($driver === 'mysql') {
            $valores['DB_HOST'] = $this->option('host') ?: ($interativo ? $this->ask('Host do banco', 'localhost') : 'localhost');
            $valores['DB_PORT'] = (string) ($this->option('port') ?: ($interativo ? $this->ask('Porta', '3306') : '3306'));
            $valores['DB_DATABASE'] = $this->option('database') ?: ($interativo ? $this->ask('Nome do banco') : '');
            $valores['DB_USERNAME'] = $this->option('username') ?: ($interativo ? $this->ask('Usuário do banco') : '');
            $valores['DB_PASSWORD'] = (string) ($this->option('password') ?? ($interativo ? $this->secret('Senha do banco') : ''));
        } else {
            $caminho = $this->option('database') ?: 'database/database.sqlite';
            $valores['DB_DATABASE'] = $caminho;
            $absoluto = str_starts_with($caminho, '/') ? $caminho : base_path($caminho);
            if (! is_file($absoluto)) {
                @touch($absoluto);
            }
        }

        $valores['APP_NAME'] = $this->option('nome') ?: ($interativo ? $this->ask('Nome da barbearia', config('barbearia.nome')) : config('barbearia.nome'));
        $valores['BARBEARIA_NOME'] = $valores['APP_NAME'];
        $valores['APP_URL'] = $this->option('url') ?: ($interativo ? $this->ask('URL pública', (string) config('app.url')) : (string) config('app.url'));
        $valores['APP_LOCALE'] = 'pt_BR';
        $valores['SESSION_DRIVER'] = 'file';
        $valores['CACHE_STORE'] = 'file';
        $valores['QUEUE_CONNECTION'] = 'sync';
        $valores['DEMO_DATA'] = $this->option('demo') ? 'true' : 'false';

        $adminEmail = $this->option('admin-email') ?: ($interativo ? $this->ask('E-mail do administrador', 'admin@barbearia.com') : 'admin@barbearia.com');
        $valores['ADMIN_EMAIL'] = $adminEmail;
        $valores['ADMIN_NOME'] = $this->option('admin-nome') ?: 'Administrador';

        // Se algo der errado, devolvemos o .env que já existia.
        $envAnterior = EnvEditor::exists() ? (string) file_get_contents(EnvEditor::path()) : null;

        EnvEditor::createFromExample();

        if (empty(EnvEditor::get('APP_KEY'))) {
            $valores['APP_KEY'] = EnvEditor::generateKey();
        }

        if (! EnvEditor::setValues($valores)) {
            $this->components->error('Não foi possível gravar o arquivo .env (permissão de escrita).');

            return self::FAILURE;
        }

        Artisan::call('config:clear');
        $this->aplicarBanco($driver, $valores);

        try {
            DB::connection()->getPdo();
            $this->components->info('Conexão com o banco ok.');
        } catch (Throwable $e) {
            $this->components->error('Falha ao conectar no banco: '.$e->getMessage());

            if ($envAnterior !== null) {
                file_put_contents(EnvEditor::path(), $envAnterior);
                Artisan::call('config:clear');
                $this->components->info('O .env anterior foi restaurado. Corrija os dados do banco e rode o comando novamente.');
            }

            return self::FAILURE;
        }

        $comando = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
        Artisan::call($comando, ['--force' => true, '--seed' => true]);
        $this->line(Artisan::output());

        Artisan::call('barbearia:admin', [
            'email' => $adminEmail,
            '--nome' => $valores['ADMIN_NOME'],
            '--password' => $this->option('admin-senha') ?: null,
        ]);
        $this->line(Artisan::output());

        // Tabelas prontas: a sessão passa a morar no banco (mais confiável em
        // hospedagem compartilhada, onde a pasta storage/ pode estar em NFS).
        if (Schema::hasTable('sessions')) {
            EnvEditor::setValues(['SESSION_DRIVER' => 'database']);
            Artisan::call('config:clear');
        }

        @file_put_contents(storage_path('app/installed.lock'), json_encode([
            'instalado_em' => now()->toDateTimeString(),
            'app_name' => $valores['APP_NAME'],
            'db_driver' => $driver,
            'admin_email' => $adminEmail,
            'via' => 'artisan',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->components->info('Instalação concluída! Acesse '.$valores['APP_URL'].'/login');

        return self::SUCCESS;
    }

    protected function aplicarBanco(string $driver, array $valores): void
    {
        if ($driver === 'mysql') {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => $valores['DB_HOST'],
                'database.connections.mysql.port' => $valores['DB_PORT'],
                'database.connections.mysql.database' => $valores['DB_DATABASE'],
                'database.connections.mysql.username' => $valores['DB_USERNAME'],
                'database.connections.mysql.password' => $valores['DB_PASSWORD'],
            ]);
        } else {
            $caminho = $valores['DB_DATABASE'];
            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => str_starts_with($caminho, '/') ? $caminho : base_path($caminho),
            ]);
        }

        DB::purge($driver);
    }
}
