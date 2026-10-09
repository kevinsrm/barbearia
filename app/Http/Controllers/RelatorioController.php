<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use App\Models\Compra;
use App\Models\Comissao;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Agendamento;
use App\Models\Pessoa;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function index()
    {
        $contasPagarCount = ContaPagar::where('status', 'pendente')->count();
        $contasPagarTotal = ContaPagar::where('status', 'pendente')->sum('valor');
        $contasReceberCount = ContaReceber::where('status', 'pendente')->count();
        $contasReceberTotal = ContaReceber::where('status', 'pendente')->sum('valor');
        
        return view('relatorios.index', compact('contasPagarCount', 'contasPagarTotal', 'contasReceberCount', 'contasReceberTotal'));
    }
    
    public function produtos()
    {
        $produtos = Produto::with('categoria', 'estoque')
            ->orderBy('nome')
            ->paginate(20);
        $totalEstoqueValor = Produto::sum(function($query) {
            $query->selectRaw('(preco_custo * estoque) as valor_total');
        });
        return view('relatorios.produtos', compact('produtos', 'totalEstoqueValor'));
    }
    
    public function financeiro()
    {
        $totalVendas = Venda::sum('valor_total');
        $totalCompras = Compra::sum('valor_total');
        $totalComissoes = Comissao::where('status', 'pago')->sum('valor');
        $lucro = $totalVendas - $totalCompras - $totalComissoes;
        
        return view('relatorios.financeiro', compact('totalVendas', 'totalCompras', 'totalComissoes', 'lucro'));
    }
    
    public function comissoes()
    {
        $comissoes = Comissao::with('funcionario')
            ->orderBy('data', 'desc')
            ->paginate(20);
        $totalComissoes = Comissao::sum('valor');
        return view('relatorios.comissoes', compact('comissoes', 'totalComissoes'));
    }
    
    public function contas()
    {
        $contasPagar = ContaPagar::with('fornecedor')
            ->where('status', 'pendente')
            ->orderBy('vencimento')
            ->paginate(20);
        
        $contasReceber = ContaReceber::with('cliente')
            ->where('status', 'pendente')
            ->orderBy('vencimento')
            ->paginate(20);
        
        return view('relatorios.contas', compact('contasPagar', 'contasReceber'));
    }
    
    public function aniversariantes()
    {
        $aniversariantes = Pessoa::where('tipo_pessoa', 'cliente')
            ->whereMonth('data_nascimento', now()->month)
            ->with('cliente')
            ->orderBy('data_nascimento')
            ->paginate(20);
        return view('relatorios.aniversariantes', compact('aniversariantes'));
    }
}
