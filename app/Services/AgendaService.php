<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\Funcionario;
use App\Models\Servico;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaService
{
    /**
     * Gera os horários livres de um profissional em uma data.
     *
     * @return array<int, string> lista de horários no formato H:i
     */
    public function horariosDisponiveis(?Funcionario $funcionario, Carbon|string $data): array
    {
        $data = $data instanceof Carbon ? $data->copy() : Carbon::parse($data);

        if ($this->estaFechado($data)) {
            return [];
        }

        $intervalo = max(10, (int) config('barbearia.agenda.intervalo', 30));
        $abertura = Carbon::createFromFormat('H:i', (string) config('barbearia.agenda.abertura', '09:00'));
        $fechamento = Carbon::createFromFormat('H:i', (string) config('barbearia.agenda.fechamento', '20:00'));
        $antecedencia = (int) config('barbearia.agenda.antecedencia_minima', 60);

        $ocupados = $this->intervalosOcupados($funcionario, $data);

        $horarios = [];
        $agora = now();

        while ($abertura->lt($fechamento)) {
            $hora = $abertura->format('H:i');
            $inicio = $data->copy()->setTimeFromTimeString($hora);
            $fim = $inicio->copy()->addMinutes($intervalo);

            $passou = $inicio->lt($agora->copy()->addMinutes($antecedencia));
            $conflito = $this->haConflito($inicio, $fim, $ocupados);

            if (! $passou && ! $conflito) {
                $horarios[] = $hora;
            }

            $abertura->addMinutes($intervalo);
        }

        return $horarios;
    }

    public function estaFechado(Carbon|string $data): bool
    {
        $data = $data instanceof Carbon ? $data->copy() : Carbon::parse($data);
        $diasFechados = array_map('intval', (array) config('barbearia.agenda.dias_fechados', []));

        return in_array((int) $data->dayOfWeek, $diasFechados, true);
    }

    /**
     * Cria um agendamento (com os serviços escolhidos) dentro de uma transação.
     */
    public function criar(array $dados): Agendamento
    {
        return DB::transaction(function () use ($dados) {
            $servicos = $this->resolverServicos($dados['servico_ids'] ?? []);

            $agendamento = Agendamento::create([
                'cliente_id' => $dados['cliente_id'] ?? null,
                'funcionario_id' => $dados['funcionario_id'] ?? null,
                'cliente_nome' => $dados['cliente_nome'] ?? null,
                'cliente_telefone' => $dados['cliente_telefone'] ?? null,
                'data' => Carbon::parse($dados['data'])->toDateString(),
                'hora' => substr((string) $dados['hora'], 0, 5),
                'duracao' => $this->duracaoTotal($servicos, (int) ($dados['duracao'] ?? 0)),
                'status' => $dados['status'] ?? Agendamento::STATUS_CONFIRMADO,
                'origem' => $dados['origem'] ?? 'painel',
                'valor_total' => $this->valorTotal($servicos),
                'desconto' => 0,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            $this->sincronizarServicos($agendamento, $servicos);

            return $agendamento->fresh(['servicos']);
        });
    }

    public function atualizar(Agendamento $agendamento, array $dados): Agendamento
    {
        return DB::transaction(function () use ($agendamento, $dados) {
            $servicos = array_key_exists('servico_ids', $dados)
                ? $this->resolverServicos($dados['servico_ids'])
                : $agendamento->servicos->all();

            $agendamento->fill([
                'cliente_id' => $dados['cliente_id'] ?? $agendamento->cliente_id,
                'funcionario_id' => $dados['funcionario_id'] ?? $agendamento->funcionario_id,
                'cliente_nome' => $dados['cliente_nome'] ?? $agendamento->cliente_nome,
                'cliente_telefone' => $dados['cliente_telefone'] ?? $agendamento->cliente_telefone,
                'data' => isset($dados['data']) ? Carbon::parse($dados['data'])->toDateString() : $agendamento->data->toDateString(),
                'hora' => isset($dados['hora']) ? substr((string) $dados['hora'], 0, 5) : $agendamento->hora,
                'status' => $dados['status'] ?? $agendamento->status,
                'observacoes' => $dados['observacoes'] ?? $agendamento->observacoes,
                'desconto' => $dados['desconto'] ?? $agendamento->desconto,
            ]);

            $agendamento->duracao = $this->duracaoTotal($servicos, (int) ($dados['duracao'] ?? 0));
            $agendamento->valor_total = $this->valorTotal($servicos);
            $agendamento->save();

            $this->sincronizarServicos($agendamento, $servicos);

            return $agendamento->fresh(['servicos']);
        });
    }

    /** @param  Collection<int, Servico>|array<int, Servico>  $servicos */
    protected function sincronizarServicos(Agendamento $agendamento, $servicos): void
    {
        $pivot = [];

        foreach ($servicos as $servico) {
            $pivot[$servico->id] = ['valor' => $servico->valor];
        }

        $agendamento->servicos()->sync($pivot);
    }

    /** @return array<int, Servico> */
    protected function resolverServicos(array|int|null $ids): array
    {
        $ids = array_filter(array_map('intval', (array) $ids));

        if (empty($ids)) {
            return [];
        }

        return Servico::query()->whereIn('id', $ids)->get()->all();
    }

    /** @param  array<int, Servico>  $servicos */
    protected function valorTotal(array $servicos): float
    {
        return round(array_sum(array_map(fn (Servico $s) => (float) $s->valor, $servicos)), 2);
    }

    /** @param  array<int, Servico>  $servicos */
    protected function duracaoTotal(array $servicos, int $manual = 0): int
    {
        if ($manual > 0) {
            return $manual;
        }

        $total = array_sum(array_map(fn (Servico $s) => (int) $s->duracao, $servicos));

        return $total > 0 ? $total : (int) config('barbearia.agenda.intervalo', 30);
    }

    /** Verifica se o horário pretendido já está ocupado. */
    public function horarioLivre(?Funcionario $funcionario, Carbon|string $data, string $hora, int $duracao = 30, ?Agendamento $ignorar = null): bool
    {
        $data = $data instanceof Carbon ? $data->copy() : Carbon::parse($data);
        $ocupados = $this->intervalosOcupados($funcionario, $data, $ignorar);

        $inicio = $data->copy()->setTimeFromTimeString(substr($hora, 0, 5));
        $fim = $inicio->copy()->addMinutes(max(10, $duracao));

        return ! $this->haConflito($inicio, $fim, $ocupados);
    }

    /** @return array<int, array{inicio: Carbon, fim: Carbon}> */
    protected function intervalosOcupados(?Funcionario $funcionario, Carbon $data, ?Agendamento $ignorar = null): array
    {
        $query = Agendamento::query()
            ->whereDate('data', $data->toDateString())
            ->ocupamHorario();

        if ($funcionario) {
            $query->where('funcionario_id', $funcionario->id);
        }

        if ($ignorar) {
            $query->whereKeyNot($ignorar->id);
        }

        $intervalos = [];

        foreach ($query->get(['hora', 'duracao']) as $agendamento) {
            $inicio = $data->copy()->setTimeFromTimeString(substr((string) $agendamento->hora, 0, 5));
            $intervalos[] = [
                'inicio' => $inicio,
                'fim' => $inicio->copy()->addMinutes(max(10, (int) ($agendamento->duracao ?: 30))),
            ];
        }

        return $intervalos;
    }

    /** @param  array<int, array{inicio: Carbon, fim: Carbon}>  $ocupados */
    protected function haConflito(Carbon $inicio, Carbon $fim, array $ocupados): bool
    {
        foreach ($ocupados as $ocupado) {
            if ($inicio->lt($ocupado['fim']) && $fim->gt($ocupado['inicio'])) {
                return true;
            }
        }

        return false;
    }
}
