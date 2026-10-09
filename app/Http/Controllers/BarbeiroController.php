<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Agendamento;
use App\Models\Comissao;
use Illuminate\Http\Request;

class BarbeiroController extends Controller
{
    public function index()
    {
        $funcionario = Funcionario::where('usuario_id', auth()->id())->first();
        if (!$funcionario) {
            return redirect('/dashboard')->with('error', 'Você não é um barbeiro.');
        }
        return view('barbeiro.index', compact('funcionario'));
    }
    
    public function agenda()
    {
        $funcionario = Funcionario::where('usuario_id', auth()->id())->first();
        if (!$funcionario) {
            return redirect('/dashboard')->with('error', 'Você não é um barbeiro.');
        }
        
        $agendamentos = Agendamento::where('funcionario_id', $funcionario->id)
            ->with('cliente', 'servicos')
            ->orderBy('data', 'asc')
            ->orderBy('hora', 'asc')
            ->paginate(10);
        
        return view('barbeiro.agenda', compact('agendamentos', 'funcionario'));
    }
    
    public function comissoes()
    {
        $funcionario = Funcionario::where('usuario_id', auth()->id())->first();
        if (!$funcionario) {
            return redirect('/dashboard')->with('error', 'Você não é um barbeiro.');
        }
        
        $comissoes = Comissao::where('funcionario_id', $funcionario->id)
            ->orderBy('data', 'desc')
            ->paginate(10);
        
        $totalComissoes = Comissao::where('funcionario_id', $funcionario->id)
            ->where('status', 'pago')
            ->sum('valor');
        
        return view('barbeiro.comissoes', compact('comissoes', 'totalComissoes', 'funcionario'));
    }
    
    public function atualizarDisponibilidade(Request $request)
    {
        $funcionario = Funcionario::where('usuario_id', auth()->id())->first();
        if (!$funcionario) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        
        $request->validate([
            'data' => 'required|date',
            'horarios' => 'required|array',
        ]);
        
        // Limpar agendamentos existentes para este dia e funcionário
        Agendamento::where('funcionario_id', $funcionario->id)
            ->whereDate('data', $request->data)
            ->delete();
        
        // Criar novos agendamentos como indisponíveis (bloqueados)
        foreach ($request->horarios as $hora) {
            Agendamento::create([
                'cliente_id' => null,
                'funcionario_id' => $funcionario->id,
                'data' => $request->data,
                'hora' => $hora,
                'status' => 'bloqueado',
                'observacoes' => 'Horário bloqueado pelo barbeiro',
            ]);
        }
        
        return response()->json(['success' => true]);
    }
}
