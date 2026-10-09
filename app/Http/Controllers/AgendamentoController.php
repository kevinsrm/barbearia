<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Servico;
use App\Services\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AgendamentoController extends Controller
{
    public function __construct(protected AgendaService $agenda) {}

    public function index(Request $request)
    {
        $agendamentos = Agendamento::query()
            ->with(['cliente.pessoa', 'funcionario.pessoa', 'servicos'])
            ->when($request->filled('data'), fn ($q) => $q->whereDate('data', $request->string('data')), fn ($q) => $q->whereDate('data', '>=', today()->subDays(7)->toDateString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('funcionario'), fn ($q) => $q->where('funcionario_id', $request->integer('funcionario')))
            ->orderByDesc('data')
            ->orderBy('hora')
            ->paginate(config('barbearia.paginacao', 15))
            ->withQueryString();

        return view('agendamentos.index', [
            'agendamentos' => $agendamentos,
            'funcionarios' => Funcionario::ativos()->with('pessoa')->get(),
            'statusDisponiveis' => Agendamento::STATUS,
        ]);
    }

    public function create()
    {
        return view('agendamentos.create', $this->dadosFormulario(new Agendamento([
            'data' => today(),
            'hora' => config('barbearia.agenda.abertura', '09:00'),
            'status' => Agendamento::STATUS_CONFIRMADO,
        ])));
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        if (! $this->agenda->horarioLivre(
            ($dados['funcionario_id'] ?? null) ? Funcionario::find($dados['funcionario_id']) : null,
            $dados['data'],
            $dados['hora']
        )) {
            return back()->withInput()->with('error', 'Já existe um agendamento neste horário para o profissional escolhido.');
        }

        $agendamento = $this->agenda->criar($dados + ['origem' => 'painel']);

        return redirect()->route('admin.agendamentos.index')->with('success', 'Agendamento criado para '.Carbon::parse($agendamento->data)->format('d/m').' às '.$agendamento->hora.'.');
    }

    public function edit(Agendamento $agendamento)
    {
        return view('agendamentos.edit', $this->dadosFormulario($agendamento->load('servicos')));
    }

    public function update(Request $request, Agendamento $agendamento)
    {
        $dados = $this->validar($request);

        if (! $this->agenda->horarioLivre(
            ($dados['funcionario_id'] ?? null) ? Funcionario::find($dados['funcionario_id']) : null,
            $dados['data'],
            $dados['hora'],
            30,
            $agendamento
        )) {
            return back()->withInput()->with('error', 'Já existe um agendamento neste horário para o profissional escolhido.');
        }

        $this->agenda->atualizar($agendamento, $dados);

        return redirect()->route('admin.agendamentos.index')->with('success', 'Agendamento atualizado!');
    }

    public function destroy(Agendamento $agendamento)
    {
        $agendamento->delete();

        return redirect()->route('admin.agendamentos.index')->with('success', 'Agendamento excluído.');
    }

    /** Troca rápida de status (confirmar, concluir, cancelar...). */
    public function atualizarStatus(Request $request, Agendamento $agendamento)
    {
        $dados = $request->validate([
            'status' => ['required', Rule::in(array_keys(Agendamento::STATUS))],
        ]);

        $agendamento->update(['status' => $dados['status']]);

        return back()->with('success', 'Agendamento marcado como "'.Agendamento::STATUS[$dados['status']].'".');
    }

    /** JSON com horários livres (usado no formulário do painel). */
    public function horariosDisponiveis(Request $request)
    {
        $dados = $request->validate([
            'data' => 'required|date',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
        ]);

        $funcionario = ! empty($dados['funcionario_id']) ? Funcionario::find($dados['funcionario_id']) : null;

        return response()->json([
            'fechado' => $this->agenda->estaFechado($dados['data']),
            'horarios' => $this->agenda->horariosDisponiveis($funcionario, $dados['data']),
        ]);
    }

    protected function dadosFormulario(Agendamento $agendamento): array
    {
        return [
            'agendamento' => $agendamento,
            'clientes' => Cliente::query()->with('pessoa')->get()->sortBy(fn ($c) => $c->pessoa?->nome)->values(),
            'funcionarios' => Funcionario::ativos()->with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome)->values(),
            'servicos' => Servico::ativos()->with('categoria')->orderBy('nome')->get(),
            'statusDisponiveis' => Agendamento::STATUS,
            'servicosSelecionados' => $agendamento->exists ? $agendamento->servicos->pluck('id')->all() : [],
        ];
    }

    protected function validar(Request $request): array
    {
        $dados = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
            'data' => 'required|date',
            'hora' => ['required', 'date_format:H:i'],
            'servico_ids' => 'required|array|min:1',
            'servico_ids.*' => 'exists:servicos,id',
            'status' => ['nullable', Rule::in(array_keys(Agendamento::STATUS))],
            'observacoes' => 'nullable|string|max:500',
        ], [], ['servico_ids' => 'serviços', 'funcionario_id' => 'profissional']);

        $dados['status'] = $dados['status'] ?? Agendamento::STATUS_CONFIRMADO;

        return $dados;
    }
}
