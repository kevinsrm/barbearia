<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Compra;
use App\Models\Comissao;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    public function index()
    {
        return view('financeiro.index');
    }
    
    public function vendas()
    {
        $vendas = Venda::with('cliente', 'funcionario')
            ->orderBy('data', 'desc')
            ->paginate(20);
        $totalVendas = Venda::sum('valor_total');
        return view('financeiro.vendas', compact('vendas', 'totalVendas'));
    }
    
    public function compras()
    {
        $compras = Compra::with('fornecedor')
            ->orderBy('data', 'desc')
            ->paginate(20);
        $totalCompras = Compra::sum('valor_total');
        return view('financeiro.compras', compact('compras', 'totalCompras'));
    }
    
    public function comissoes()
    {
        $comissoes = Comissao::with('funcionario')
            ->where('status', 'pendente')
            ->orderBy('data', 'asc')
            ->paginate(20);
        $totalComissoes = Comissao::where('status', 'pendente')->sum('valor');
        return view('financeiro.comissoes', compact('comissoes', 'totalComissoes'));
    }
    
    public function pagarComissao(Request $request, Comissao $comissao)
    {
        $comissao->update(['status' => 'pago']);
        return redirect()->route('financeiro.comissoes')->with('success', 'Comissão paga com sucesso!');
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
        
        return view('financeiro.contas', compact('contasPagar', 'contasReceber'));
    }
}
