<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Pessoa;
use App\Models\ProdutoCategoria;
use App\Models\ServicoCategoria;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Cria a base mínima para o sistema funcionar: cargos, categorias e o administrador.
     * Pode ser executado quantas vezes quiser (não duplica registros).
     */
    public function run(): void
    {
        foreach ([
            ['nome' => 'Barbeiro', 'codigo' => 'BAR', 'descricao' => 'Profissional responsável por cortes e barba'],
            ['nome' => 'Gerente', 'codigo' => 'GER', 'descricao' => 'Responsável pela gestão da barbearia'],
            ['nome' => 'Recepcionista', 'codigo' => 'REC', 'descricao' => 'Atendimento e agendamentos'],
            ['nome' => 'Auxiliar', 'codigo' => 'AUX', 'descricao' => 'Apoio geral'],
        ] as $cargo) {
            Cargo::firstOrCreate(['nome' => $cargo['nome']], $cargo);
        }

        foreach ([
            ['nome' => 'Cabelo', 'descricao' => 'Cortes e acabamentos', 'ordem' => 1],
            ['nome' => 'Barba', 'descricao' => 'Barba, bigode e navalha', 'ordem' => 2],
            ['nome' => 'Sobrancelha', 'descricao' => 'Design de sobrancelha', 'ordem' => 3],
            ['nome' => 'Tratamentos', 'descricao' => 'Hidratação e tratamentos', 'ordem' => 4],
        ] as $categoria) {
            ServicoCategoria::firstOrCreate(['nome' => $categoria['nome']], $categoria + ['ativo' => true]);
        }

        foreach ([
            ['nome' => 'Cabelo', 'descricao' => 'Shampoos, pomadas e finalizadores', 'ordem' => 1],
            ['nome' => 'Barba', 'descricao' => 'Óleos, balms e lâminas', 'ordem' => 2],
            ['nome' => 'Acessórios', 'descricao' => 'Pentes, tesouras e máquinas', 'ordem' => 3],
            ['nome' => 'Descartáveis', 'descricao' => 'Capas, luvas e lâminas', 'ordem' => 4],
        ] as $categoria) {
            ProdutoCategoria::firstOrCreate(['nome' => $categoria['nome']], $categoria + ['ativo' => true]);
        }

        $this->criarAdministrador();

        if (env('DEMO_DATA', false)) {
            $this->call(DemoSeeder::class);
        }
    }

    protected function criarAdministrador(): void
    {
        $email = strtolower((string) env('ADMIN_EMAIL', 'admin@barbearia.com'));
        $nome = (string) env('ADMIN_NOME', 'Administrador');
        $senha = (string) env('ADMIN_PASSWORD', 'admin123');

        $pessoa = Pessoa::firstOrCreate(
            ['email' => $email],
            ['nome' => $nome, 'tipo_pessoa' => 'usuario', 'ativo' => true]
        );

        Usuario::firstOrCreate(
            ['email' => $email],
            [
                'pessoa_id' => $pessoa->id,
                'nome' => $nome,
                'password' => $senha,
                'role' => Usuario::ROLE_ADMIN,
                'ativo' => true,
            ]
        );

        $this->command?->info('Administrador: '.$email.' (senha padrão: '.$senha.') — troque após o primeiro acesso.');
    }
}
