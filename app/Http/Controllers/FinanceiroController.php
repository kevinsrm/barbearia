<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Compra;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Fornecedor;
use App\Models\Venda;
use App\Support\Number;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceiroController extends Controller
{
    public function index()
    {
        $inicioMes = now()->startOfMonth();

        return view('financeiro.index', [
            'vendasHoje' => Venda::naoCanceladas()->doDia()->sum('valor_total'),
            'vendasMes' => Venda::naoCanceladas()->where('data', '>=', $inicioMes)->sum('valor_total'),
            'comprasMes' => Compra::where('status', '!=', 'cancelada')->where('data', '>=', $inicioMes)->sum('valor_total'),
            'comissoesPendentes' => Comissao::pendentes()->sum('valor'),
            'comissoesPagasMes' => Comissao::where('status', 'paga')->where('data_pagamento', '>=', $inicioMes->toDateString())->sum('valor'),
            'aReceber' => ContaReceber::pendentes()->sum('valor'),
            'aPagar' => ContaPagar::pendentes()->sum('valor'),
            'porFormaPagamento' => Venda::query()
                ->naoCanceladas()
                ->where('data', '>=', $inicioMes)
                ->selectRaw('forma_pagamento, SUM(valor_total) as total, COUNT(*) as quantidade')
                ->groupBy('forma_pagamento')
                ->get(),
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
            'ultimasVendas' => Venda::naoCanceladas()->with('cliente.pessoa')->orderByDesc('data')->limit(8)->get(),
        ]);
    }

    public function contas()
    {
        return view('financeiro.contas', [
            'contasPagar' => ContaPagar::with('fornecedor.pessoa')->orderBy('vencimento')->paginate(10, ['*'], 'pagar')->withQueryString(),
            'contasReceber' => ContaReceber::with('cliente.pessoa')->orderBy('vencimento')->paginate(10, ['*'], 'receber')->withQueryString(),
            'fornecedores' => Fornecedor::with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
            'clientes' => Cliente::with('pessoa')->get()->sortBy(fn ($c) => $c->pessoa?->nome)->values(),
            'totalPagar' => ContaPagar::pendentes()->sum('valor'),
            'totalReceber' => ContaReceber::pendentes()->sum('valor'),
        ]);
    }

    public function criarContaPagar(Request $request)
    {
        Number::normalizarRequest($request, ['valor']);

        $dados = $request->validate([
            'descricao' => 'required|string|max:180',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'valor' => 'required|numeric|min:0.01',
            'vencimento' => 'required|date',
            'observacoes' => 'nullable|string|max:500',
        ]);

        ContaPagar::create([
            'descricao' => $dados['descricao'],
            'fornecedor_id' => $dados['fornecedor_id'] ?? null,
            'valor' => Number::money($dados['valor']),
            'vencimento' => $dados['vencimento'],
            'status' => 'pendente',
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        return back()->with('success', 'Conta a pagar lançada.');
    }

    public function criarContaReceber(Request $request)
    {
        Number::normalizarRequest($request, ['valor']);

        $dados = $request->validate([
            'descricao' => 'required|string|max:180',
            'cliente_id' => 'nullable|exists:clientes,id',
            'valor' => 'required|numeric|min:0.01',
            'vencimento' => 'required|date',
            'observacoes' => 'nullable|string|max:500',
        ]);

        ContaReceber::create([
            'descricao' => $dados['descricao'],
            'cliente_id' => $dados['cliente_id'] ?? null,
            'valor' => Number::money($dados['valor']),
            'vencimento' => $dados['vencimento'],
            'status' => 'pendente',
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        return back()->with('success', 'Conta a receber lançada.');
    }

    public function pagarConta(Request $request, ContaPagar $conta)
    {
        $dados = $request->validate([
            'forma_pagamento' => 'nullable|string|max:30',
            'data_pagamento' => 'nullable|date',
        ]);

        $conta->update([
            'status' => 'paga',
            'data_pagamento' => $dados['data_pagamento'] ?? today()->toDateString(),
            'forma_pagamento' => $dados['forma_pagamento'] ?? 'dinheiro',
        ]);

        return back()->with('success', 'Pagamento registrado.');
    }

    public function receberConta(Request $request, ContaReceber $conta)
    {
        $dados = $request->validate([
            'forma_pagamento' => ['nullable', Rule::in(array_keys(Venda::FORMAS_PAGAMENTO))],
            'data_pagamento' => 'nullable|date',
        ]);

        $conta->update([
            'status' => 'recebida',
            'data_pagamento' => $dados['data_pagamento'] ?? today()->toDateString(),
            'forma_pagamento' => $dados['forma_pagamento'] ?? 'dinheiro',
        ]);

        if ($conta->venda_id) {
            $conta->venda?->update(['forma_pagamento' => $conta->forma_pagamento, 'status' => Venda::STATUS_CONCLUIDA]);
        }

        return back()->with('success', 'Recebimento registrado.');
    }

    public function excluirConta(string $tipo, int $id)
    {
        $modelo = $tipo === 'pagar' ? ContaPagar::class : ContaReceber::class;
        $conta = $modelo::findOrFail($id);
        $conta->delete();

        return back()->with('success', 'Conta removida.');
    }

    public function comissoes(Request $request)
    {
        $comissoes = Comissao::query()
            ->with(['funcionario.pessoa', 'venda'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')), fn ($q) => $q->where('status', 'pendente'))
            ->when($request->filled('funcionario'), fn ($q) => $q->where('funcionario_id', $request->integer('funcionario')))
            ->orderByDesc('data')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('financeiro.comissoes', [
            'comissoes' => $comissoes,
            'totalPendente' => Comissao::pendentes()->sum('valor'),
            'totalPago' => Comissao::where('status', 'paga')->sum('valor'),
            'funcionarios' => \App\Models\Funcionario::ativos()->with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
        ]);
    }

    public function pagarComissao(Comissao $comissao)
    {
        $comissao->update(['status' => 'paga', 'data_pagamento' => today()->toDateString()]);

        return back()->with('success', 'Comissão de '.number_format((float) $comissao->valor, 2, ',', '.').' marcada como paga.');
    }

    public function estornarComissao(Comissao $comissao)
    {
        $comissao->update(['status' => 'pendente', 'data_pagamento' => null]);

        return back()->with('success', 'Comissão voltou para pendente.');
    }
}
