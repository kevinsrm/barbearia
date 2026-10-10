<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\ServicoCategoria;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicoController extends Controller
{
    public function index(Request $request)
    {
        $servicos = Servico::query()
            ->with('categoria')
            ->when($request->filled('categoria'), fn ($q) => $q->where('servico_categoria_id', $request->integer('categoria')))
            ->when($request->filled('busca'), fn ($q) => $q->where('nome', 'like', '%'.trim((string) $request->string('busca')).'%'))
            ->orderBy('nome')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('servicos.index', [
            'servicos' => $servicos,
            'categorias' => ServicoCategoria::orderBy('ordem')->orderBy('nome')->get(),
        ]);
    }

    public function create()
    {
        return view('servicos.create', [
            'servico' => new Servico([
                'duracao' => (int) config('barbearia.agenda.intervalo', 30),
                'ativo' => true,
                'agendamento_online' => true,
                'percentual_comissao' => config('barbearia.comissao_padrao', 0),
            ]),
            'categorias' => ServicoCategoria::ativas()->orderBy('ordem')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Servico::create($this->validar($request));

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function edit(Servico $servico)
    {
        return view('servicos.edit', [
            'servico' => $servico,
            'categorias' => ServicoCategoria::orderBy('ordem')->get(),
        ]);
    }

    public function update(Request $request, Servico $servico)
    {
        $servico->update($this->validar($request));

        return redirect()->route('admin.servicos.index')->with('success', 'Serviço atualizado!');
    }

    public function destroy(Servico $servico)
    {
        if ($servico->agendamentos()->exists()) {
            $servico->update(['ativo' => false, 'agendamento_online' => false]);

            return back()->with('error', 'Serviço possui histórico de agendamentos e foi apenas desativado.');
        }

        $servico->delete();

        return back()->with('success', 'Serviço removido.');
    }

    protected function validar(Request $request): array
    {
        Number::normalizarRequest($request, ['valor', 'percentual_comissao'], ['duracao']);

        $dados = $request->validate([
            'nome' => 'required|string|min:2|max:120',
            'descricao' => 'nullable|string|max:500',
            'duracao' => 'required|integer|min:5|max:480',
            'valor' => 'required|numeric|min:0|max:999999',
            'percentual_comissao' => 'nullable|numeric|min:0|max:100',
            'servico_categoria_id' => ['nullable', Rule::exists('servico_categorias', 'id')],
        ], [], ['servico_categoria_id' => 'categoria']);

        return [
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'duracao' => (int) $dados['duracao'],
            'valor' => Number::money($dados['valor']),
            'percentual_comissao' => Number::money($dados['percentual_comissao'] ?? 0),
            'servico_categoria_id' => $dados['servico_categoria_id'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'agendamento_online' => $request->boolean('agendamento_online', true),
        ];
    }
}
