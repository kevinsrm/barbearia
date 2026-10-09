<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Pessoa;
use App\Models\Servico;
use App\Services\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SiteController extends Controller
{
    public function __construct(protected AgendaService $agenda) {}

    public function index()
    {
        $servicos = Servico::query()->disponiveisOnline()->with('categoria')->orderBy('nome')->get();

        return view('site.index', [
            'servicos' => $servicos,
            'destaques' => $servicos->take(6),
        ]);
    }

    public function servicos()
    {
        $categorias = Servico::query()
            ->disponiveisOnline()
            ->with('categoria')
            ->get()
            ->groupBy(fn ($servico) => $servico->categoria?->nome ?? 'Outros');

        return view('site.servicos', ['categorias' => $categorias]);
    }

    public function sobre()
    {
        return view('site.sobre');
    }

    public function agendamento()
    {
        return view('site.agendamento', [
            'funcionarios' => Funcionario::query()->ativos()->with('pessoa')->get()->sortBy(fn ($f) => $f->pessoa?->nome),
            'servicos' => Servico::query()->disponiveisOnline()->orderBy('nome')->get(),
        ]);
    }

    /**
     * Horários livres usados pelo formulário público (JSON).
     */
    public function horarios(Request $request)
    {
        $dados = $request->validate([
            'data' => 'required|date',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
        ]);

        $funcionario = ! empty($dados['funcionario_id'])
            ? Funcionario::find($dados['funcionario_id'])
            : null;

        return response()->json([
            'fechado' => $this->agenda->estaFechado($dados['data']),
            'horarios' => $this->agenda->horariosDisponiveis($funcionario, $dados['data']),
        ]);
    }

    /**
     * Recebe o agendamento feito pelo site.
     */
    public function agendamentoStore(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:120',
            'telefone' => 'required|string|min:8|max:30',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
            'data' => 'required|date|after_or_equal:today',
            'hora' => ['required', 'date_format:H:i'],
            'servico_ids' => 'required|array|min:1',
            'servico_ids.*' => 'exists:servicos,id',
            'observacoes' => 'nullable|string|max:500',
        ], [], [
            'nome' => 'nome',
            'telefone' => 'telefone',
            'servico_ids' => 'serviços',
        ]);

        $data = Carbon::parse($dados['data']);

        if ($this->agenda->estaFechado($data)) {
            return back()->withInput()->with('error', 'Não atendemos neste dia da semana. Escolha outra data.');
        }

        $funcionario = ! empty($dados['funcionario_id']) ? Funcionario::find($dados['funcionario_id']) : null;

        if (! $this->agenda->horarioLivre($funcionario, $data, $dados['hora'])) {
            return back()->withInput()->with('error', 'Este horário acabou de ser reservado. Escolha outro.');
        }

        $cliente = $this->localizarOuCriarCliente($dados['nome'], $dados['telefone']);

        $this->agenda->criar([
            'cliente_id' => $cliente->id,
            'funcionario_id' => $funcionario?->id,
            'data' => $data->toDateString(),
            'hora' => $dados['hora'],
            'servico_ids' => $dados['servico_ids'],
            'observacoes' => $dados['observacoes'] ?? null,
            'status' => 'pendente',
            'origem' => 'site',
        ]);

        return redirect()->route('site.agendamento')
            ->with('success', 'Agendamento solicitado! '.$data->format('d/m').' às '.$dados['hora'].'. Você receberá a confirmação da barbearia.');
    }

    protected function localizarOuCriarCliente(string $nome, string $telefone): Cliente
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        $pessoa = Pessoa::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') = ?", [$digitos])
            ->first();

        if (! $pessoa) {
            $pessoa = Pessoa::create([
                'nome' => $nome,
                'tipo_pessoa' => 'cliente',
                'telefone' => $telefone,
                'ativo' => true,
            ]);
        }

        return $pessoa->cliente ?? Cliente::create(['pessoa_id' => $pessoa->id, 'status' => 'ativo']);
    }
}
