<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Services\CompraService;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompraController extends Controller
{
    public function __construct(protected CompraService $compras) {}

    public function index(Request $request)
    {
        $compras = Compra::query()
            ->with(['fornecedor.pessoa', 'itens'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('fornecedor'), fn ($q) => $q->where('fornecedor_id', $request->integer('fornecedor')))
            ->orderByDesc('data')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('compras.index', [
            'compras' => $compras,
            'fornecedores' => Fornecedor::with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
            'totalPeriodo' => Compra::where('status', '!=', 'cancelada')->sum('valor_total'),
        ]);
    }

    public function create()
    {
        return view('compras.create', [
            'fornecedores' => Fornecedor::with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
            'produtos' => Produto::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Number::normalizarRequest($request, [], ['prazo_pagamento']);
        Number::normalizarItens($request, 'itens', ['valor_unitario'], ['quantidade', 'id']);

        $dados = $request->validate([
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'data' => 'required|date',
            'forma_pagamento' => 'required|string|max:30',
            'status' => ['required', Rule::in(['pendente', 'recebida'])],
            'prazo_pagamento' => 'nullable|integer|min:0|max:365',
            'gerar_conta_pagar' => 'nullable|boolean',
            'observacoes' => 'nullable|string|max:500',
            'itens' => 'required|array|min:1',
            'itens.*.id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1|max:99999',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ], [], ['itens' => 'itens', 'gerar_conta_pagar' => 'gerar conta a pagar']);

        try {
            $compra = $this->compras->criar($dados + ['gerar_conta_pagar' => $request->boolean('gerar_conta_pagar')]);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.compras.show', $compra)->with('success', 'Compra #'.$compra->id.' registrada!');
    }

    public function show(Compra $compra)
    {
        return view('compras.show', [
            'compra' => $compra->load(['itens.produto', 'fornecedor.pessoa', 'contaPagar', 'movimentacoes']),
        ]);
    }
}
