<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Servico;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class AgendamentoController extends Controller
{
    public function index()
    {
        $agendamentos = Agendamento::with('cliente', 'funcionario')
            ->orderBy('data', 'asc')
            ->orderBy('hora', 'asc')
            ->paginate(20);
        return view('agendamentos.index', compact('agendamentos'));
    }
    
    public function create()
    {
        $servicos = Servico::where('ativo', true)->get();
        $funcionarios = Funcionario::with('pessoa')
            ->where('ativo', true)
            ->orderBy('pessoa.nome')
            ->get();
        return view('agendamentos.create', compact('servicos', 'funcionarios'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'servico_ids.*' => 'exists:servicos,id',
        ]);
        
        $agendamento = Agendamento::create([
            'cliente_id' => $request->cliente_id,
            'funcionario_id' => $request->funcionario_id,
            'data' => $request->data,
            'hora' => $request->hora,
            'status' => 'confirmado',
            'valor_total' => 0,
        ]);
        
        $valorTotal = 0;
        foreach ($request->servico_ids as $servicoId) {
            $servico = Servico::find($servicoId);
            if ($servico) {
                $agendamento->servicos()->attach($servicoId);
                $valorTotal += $servico->valor;
            }
        }
        
        $agendamento->update(['valor_total' => $valorTotal]);
        
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento criado com sucesso!');
    }
    
    public function edit(Agendamento $agendamento)
    {
        $servicos = Servico::where('ativo', true)->get();
        $funcionarios = Funcionario::with('pessoa')
            ->where('ativo', true)
            ->orderBy('pessoa.nome')
            ->get();
        return view('agendamentos.edit', compact('agendamento', 'servicos', 'funcionarios'));
    }
    
    public function update(Request $request, Agendamento $agendamento)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'servico_ids.*' => 'exists:servicos,id',
        ]);
        
        $agendamento->update([
            'funcionario_id' => $request->funcionario_id,
            'data' => $request->data,
            'hora' => $request->hora,
        ]);
        
        // Atualiza serviços
        $agendamento->servicos()->sync($request->servico_ids ?? []);
        
        // Recalcula valor total
        $valorTotal = $agendamento->servicos->sum('valor');
        $agendamento->update(['valor_total' => $valorTotal]);
        
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento atualizado com sucesso!');
    }
    
    public function destroy(Agendamento $agendamento)
    {
        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento excluído com sucesso!');
    }
    
    public function getHorariosDisponiveis(Request $request)
    {
        $funcionarioId = $request->funcionario_id;
        $data = $request->data;
        
        $horariosOcupados = Agendamento::where('funcionario_id', $funcionarioId)
            ->whereDate('data', $data)
            ->pluck('hora');
        
        // Gerar horários disponíveis (08:00 às 20:00, intervalos de 30 min)
        $horariosDisponiveis = [];
        $startTime = now()->setHour(8)->setMinute(0);
        $endTime = now()->setHour(20)->setMinute(0);
        
        while ($startTime <= $endTime) {
            $horaFormatada = $startTime->format('H:i');
            if (!$horariosOcupados->contains($horaFormatada)) {
                $horariosDisponiveis[] = $horaFormatada;
            }
            $startTime->addMinutes(30);
        }
        
        return response()->json($horariosDisponiveis);
    }
}
