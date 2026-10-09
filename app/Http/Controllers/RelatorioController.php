<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Compra;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\EstoqueMovimentacao;
use App\Models\Funcionario;
use App\Models\Pessoa;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index', [
            'contasPagarCount' => ContaPagar::pendentes()->count(),
            'contasPagarTotal' => ContaPagar::pendentes()->sum('valor'),
            'contasReceberCount' => ContaReceber::pendentes()->count(),
            'contasReceberTotal' => ContaReceber::pendentes()->sum('valor'),
            'totalClientes' => Cliente::count(),
            'totalProdutos' => Produto::count(),
            'estoqueBaixo' => Produto::ativos()->estoqueBaixo()->count(),
        ]);
    }

    public function produtos(Request $request)
    {
        $produtos = Produto::query()
            ->with('categoria')
            ->when($request->boolean('estoque_baixo'), fn ($q) => $q->estoqueBaixo())
            ->orderBy('nome')
            ->get();

        return view('relatorios.produtos', [
            'produtos' => $produtos,
            'totalCusto' => $produtos->sum(fn ($p) => (float) $p->preco_custo * (int) $p->estoque),
            'totalVenda' => $produtos->sum(fn ($p) => (float) $p->preco_venda * (int) $p->estoque),
            'totalItens' => $produtos->sum('estoque'),
        ]);
    }

    public function financeiro(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $vendas = Venda::query()->naoCanceladas()->whereBetween('data', [$de->copy()->startOfDay(), $ate->copy()->endOfDay()]);
        $compras = Compra::query()->where('status', '!=', 'cancelada')->whereBetween('data', [$de->copy()->startOfDay(), $ate->copy()->endOfDay()]);
        $comissoes = Comissao::query()->whereBetween('data', [$de->toDateString(), $ate->toDateString()]);

        $totalVendas = (float) (clone $vendas)->sum('valor_total');
        $totalCompras = (float) (clone $compras)->sum('valor_total');
        $totalComissoes = (float) (clone $comissoes)->sum('valor');

        return view('relatorios.financeiro', [
            'de' => $de,
            'ate' => $ate,
            'totalVendas' => $totalVendas,
            'totalCompras' => $totalCompras,
            'totalComissoes' => $totalComissoes,
            'lucro' => round($totalVendas - $totalCompras - $totalComissoes, 2),
            'quantidadeVendas' => (clone $vendas)->count(),
            'ticketMedio' => (clone $vendas)->count() > 0 ? round($totalVendas / (clone $vendas)->count(), 2) : 0.0,
            'porFormaPagamento' => (clone $vendas)
                ->selectRaw('forma_pagamento, SUM(valor_total) as total, COUNT(*) as quantidade')
                ->groupBy('forma_pagamento')
                ->get(),
            'formasPagamento' => Venda::FORMAS_PAGAMENTO,
            'porDia' => (clone $vendas)
                ->selectRaw('DATE(data) as dia, SUM(valor_total) as total, COUNT(*) as quantidade')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get(),
        ]);
    }

    public function comissoes(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $porFuncionario = Comissao::query()
            ->whereBetween('data', [$de->toDateString(), $ate->toDateString()])
            ->selectRaw('funcionario_id, SUM(valor) as total, SUM(CASE WHEN status = \'paga\' THEN valor ELSE 0 END) as pago, COUNT(*) as quantidade')
            ->groupBy('funcionario_id')
            ->with('funcionario.pessoa')
            ->get();

        return view('relatorios.comissoes', [
            'de' => $de,
            'ate' => $ate,
            'porFuncionario' => $porFuncionario,
            'totalGeral' => (float) $porFuncionario->sum('total'),
            'totalPago' => (float) $porFuncionario->sum('pago'),
            'detalhes' => Comissao::query()
                ->with(['funcionario.pessoa', 'venda'])
                ->whereBetween('data', [$de->toDateString(), $ate->toDateString()])
                ->orderByDesc('data')
                ->limit(200)
                ->get(),
        ]);
    }

    public function aniversariantes(Request $request)
    {
        $mes = $request->integer('mes') ?: (int) now()->month;

        $aniversariantes = Pessoa::query()
            ->where('tipo_pessoa', 'cliente')
            ->aniversariantesDoMes($mes)
            ->with('cliente')
            ->orderBy('data_nascimento')
            ->get()
            ->sortBy(fn ($pessoa) => (int) $pessoa->data_nascimento?->format('d'))
            ->values();

        return view('relatorios.aniversariantes', [
            'aniversariantes' => $aniversariantes,
            'mes' => $mes,
            'meses' => [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        ]);
    }

    public function clientes(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $inicio = $de->copy()->startOfDay();
        $fim = $ate->copy()->endOfDay();

        // Subconsultas (funcionam igual em MySQL e SQLite) para o ranking do período.
        $baseVendas = fn (string $agregacao) => Venda::query()
            ->naoCanceladas()
            ->whereColumn('vendas.cliente_id', 'clientes.id')
            ->whereBetween('data', [$inicio, $fim])
            ->selectRaw($agregacao);

        $clientes = Cliente::query()
            ->with('pessoa')
            ->select('clientes.*')
            ->selectSub($baseVendas('COUNT(*)'), 'vendas_total')
            ->selectSub($baseVendas('COALESCE(SUM(valor_total), 0)'), 'valor_total')
            ->orderByDesc('valor_total')
            ->limit(100)
            ->get();

        return view('relatorios.clientes', [
            'de' => $de,
            'ate' => $ate,
            'clientes' => $clientes,
        ]);
    }

    public function movimentacoes(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $movimentacoes = EstoqueMovimentacao::query()
            ->with(['produto', 'usuario'])
            ->whereBetween('data', [$de->copy()->startOfDay(), $ate->copy()->endOfDay()])
            ->when($request->filled('produto'), fn ($q) => $q->where('produto_id', $request->integer('produto')))
            ->orderByDesc('data')
            ->paginate(30)
            ->withQueryString();

        return view('relatorios.movimentacoes', [
            'movimentacoes' => $movimentacoes,
            'produtos' => Produto::orderBy('nome')->get(['id', 'nome']),
            'de' => $de,
            'ate' => $ate,
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    protected function periodo(Request $request): array
    {
        $de = $request->filled('de') ? Carbon::parse($request->string('de')) : now()->startOfMonth();
        $ate = $request->filled('ate') ? Carbon::parse($request->string('ate')) : now()->endOfMonth();

        if ($de->gt($ate)) {
            [$de, $ate] = [$ate, $de];
        }

        return [$de, $ate];
    }
}
