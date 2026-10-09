<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Fornecedor;
use App\Models\Pessoa;
use App\Models\Produto;
use App\Models\ProdutoCategoria;
use App\Models\Servico;
use App\Models\ServicoCategoria;
use App\Models\Usuario;
use App\Services\VendaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dados de exemplo para o cliente já ver o sistema funcionando.
 * Só é executado quando DEMO_DATA=true no .env (ou --class=DemoSeeder).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->criarServicos();
        $this->criarProdutos();
        $barbeiros = $this->criarEquipe();
        $clientes = $this->criarClientes();
        $this->criarFornecedor();
        $this->criarVendasExemplo($barbeiros, $clientes);
    }

    protected function criarServicos(): void
    {
        $servicos = [
            ['nome' => 'Corte de cabelo', 'categoria' => 'Cabelo', 'duracao' => 40, 'valor' => 45.00, 'descricao' => 'Corte na tesoura ou máquina com acabamento.'],
            ['nome' => 'Corte infantil', 'categoria' => 'Cabelo', 'duracao' => 30, 'valor' => 35.00, 'descricao' => 'Corte para crianças até 12 anos.'],
            ['nome' => 'Barba completa', 'categoria' => 'Barba', 'duracao' => 30, 'valor' => 35.00, 'descricao' => 'Toalha quente, navalha e finalização.'],
            ['nome' => 'Corte + barba', 'categoria' => 'Cabelo', 'duracao' => 60, 'valor' => 70.00, 'descricao' => 'Combo mais pedido da casa.'],
            ['nome' => 'Sobrancelha', 'categoria' => 'Sobrancelha', 'duracao' => 15, 'valor' => 15.00, 'descricao' => 'Alinhamento na navalha ou pinça.'],
            ['nome' => 'Hidratação capilar', 'categoria' => 'Tratamentos', 'duracao' => 30, 'valor' => 40.00, 'descricao' => 'Hidratação profunda com massagem.'],
            ['nome' => 'Pezinho / acabamento', 'categoria' => 'Cabelo', 'duracao' => 15, 'valor' => 20.00, 'descricao' => 'Retoque rápido entre cortes.'],
        ];

        foreach ($servicos as $servico) {
            $categoria = ServicoCategoria::firstOrCreate(['nome' => $servico['categoria']], ['ordem' => 99, 'ativo' => true]);

            Servico::firstOrCreate(['nome' => $servico['nome']], [
                'servico_categoria_id' => $categoria->id,
                'descricao' => $servico['descricao'],
                'duracao' => $servico['duracao'],
                'valor' => $servico['valor'],
                'percentual_comissao' => config('barbearia.comissao_padrao', 40),
                'ativo' => true,
                'agendamento_online' => true,
            ]);
        }
    }

    protected function criarProdutos(): void
    {
        $produtos = [
            ['nome' => 'Pomada modeladora 120g', 'codigo' => 'POM-120', 'categoria' => 'Cabelo', 'custo' => 22.00, 'venda' => 45.00, 'estoque' => 24, 'minimo' => 6],
            ['nome' => 'Shampoo anticaspa 300ml', 'codigo' => 'SHA-300', 'categoria' => 'Cabelo', 'custo' => 16.50, 'venda' => 34.90, 'estoque' => 18, 'minimo' => 5],
            ['nome' => 'Óleo para barba 30ml', 'codigo' => 'OLE-030', 'categoria' => 'Barba', 'custo' => 19.00, 'venda' => 42.00, 'estoque' => 12, 'minimo' => 4],
            ['nome' => 'Balm pós-barba 100g', 'codigo' => 'BAL-100', 'categoria' => 'Barba', 'custo' => 14.00, 'venda' => 32.00, 'estoque' => 3, 'minimo' => 5],
            ['nome' => 'Navalha descartável (un)', 'codigo' => 'NAV-001', 'categoria' => 'Descartáveis', 'custo' => 0.80, 'venda' => 3.00, 'estoque' => 120, 'minimo' => 40],
            ['nome' => 'Pente profissional', 'codigo' => 'PEN-001', 'categoria' => 'Acessórios', 'custo' => 9.00, 'venda' => 22.00, 'estoque' => 8, 'minimo' => 3],
        ];

        foreach ($produtos as $produto) {
            $categoria = ProdutoCategoria::firstOrCreate(['nome' => $produto['categoria']], ['ordem' => 99, 'ativo' => true]);

            Produto::firstOrCreate(['codigo' => $produto['codigo']], [
                'categoria_id' => $categoria->id,
                'nome' => $produto['nome'],
                'descricao' => null,
                'preco_custo' => $produto['custo'],
                'preco_venda' => $produto['venda'],
                'estoque' => $produto['estoque'],
                'estoque_minimo' => $produto['minimo'],
                'unidade' => 'un',
                'ativo' => true,
            ]);
        }
    }

    /** @return array<int, Funcionario> */
    protected function criarEquipe(): array
    {
        $equipe = [
            ['nome' => 'João Barbeiro', 'cpf' => '11122233344', 'email' => 'joao@barbearia.exemplo', 'telefone' => '(98) 98888-1111', 'cargo' => 'Barbeiro', 'codigo' => 'BAR-001', 'comissao' => 45, 'salario' => 2200],
            ['nome' => 'Maria Silva', 'cpf' => '22233344455', 'email' => 'maria@barbearia.exemplo', 'telefone' => '(98) 98888-2222', 'cargo' => 'Barbeiro', 'codigo' => 'BAR-002', 'comissao' => 40, 'salario' => 2000],
            ['nome' => 'Carlos Recepção', 'cpf' => '33344455566', 'email' => 'recepcao@barbearia.exemplo', 'telefone' => '(98) 98888-3333', 'cargo' => 'Recepcionista', 'codigo' => 'REC-001', 'comissao' => 0, 'salario' => 1600],
        ];

        $cargoBarbeiro = Cargo::where('nome', 'Barbeiro')->first();
        $funcionarios = [];

        foreach ($equipe as $dados) {
            $pessoa = Pessoa::firstOrCreate(['cpf' => $dados['cpf']], [
                'nome' => $dados['nome'],
                'tipo_pessoa' => 'funcionario',
                'email' => $dados['email'],
                'telefone' => $dados['telefone'],
                'ativo' => true,
            ]);

            $usuario = Usuario::firstOrCreate(['email' => $dados['email']], [
                'pessoa_id' => $pessoa->id,
                'nome' => $dados['nome'],
                'password' => 'barbeiro123',
                'role' => $dados['cargo'] === 'Barbeiro' ? Usuario::ROLE_BARBEIRO : Usuario::ROLE_RECEPCAO,
                'ativo' => true,
            ]);

            $cargo = $dados['cargo'] === 'Barbeiro'
                ? $cargoBarbeiro
                : Cargo::where('nome', $dados['cargo'])->first();

            $funcionarios[] = Funcionario::firstOrCreate(['pessoa_id' => $pessoa->id], [
                'usuario_id' => $usuario->id,
                'cargo_id' => $cargo?->id,
                'codigo_funcional' => $dados['codigo'],
                'data_admissao' => now()->subYear()->toDateString(),
                'salario_base' => $dados['salario'],
                'percentual_comissao' => $dados['comissao'],
                'ativo' => true,
            ]);
        }

        return $funcionarios;
    }

    /** @return array<int, Cliente> */
    protected function criarClientes(): array
    {
        $lista = [
            ['nome' => 'Pedro Santos', 'cpf' => '44455566677', 'telefone' => '(98) 97777-1010', 'email' => 'pedro.cliente@exemplo.com'],
            ['nome' => 'Lucas Oliveira', 'cpf' => '55566677788', 'telefone' => '(98) 97777-2020', 'email' => 'lucas.cliente@exemplo.com'],
            ['nome' => 'Ana Souza', 'cpf' => '66677788899', 'telefone' => '(98) 97777-3030', 'email' => 'ana.cliente@exemplo.com'],
            ['nome' => 'Rafael Lima', 'cpf' => '77788899900', 'telefone' => '(98) 97777-4040', 'email' => 'rafael.cliente@exemplo.com'],
        ];

        $clientes = [];

        foreach ($lista as $dados) {
            $pessoa = Pessoa::firstOrCreate(['cpf' => $dados['cpf']], [
                'nome' => $dados['nome'],
                'tipo_pessoa' => 'cliente',
                'telefone' => $dados['telefone'],
                'email' => $dados['email'],
                'ativo' => true,
            ]);

            $clientes[] = Cliente::firstOrCreate(['pessoa_id' => $pessoa->id], ['status' => 'ativo']);
        }

        return $clientes;
    }

    protected function criarFornecedor(): void
    {
        $pessoa = Pessoa::firstOrCreate(['cpf' => '11222333000181'], [
            'nome' => 'Distribuidora Beleza Pura',
            'tipo_pessoa' => 'fornecedor',
            'telefone' => '(98) 3222-0000',
            'email' => 'contato@belezapura.exemplo',
            'cidade' => 'São Luís',
            'estado' => 'MA',
            'ativo' => true,
        ]);

        Fornecedor::firstOrCreate(['pessoa_id' => $pessoa->id], [
            'codigo_fornecedor' => 'FOR-001',
            'contato_comercial' => 'Vendas',
            'prazo_pagamento' => 28,
        ]);
    }

    protected function criarVendasExemplo(array $barbeiros, array $clientes): void
    {
        if (\App\Models\Venda::count() > 0) {
            return;
        }

        $vendas = app(VendaService::class);
        $servicos = Servico::ativos()->get();
        $produtos = Produto::ativos()->get();

        foreach (range(1, 12) as $i) {
            $funcionario = $barbeiros[array_rand($barbeiros)] ?? null;
            $cliente = $clientes[array_rand($clientes)] ?? null;
            $servico = $servicos->random();
            $data = now()->subDays(random_int(0, 25))->setTime(random_int(9, 19), random_int(0, 1) ? 30 : 0);

            $itens = [[
                'tipo' => 'servico',
                'id' => $servico->id,
                'quantidade' => 1,
                'valor_unitario' => $servico->valor,
            ]];

            if ($i % 3 === 0 && $produtos->isNotEmpty()) {
                $produto = $produtos->random();
                $itens[] = [
                    'tipo' => 'produto',
                    'id' => $produto->id,
                    'quantidade' => 1,
                    'valor_unitario' => $produto->preco_venda,
                ];
            }

            try {
                $vendas->criar([
                    'cliente_id' => $cliente?->id,
                    'funcionario_id' => $funcionario?->id,
                    'data' => $data,
                    'forma_pagamento' => ['dinheiro', 'pix', 'debito', 'credito'][array_rand(['dinheiro', 'pix', 'debito', 'credito'])],
                    'itens' => $itens,
                ]);
            } catch (\Throwable $e) {
                $this->command?->warn('Venda de exemplo ignorada: '.$e->getMessage());
            }
        }
    }
}
