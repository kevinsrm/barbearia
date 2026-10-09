<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Comissao;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Produto;
use App\Models\Venda;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = today()->toDateString();
        $inicioMes = now()->startOfMonth();

        $agendamentosHoje = Agendamento::query()
            ->doDia($hoje)
            ->where('status', '!=', Agendamento::STATUS_CANCELADO)
            ->orderBy('hora')
            ->with(['cliente.pessoa', 'funcionario.pessoa', 'servicos'])
            ->get();

        return view('dashboard.index', [
            'agendamentosHoje' => $agendamentosHoje,
            'totalAgendamentosHoje' => $agendamentosHoje->count(),
            'agendamentosPendentes' => Agendamento::query()->futuros()->where('status', Agendamento::STATUS_PENDENTE)->count(),
            'vendasHoje' => Venda::query()->naoCanceladas()->doDia($hoje)->sum('valor_total'),
            'vendasMes' => Venda::query()->naoCanceladas()->where('data', '>=', $inicioMes)->sum('valor_total'),
            'ticketMedio' => $this->ticketMedio($inicioMes),
            'comissoesPendentes' => Comissao::query()->pendentes()->sum('valor'),
            'contasPagar' => ContaPagar::query()->pendentes()->sum('valor'),
            'contasReceber' => ContaReceber::query()->pendentes()->sum('valor'),
            'produtosEstoqueBaixo' => Produto::query()->ativos()->estoqueBaixo()->orderBy('nome')->limit(8)->get(),
            'totalProdutosEstoqueBaixo' => Produto::query()->ativos()->estoqueBaixo()->count(),
            'novosClientesMes' => \App\Models\Cliente::whereHas('pessoa', fn ($q) => $q->where('created_at', '>=', $inicioMes))->count(),
        ]);
    }

    protected function ticketMedio($inicioMes): float
    {
        $vendas = Venda::query()->naoCanceladas()->where('data', '>=', $inicioMes);
        $quantidade = (clone $vendas)->count();

        return $quantidade > 0 ? round((float) $vendas->sum('valor_total') / $quantidade, 2) : 0.0;
    }
}
