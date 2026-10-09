<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Venda;
use App\Models\Produto;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $agendamentosHoje = Agendamento::whereDate('data', today())->count();
        $agendamentosFuturos = Agendamento::agendamentosFuturos()->count();
        $vendasHoje = Venda::whereDate('data', today())->sum('valor_total');
        $produtosEstoqueBaixo = Produto::where('estoque_minimo', '>=', function($query) {
            $query->selectRaw('COALESCE(estoque - estoque_minimo, 0)')
                 ->from('estoque')
                 ->whereColumn('produto_id', 'produtos.id');
        })->count();
        
        return view('dashboard.index', compact(
            'agendamentosHoje',
            'agendamentosFuturos',
            'vendasHoje',
            'produtosEstoqueBaixo'
        ));
    }
}
