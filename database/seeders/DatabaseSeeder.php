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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cargos
        Cargo::insert([
            ['nome' => 'Barbeiro', 'descricao' => 'Profissional responsável por cortes e barbas'],
            ['nome' => 'Gerente', 'descricao' => 'Responsável pela gestão do salão'],
            ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema'],
        ]);
        
        // Pessoas e Usuários
        $pessoas = [
            ['nome' => 'Admin Silva', 'cpf' => '12345678901', 'email' => 'admin@barbearia.com', 'tipo_pessoa' => 'usuario'],
            ['nome' => 'Gerente Souza', 'cpf' => '23456789012', 'email' => 'gerente@barbearia.com', 'tipo_pessoa' => 'usuario'],
            ['nome' => 'João Barbeiro', 'cpf' => '34567890123', 'email' => 'joao@barbearia.com', 'tipo_pessoa' => 'funcionario'],
            ['nome' => 'Maria Barbeira', 'cpf' => '45678901234', 'email' => 'maria@barbearia.com', 'tipo_pessoa' => 'funcionario'],
            ['nome' => 'Cliente 1', 'cpf' => '56789012345', 'email' => 'cliente1@barbearia.com', 'tipo_pessoa' => 'cliente'],
            ['nome' => 'Cliente 2', 'cpf' => '67890123456', 'email' => 'cliente2@barbearia.com', 'tipo_pessoa' => 'cliente'],
            ['nome' => 'Fornecedor Produtos', 'cpf' => '78901234567', 'email' => 'fornecedor@barbearia.com', 'tipo_pessoa' => 'fornecedor'],
        ];
        
        foreach ($pessoas as $pessoaData) {
            Pessoa::firstOrCreate(['cpf' => $pessoaData['cpf']], $pessoaData);
        }
        
        // Usuários
        Usuario::create([
            'pessoa_id' => Pessoa::where('cpf', '12345678901')->first()->id,
            'email' => 'admin@barbearia.com',
            'password' => bcrypt('12345678'),
        ])->assignRole('admin');
        
        Usuario::create([
            'pessoa_id' => Pessoa::where('cpf', '23456789012')->first()->id,
            'email' => 'gerente@barbearia.com',
            'password' => bcrypt('12345678'),
        ])->assignRole('gerente');
        
        // Categorias de Serviços
        ServicoCategoria::insert([
            ['nome' => 'Cabelo', 'descricao' => 'Cortes e tratamentos capilares', 'ordem' => 1],
            ['nome' => 'Barba', 'descricao' => 'Barba e bigode', 'ordem' => 2],
            ['nome' => 'Estética', 'descricao' => 'Tratamentos faciais', 'ordem' => 3],
        ]);
        
        // Serviços
        Servico::insert([
            ['nome' => 'Corte de Cabelo', 'descricao' => 'Corte tradicional ou moderno', 'duracao' => 45, 'valor' => 40.00, 'servico_categoria_id' => 1],
            ['nome' => 'Barba Completa', 'descricao' => 'Barba completa com toalha quente', 'duracao' => 30, 'valor' => 35.00, 'servico_categoria_id' => 2],
            ['nome' => 'Penteado', 'descricao' => 'Finalização e styling', 'duracao' => 30, 'valor' => 25.00, 'servico_categoria_id' => 1],
            ['nome' => 'Máscara Facial', 'descricao' => 'Tratamento facial revitalizante', 'duracao' => 60, 'valor' => 60.00, 'servico_categoria_id' => 3],
        ]);
        
        // Categorias de Produtos
        ProdutoCategoria::insert([
            ['nome' => 'Shampoos', 'descricao' => 'Produtos para cabelo', 'ordem' => 1],
            ['nome' => 'Condicionadores', 'descricao' => 'Produtos para cabelo', 'ordem' => 2],
            ['nome' => 'Finalizadores', 'descricao' => 'Produtos para finalização', 'ordem' => 3],
        ]);
        
        // Produtos
        Produto::insert([
            ['nome' => 'Shampoo Anti-caspa', 'codigo' => 'SHAM001', 'preco_custo' => 15.00, 'preco_venda' => 25.00, 'estoque_minimo' => 10, 'unidade' => 'unidade', 'categoria_id' => 1],
            ['nome' => 'Condicionador Hidratante', 'codigo' => 'COND001', 'preco_custo' => 20.00, 'preco_venda' => 35.00, 'estoque_minimo' => 10, 'unidade' => 'unidade', 'categoria_id' => 2],
            ['nome' => 'Pomada Modeladora', 'codigo' => 'POMA001', 'preco_custo' => 30.00, 'preco_venda' => 50.00, 'estoque_minimo' => 5, 'unidade' => 'unidade', 'categoria_id' => 3],
        ]);
        
        // Associar funcionários aos cargos
        $funcionarioPessoa = Pessoa::where('cpf', '34567890123')->first();
        if ($funcionarioPessoa) {
            Funcionario::create([
                'pessoa_id' => $funcionarioPessoa->id,
                'usuario_id' => Usuario::where('pessoa_id', $funcionarioPessoa->id)->first()->id,
                'cargo_id' => Cargo::where('nome', 'Barbeiro')->first()->id,
                'codigo_funcional' => 'BAR001',
                'data_admissao' => now(),
                'salario_base' => 3000.00,
                'ativo' => true,
            ]);
        }
        
        // Gerar estoque inicial
        foreach (Produto::all() as $produto) {
            if (!$produto->estoque) {
                $produto->estoque = rand(20, 50);
                $produto->save();
            }
        }
        
        echo "Dados iniciais inseridos com sucesso!";
    }
}