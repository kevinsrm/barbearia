<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\Usuario;
use App\Models\Funcionario;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\ServicoCategoria;
use App\Models\Servico;
use App\Models\ProdutoCategoria;
use App\Models\Produto;
use App\Models\Cargo;
use Illuminate\Database\Seeder;

class InicialSeeder extends Seeder
{
    public function run(): void
    {
        // Cargos
        $cargos = [
            ['nome' => 'Barbeiro', 'descricao' => 'Profissional responsável por cortes e barbas'],
            ['nome' => 'Gerente', 'descricao' => 'Responsável pela gestão do salão'],
            ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema'],
        ];
        foreach ($cargos as $cargo) {
            Cargo::firstOrCreate($cargo);
        }
        
        // Pessoas e Usuários
        $pessoas = [
            // Administrador
            ['nome' => 'Admin Silva', 'cpf' => '12345678901', 'email' => 'admin@barbearia.com', 'tipo_pessoa' => 'usuario'],
            // Gerente
            ['nome' => 'Gerente Souza', 'cpf' => '23456789012', 'email' => 'gerente@barbearia.com', 'tipo_pessoa' => 'usuario'],
            // Barbeiros
            ['nome' => 'João Barbeiro', 'cpf' => '34567890123', 'email' => 'joao@barbearia.com', 'tipo_pessoa' => 'funcionario'],
            ['nome' => 'Maria Barbeira', 'cpf' => '45678901234', 'email' => 'maria@barbearia.com', 'tipo_pessoa' => 'funcionario'],
            // Clientes
            ['nome' => 'Cliente 1', 'cpf' => '56789012345', 'email' => 'cliente1@barbearia.com', 'tipo_pessoa' => 'cliente'],
            ['nome' => 'Cliente 2', 'cpf' => '67890123456', 'email' => 'cliente2@barbearia.com', 'tipo_pessoa' => 'cliente'],
            // Fornecedor
            ['nome' => 'Fornecedor Produtos', 'cpf' => '78901234567', 'email' => 'fornecedor@barbearia.com', 'tipo_pessoa' => 'fornecedor'],
        ];
        
        foreach ($pessoas as $pessoaData) {
            $pessoa = Pessoa::firstOrCreate(['cpf' => $pessoaData['cpf']], $pessoaData);
            
            if ($pessoaData['tipo_pessoa'] == 'usuario') {
                Usuario::firstOrCreate(['pessoa_id' => $pessoa->id, 'email' => $pessoaData['email']], [
                    'password' => bcrypt('12345678'),
                ])->assignRole($pessoaData['email'] == 'admin@barbearia.com' ? 'admin' : 'gerente');
            }
        }
        
        // Categorias de Serviços
        $servicoCategorias = [
            ['nome' => 'Cabelo', 'descricao' => 'Cortes e tratamentos capilares', 'ordem' => 1],
            ['nome' => 'Barba', 'descricao' => 'Barba e bigode', 'ordem' => 2],
            ['nome' => 'Estética', 'descricao' => 'Tratamentos faciais', 'ordem' => 3],
        ];
        foreach ($servicoCategorias as $cat) {
            ServicoCategoria::firstOrCreate($cat);
        }
        
        // Serviços
        $servicos = [
            ['nome' => 'Corte de Cabelo', 'descricao' => 'Corte tradicional ou moderno', 'duracao' => 45, 'valor' => 40.00, 'servico_categoria_id' => 1],
            ['nome' => 'Barba Completa', 'descricao' => 'Barba completa com toalha quente', 'duracao' => 30, 'valor' => 35.00, 'servico_categoria_id' => 2],
            ['nome' => 'Penteado', 'descricao' => 'Finalização e styling', 'duracao' => 30, 'valor' => 25.00, 'servico_categoria_id' => 1],
            ['nome' => 'Máscara Facial', 'descricao' => 'Tratamento facial revitalizante', 'duracao' => 60, 'valor' => 60.00, 'servico_categoria_id' => 3],
        ];
        foreach ($servicos as $servico) {
            Servico::firstOrCreate($servico);
        }
        
        // Categorias de Produtos
        $produtoCategorias = [
            ['nome' => 'Shampoos', 'descricao' => 'Produtos para cabelo', 'ordem' => 1],
            ['nome' => 'Condicionadores', 'descricao' => 'Produtos para cabelo', 'ordem' => 2],
            ['nome' => 'Finalizadores', 'descricao' => 'Produtos para finalização', 'ordem' => 3],
        ];
        foreach ($produtoCategorias as $cat) {
            ProdutoCategoria::firstOrCreate($cat);
        }
        
        // Produtos
        $produtos = [
            ['nome' => 'Shampoo Anti-caspa', 'codigo' => 'SHAM001', 'preco_custo' => 15.00, 'preco_venda' => 25.00, 'estoque_minimo' => 10, 'unidade' => 'unidade', 'categoria_id' => 1],
            ['nome' => 'Condicionador Hidratante', 'codigo' => 'COND001', 'preco_custo' => 20.00, 'preco_venda' => 35.00, 'estoque_minimo' => 10, 'unidade' => 'unidade', 'categoria_id' => 2],
            ['nome' => 'Pomada Modeladora', 'codigo' => 'POMA001', 'preco_custo' => 30.00, 'preco_venda' => 50.00, 'estoque_minimo' => 5, 'unidade' => 'unidade', 'categoria_id' => 3],
        ];
        foreach ($produtos as $produto) {
            Produto::firstOrCreate(['codigo' => $produto['codigo']], $produto);
        }
        
        // Associar funcionários aos cargos
        $funcionarioPessoa = Pessoa::where('cpf', '34567890123')->first();
        if ($funcionarioPessoa) {
            $funcionario = Funcionario::firstOrCreate(['pessoa_id' => $funcionarioPessoa->id], [
                'usuario_id' => Usuario::where('pessoa_id', $funcionarioPessoa->id)->first()->id,
                'cargo_id' => Cargo::where('nome', 'Barbeiro')->first()->id,
                'codigo_funcional' => 'BAR001',
                'data_admissao' => now(),
                'salario_base' => 3000.00,
                'ativo' => true,
            ]);
        }
        
        // Criar estoque inicial
        foreach (Produto::all() as $produto) {
            if (!$produto->estoque) {
                $produto->estoque = rand(20, 50);
                $produto->save();
            }
        }
        
        echo "Dados iniciais criados com sucesso!";
    }
}