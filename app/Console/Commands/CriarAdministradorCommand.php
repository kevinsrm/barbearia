<?php

namespace App\Console\Commands;

use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CriarAdministradorCommand extends Command
{
    protected $signature = 'barbearia:admin
                            {email : E-mail do administrador}
                            {--nome=Administrador : Nome de exibição}
                            {--password= : Senha (se omitida será gerada e exibida)}';

    protected $description = 'Cria (ou atualiza) o usuário administrador do sistema';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $nome = trim((string) $this->option('nome')) ?: 'Administrador';
        $senha = (string) $this->option('password');
        $gerada = false;

        if ($senha === '') {
            $senha = 'Barb'.random_int(100000, 999999).'!';
            $gerada = true;
        }

        $usuario = DB::transaction(function () use ($email, $nome, $senha) {
            $pessoa = Pessoa::firstOrCreate(
                ['email' => $email],
                ['nome' => $nome, 'tipo_pessoa' => 'usuario', 'ativo' => true]
            );

            $pessoa->update(['nome' => $nome, 'tipo_pessoa' => 'usuario', 'ativo' => true]);

            return Usuario::updateOrCreate(
                ['email' => $email],
                [
                    'pessoa_id' => $pessoa->id,
                    'nome' => $nome,
                    'password' => $senha,
                    'role' => Usuario::ROLE_ADMIN,
                    'ativo' => true,
                ]
            );
        });

        $this->components->info('Administrador salvo: '.$usuario->email);

        if ($gerada) {
            $this->components->warn('Senha gerada: '.$senha.' (peça para o usuário trocá-la)');
        }

        return self::SUCCESS;
    }
}
