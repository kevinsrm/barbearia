<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\Comissao;
use App\Models\ContaReceber;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Support\Number;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VendaService
{
    public function __construct(
        protected EstoqueService $estoque,
        protected AgendaService $agenda,
    ) {}

    /**
     * Cria a venda com seus itens, dá baixa no estoque, gera as comissões e
     * (quando a venda é a prazo) cria a conta a receber.
     *
     * @param  array{cliente_id?: int|null, funcionario_id?: int|null, agendamento_id?: int|null,
     *               data?: string|null, forma_pagamento?: string, desconto?: mixed,
     *               observacoes?: string|null, itens: array<int, array<string, mixed>>}  $dados
     */
    public function criar(array $dados): Venda
    {
        $itens = $dados['itens'] ?? [];

        if (empty($itens)) {
            throw new RuntimeException('Informe ao menos um item para a venda.');
        }

        return DB::transaction(function () use ($dados, $itens) {
            $desconto = Number::money($dados['desconto'] ?? 0);

            $venda = Venda::create([
                'cliente_id' => $dados['cliente_id'] ?? null,
                'funcionario_id' => $dados['funcionario_id'] ?? null,
                'agendamento_id' => $dados['agendamento_id'] ?? null,
                'usuario_id' => auth()->id() ?: null,
                'data' => ! empty($dados['data']) ? Carbon::parse($dados['data']) : now(),
                'forma_pagamento' => $dados['forma_pagamento'] ?? 'dinheiro',
                'status' => $dados['status'] ?? Venda::STATUS_CONCLUIDA,
                'desconto' => $desconto,
                'valor_total' => 0,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            $total = 0.0;

            foreach ($itens as $item) {
                $quantidade = max(1, Number::inteiro($item['quantidade'] ?? 1, 1));
                $tipo = ($item['tipo'] ?? 'produto') === 'servico' ? 'servico' : 'produto';

                if ($tipo === 'servico') {
                    $servico = Servico::find($item['id'] ?? 0);

                    if (! $servico) {
                        continue;
                    }

                    $unitario = Number::money($item['valor_unitario'] ?? $servico->valor);
                    $subtotal = round($unitario * $quantidade, 2);

                    VendaItem::create([
                        'venda_id' => $venda->id,
                        'servico_id' => $servico->id,
                        'descricao' => $servico->nome,
                        'quantidade' => $quantidade,
                        'valor_unitario' => $unitario,
                        'valor_total' => $subtotal,
                    ]);

                    $this->gerarComissao($venda, $subtotal, (float) $servico->percentual_comissao);
                    $total += $subtotal;

                    continue;
                }

                $produto = Produto::find($item['id'] ?? 0);

                if (! $produto) {
                    continue;
                }

                $disponivel = (int) $produto->estoque;

                if (config('barbearia.bloquear_venda_sem_estoque', true) && $quantidade > $disponivel) {
                    throw new RuntimeException(
                        'Estoque insuficiente de "'.$produto->nome.'": há '.$disponivel.' em estoque e a venda pede '
                        .$quantidade.'. Dê entrada no estoque (menu Produtos) ou reduza a quantidade.'
                    );
                }

                $unitario = Number::money($item['valor_unitario'] ?? $produto->preco_venda);
                $subtotal = round($unitario * $quantidade, 2);

                VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $produto->id,
                    'descricao' => $produto->nome,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $unitario,
                    'valor_total' => $subtotal,
                ]);

                $this->estoque->darBaixaPorVenda($produto, $quantidade, $venda->id, (float) $produto->preco_custo);

                if (config('barbearia.comissao_sobre_produtos', false)) {
                    $this->gerarComissao($venda, $subtotal, 0.0);
                }

                $total += $subtotal;
            }

            if ($venda->itens()->count() === 0) {
                throw new RuntimeException('Nenhum item válido foi informado para a venda.');
            }

            $venda->valor_total = round($total, 2);
            $venda->desconto = min($desconto, $total);
            $venda->save();

            if ($venda->agendamento_id) {
                Agendamento::whereKey($venda->agendamento_id)
                    ->update(['status' => Agendamento::STATUS_CONCLUIDO]);
            }

            if ($venda->forma_pagamento === 'fiado') {
                $this->criarContaReceber($venda);
            }

            return $venda->fresh(['itens.produto', 'itens.servico', 'cliente.pessoa', 'funcionario.pessoa']);
        });
    }

    /**
     * Cancela a venda: devolve o estoque, remove comissões não pagas e marca como cancelada.
     */
    public function cancelar(Venda $venda): Venda
    {
        return DB::transaction(function () use ($venda) {
            if ($venda->status === Venda::STATUS_CANCELADA) {
                return $venda;
            }

            foreach ($venda->itens()->with('produto')->get() as $item) {
                if ($item->produto) {
                    $this->estoque->movimentar($item->produto, 'entrada', (int) $item->quantidade, [
                        'venda_id' => $venda->id,
                        'observacoes' => 'Estorno da venda #'.$venda->id,
                    ]);
                }
            }

            Comissao::where('venda_id', $venda->id)->where('status', 'pendente')->delete();

            ContaReceber::where('venda_id', $venda->id)->where('status', 'pendente')->delete();

            $venda->status = Venda::STATUS_CANCELADA;
            $venda->save();

            return $venda;
        });
    }

    protected function gerarComissao(Venda $venda, float $base, float $percentualServico): void
    {
        if (! $venda->funcionario_id) {
            return;
        }

        $funcionario = $venda->funcionario;

        if (! $funcionario) {
            return;
        }

        $percentual = $percentualServico > 0 ? $percentualServico : $funcionario->percentualComissaoEfetivo();

        if ($percentual <= 0) {
            return;
        }

        Comissao::create([
            'funcionario_id' => $funcionario->id,
            'venda_id' => $venda->id,
            'valor_base' => round($base, 2),
            'percentual' => round($percentual, 2),
            'valor' => round($base * $percentual / 100, 2),
            'data' => $venda->data->toDateString(),
            'status' => 'pendente',
        ]);
    }

    protected function criarContaReceber(Venda $venda): ContaReceber
    {
        $prazo = (int) config('barbearia.prazo_fiado_dias', 30);

        return ContaReceber::create([
            'descricao' => 'Venda #'.$venda->id.' - '.($venda->cliente?->pessoa?->nome ?? 'Consumidor final'),
            'cliente_id' => $venda->cliente_id,
            'venda_id' => $venda->id,
            'valor' => $venda->valor_liquido,
            'vencimento' => $venda->data->copy()->addDays($prazo)->toDateString(),
            'status' => 'pendente',
            'observacoes' => 'Gerada automaticamente pela venda a prazo.',
        ]);
    }
}
