<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Fornecedor;
use App\Models\Pessoa;
use App\Models\Usuario;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PessoaController extends Controller
{
    public function index(Request $request)
    {
        $pessoas = Pessoa::query()
            ->with(['cliente', 'funcionario.cargo', 'fornecedor', 'usuario'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo_pessoa', $request->string('tipo')))
            ->when($request->filled('status'), fn ($q) => $q->where('ativo', $request->boolean('status')))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = trim((string) $request->string('busca'));
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('cpf', 'like', "%{$busca}%");
                });
            })
            ->orderBy('nome')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('pessoas.index', [
            'pessoas' => $pessoas,
            'tipos' => Pessoa::TIPOS,
            'filtros' => $request->only(['tipo', 'busca', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        return view('pessoas.create', [
            'pessoa' => new Pessoa(['tipo_pessoa' => $request->string('tipo')->toString() ?: 'cliente', 'ativo' => true]),
            'tipos' => Pessoa::TIPOS,
            'cargos' => Cargo::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        DB::transaction(function () use ($dados, $request) {
            $pessoa = Pessoa::create($dados);
            $this->sincronizarVinculo($pessoa, $request->all());
        });

        return redirect()->route('admin.pessoas.index')->with('success', 'Cadastro salvo com sucesso!');
    }

    public function edit(Pessoa $pessoa)
    {
        return view('pessoas.edit', [
            'pessoa' => $pessoa->load(['cliente', 'funcionario', 'fornecedor', 'usuario']),
            'tipos' => Pessoa::TIPOS,
            'cargos' => Cargo::orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Pessoa $pessoa)
    {
        $dados = $this->validar($request, $pessoa);

        DB::transaction(function () use ($pessoa, $dados, $request) {
            $pessoa->update($dados);
            $this->sincronizarVinculo($pessoa, $request->all());
        });

        return redirect()->route('admin.pessoas.index')->with('success', 'Cadastro atualizado!');
    }

    public function destroy(Pessoa $pessoa)
    {
        $temMovimentacao = $pessoa->cliente?->vendas()->exists()
            || $pessoa->fornecedor?->compras()->exists()
            || $pessoa->cliente?->agendamentos()->exists();

        if ($temMovimentacao) {
            $pessoa->update(['ativo' => false]);

            return back()->with('error', 'Este cadastro possui histórico e não pode ser apagado. Ele foi desativado.');
        }

        $pessoa->delete();

        return redirect()->route('admin.pessoas.index')->with('success', 'Cadastro excluído.');
    }

    protected function validar(Request $request, ?Pessoa $pessoa = null): array
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:120',
            'tipo_pessoa' => ['required', Rule::in(array_keys(Pessoa::TIPOS))],
            'cpf' => ['nullable', 'string', 'max:20', Rule::unique('pessoas', 'cpf')->ignore($pessoa?->id)],
            'telefone' => 'nullable|string|max:30',
            'email' => ['nullable', 'email', 'max:180', Rule::unique('pessoas', 'email')->ignore($pessoa?->id)],
            'data_nascimento' => 'nullable|date',
            'endereco' => 'nullable|string|max:180',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:60',
            'bairro' => 'nullable|string|max:80',
            'cidade' => 'nullable|string|max:80',
            'estado' => 'nullable|string|size:2',
            'cep' => 'nullable|string|max:15',
            'observacoes' => 'nullable|string|max:2000',
            'ativo' => 'nullable|boolean',
        ], [], ['cpf' => 'CPF', 'data_nascimento' => 'data de nascimento']);

        $dados['cpf'] = Number::somenteDigitos($dados['cpf'] ?? null);
        $dados['ativo'] = $request->boolean('ativo');

        return $dados;
    }

    /** Cria/atualiza o registro específico do tipo (cliente, funcionário, fornecedor ou usuário). */
    protected function sincronizarVinculo(Pessoa $pessoa, array $entrada): void
    {
        match ($pessoa->tipo_pessoa) {
            'cliente' => Cliente::updateOrCreate(
                ['pessoa_id' => $pessoa->id],
                [
                    'codigo_cliente' => $entrada['codigo_cliente'] ?? null,
                    'status' => $entrada['status_cliente'] ?? 'ativo',
                    'observacoes' => $entrada['observacoes_cliente'] ?? null,
                ]
            ),
            'fornecedor' => Fornecedor::updateOrCreate(
                ['pessoa_id' => $pessoa->id],
                [
                    'codigo_fornecedor' => $entrada['codigo_fornecedor'] ?? null,
                    'contato_comercial' => $entrada['contato_comercial'] ?? null,
                    'prazo_pagamento' => Number::inteiro($entrada['prazo_pagamento'] ?? 30, 30),
                ]
            ),
            'funcionario' => $this->sincronizarFuncionario($pessoa, $entrada),
            'usuario' => null,
            default => null,
        };
    }

    protected function sincronizarFuncionario(Pessoa $pessoa, array $entrada): void
    {
        $funcionario = Funcionario::updateOrCreate(
            ['pessoa_id' => $pessoa->id],
            [
                'cargo_id' => $entrada['cargo_id'] ?: null,
                'codigo_funcional' => $entrada['codigo_funcional'] ?? null,
                'data_admissao' => $entrada['data_admissao'] ?? null,
                'salario_base' => Number::money($entrada['salario_base'] ?? 0),
                'percentual_comissao' => Number::money($entrada['percentual_comissao'] ?? 0),
                'ativo' => (bool) ($entrada['ativo'] ?? true),
            ]
        );

        if (! empty($entrada['criar_acesso']) && $pessoa->email) {
            $usuario = Usuario::firstOrCreate(
                ['email' => $pessoa->email],
                [
                    'pessoa_id' => $pessoa->id,
                    'nome' => $pessoa->nome,
                    'password' => $entrada['senha_acesso'] ?: 'mudar123',
                    'role' => Usuario::ROLE_BARBEIRO,
                    'ativo' => true,
                ]
            );

            $funcionario->update(['usuario_id' => $usuario->id]);
        }
    }
}
