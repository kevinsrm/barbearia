<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Comissao;
use App\Models\Funcionario;
use App\Services\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BarbeiroController extends Controller
{
    public function __construct(protected AgendaService $agenda) {}

    public function index(Request $request)
    {
        $funcionario = $this->funcionarioLogado();

        if (! $funcionario) {
            return redirect()->route('admin.dashboard')->with('error', 'Seu usuário não está vinculado a um profissional.');
        }

        $data = $request->filled('data') ? Carbon::parse($request->string('data')) : today();

        return view('barbeiro.index', [
            'funcionario' => $funcionario,
            'data' => $data,
            'agendamentos' => Agendamento::query()
                ->where('funcionario_id', $funcionario->id)
                ->doDia($data)
                ->with(['cliente.pessoa', 'servicos'])
                ->orderBy('hora')
                ->get(),
            'horariosLivres' => $this->agenda->horariosDisponiveis($funcionario, $data),
            'totalMes' => Comissao::where('funcionario_id', $funcionario->id)
                ->whereBetween('data', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('valor'),
            'pendente' => Comissao::where('funcionario_id', $funcionario->id)->pendentes()->sum('valor'),
            'atendimentosMes' => Agendamento::where('funcionario_id', $funcionario->id)
                ->where('status', Agendamento::STATUS_CONCLUIDO)
                ->whereDate('data', '>=', now()->startOfMonth()->toDateString())
                ->count(),
        ]);
    }

    public function agenda(Request $request)
    {
        $funcionario = $this->funcionarioLogado();

        if (! $funcionario) {
            return redirect()->route('admin.dashboard')->with('error', 'Seu usuário não está vinculado a um profissional.');
        }

        $agendamentos = Agendamento::query()
            ->where('funcionario_id', $funcionario->id)
            ->when($request->filled('data'), fn ($q) => $q->whereDate('data', $request->string('data')))
            ->with(['cliente.pessoa', 'servicos'])
            ->orderByDesc('data')
            ->orderBy('hora')
            ->paginate(15)
            ->withQueryString();

        return view('barbeiro.agenda', ['agendamentos' => $agendamentos, 'funcionario' => $funcionario]);
    }

    public function comissoes()
    {
        $funcionario = $this->funcionarioLogado();

        if (! $funcionario) {
            return redirect()->route('admin.dashboard')->with('error', 'Seu usuário não está vinculado a um profissional.');
        }

        $comissoes = Comissao::query()
            ->where('funcionario_id', $funcionario->id)
            ->with('venda')
            ->orderByDesc('data')
            ->paginate(15);

        return view('barbeiro.comissoes', [
            'comissoes' => $comissoes,
            'funcionario' => $funcionario,
            'totalPendentes' => Comissao::where('funcionario_id', $funcionario->id)->pendentes()->sum('valor'),
            'totalPagas' => Comissao::where('funcionario_id', $funcionario->id)->where('status', 'paga')->sum('valor'),
            'totalMes' => Comissao::where('funcionario_id', $funcionario->id)
                ->whereBetween('data', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('valor'),
        ]);
    }

    /**
     * Bloqueia/libera horários do próprio profissional em uma data.
     */
    public function atualizarDisponibilidade(Request $request)
    {
        $funcionario = $this->funcionarioLogado();

        if (! $funcionario) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $dados = $request->validate([
            'data' => 'required|date',
            'horarios' => 'present|array',
            'horarios.*' => 'date_format:H:i',
        ]);

        $data = Carbon::parse($dados['data'])->toDateString();

        Agendamento::query()
            ->where('funcionario_id', $funcionario->id)
            ->whereDate('data', $data)
            ->where('status', Agendamento::STATUS_BLOQUEADO)
            ->delete();

        foreach (array_unique($dados['horarios'] ?? []) as $hora) {
            Agendamento::create([
                'funcionario_id' => $funcionario->id,
                'data' => $data,
                'hora' => $hora,
                'duracao' => (int) config('barbearia.agenda.intervalo', 30),
                'status' => Agendamento::STATUS_BLOQUEADO,
                'origem' => 'barbeiro',
                'observacoes' => 'Horário bloqueado pelo profissional',
                'valor_total' => 0,
            ]);
        }

        $mensagem = 'Disponibilidade atualizada para '.Carbon::parse($data)->format('d/m/Y').'.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $mensagem])
            : back()->with('success', $mensagem);
    }

    protected function funcionarioLogado(): ?Funcionario
    {
        return Funcionario::query()->where('usuario_id', auth()->id())->first();
    }
}
