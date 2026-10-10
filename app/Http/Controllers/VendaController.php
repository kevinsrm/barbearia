<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Venda;
use App\Services\VendaService;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendaController extends Controller
{
    public function __construct(protected VendaService $vendas) {}

    public function index(Request $request)
    {
        $vendas = Venda::query()
            ->with(['cliente.pessoa', 'funcionario.pessoa', 'itens'])
            ->when($request->filled('data'), fn ($q) => $q->whereDate('data', $request->string('data')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('forma_pagamento'), fn ($q) => $q->where('forma_pagamento', $request->string('forma_pagamento')))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = trim((string) $request->string('busca'));
                $q->whereHas('cliente.pessoa', fn ($sub) => $sub->where('nome', 'like', "%{$busca}%"))
                    ->orWhere('id', ctype_digit($busca) ? (int) $busca : 0);
            })
            ->orderByDesc('data')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('vendas.index', [
            'vendas' => $vendas,
            'totalPeriodo' => Venda::query()->naoCanceladas()->when($request->filled('data'), fn ($q) => $q->whereDate('data', $request->string('data')))->sum('valor_total'),
            'statusDisponiveis' => Venda::STATUS,
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
        ]);
    }

    public function create(Request $request)
    {
        $agendamento = $request->filled('agendamento') ? Agendamento::with('servicos')->find($request->integer('agendamento')) : null;

        return view('vendas.create', [
            'clientes' => Cliente::query()->with('pessoa')->get()->sortBy(fn ($c) => $c->pessoa?->nome)->values(),
            'funcionarios' => Funcionario::ativos()->with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
            'servicos' => Servico::ativos()->orderBy('nome')->get(),
            'produtos' => Produto::ativos()->orderBy('nome')->get(),
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
            'agendamento' => $agendamento,
        ]);
    }

    public function store(Request $request)
    {
        Number::normalizarRequest($request, ['desconto']);
        Number::normalizarItens($request, 'itens', ['valor_unitario'], ['quantidade', 'id']);

        $dados = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
            'agendamento_id' => 'nullable|exists:agendamentos,id',
            'data' => 'required|date',
            'forma_pagamento' => ['required', Rule::in(array_keys(Venda::FORMAS_PAGAMENTO))],
            'desconto' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string|max:500',
            'itens' => 'required|array|min:1',
            'itens.*.tipo' => ['required', Rule::in(['produto', 'servico'])],
            'itens.*.id' => 'required|integer',
            'itens.*.quantidade' => 'required|integer|min:1|max:999',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ], [], [
            'itens' => 'itens',
            'itens.*.id' => 'item',
            'forma_pagamento' => 'forma de pagamento',
        ]);

        try {
            $venda = $this->vendas->criar($dados);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.vendas.show', $venda)->with('success', 'Venda #'.$venda->id.' registrada com sucesso!');
    }

    public function show(Venda $venda)
    {
        return view('vendas.show', [
            'venda' => $venda->load(['itens.produto', 'itens.servico', 'cliente.pessoa', 'funcionario.pessoa', 'comissoes.funcionario.pessoa', 'contaReceber']),
        ]);
    }

    public function cancelar(Venda $venda)
    {
        $this->vendas->cancelar($venda);

        return redirect()->route('admin.vendas.show', $venda)->with('success', 'Venda cancelada e estoque devolvido.');
    }
}
