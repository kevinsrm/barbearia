<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\ContaPagar;
use App\Models\Produto;
use App\Support\Number;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompraService
{
    public function __construct(protected EstoqueService $estoque) {}

    /**
     * Registra uma compra de fornecedor, atualiza o estoque (quando recebida)
     * e gera a conta a pagar se solicitado.
     */
    public function criar(array $dados): Compra
    {
        $itens = $dados['itens'] ?? [];

        if (empty($itens)) {
            throw new RuntimeException('Informe ao menos um produto para a compra.');
        }

        return DB::transaction(function () use ($dados, $itens) {
            $compra = Compra::create([
                'fornecedor_id' => $dados['fornecedor_id'] ?? null,
                'usuario_id' => auth()->id() ?: null,
                'data' => ! empty($dados['data']) ? Carbon::parse($dados['data']) : now(),
                'forma_pagamento' => $dados['forma_pagamento'] ?? 'boleto',
                'status' => $dados['status'] ?? 'recebida',
                'prazo_pagamento' => (int) ($dados['prazo_pagamento'] ?? 30),
                'valor_total' => 0,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            $total = 0.0;

            foreach ($itens as $item) {
                $produto = Produto::find($item['id'] ?? ($item['produto_id'] ?? 0));

                if (! $produto) {
                    continue;
                }

                $quantidade = max(1, Number::inteiro($item['quantidade'] ?? 1, 1));
                $unitario = Number::money($item['valor_unitario'] ?? $produto->preco_custo);
                $subtotal = round($unitario * $quantidade, 2);

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'produto_id' => $produto->id,
                    'descricao' => $produto->nome,
                    'quantidade' => $quantidade,
                    'valor_unitario' => $unitario,
                    'valor_total' => $subtotal,
                ]);

                if ($compra->status === 'recebida') {
                    $this->estoque->entradaPorCompra(
                        $produto,
                        $quantidade,
                        $compra->id,
                        $unitario,
                        $compra->fornecedor_id
                    );

                    $produto->preco_custo = $unitario;
                    $produto->save();
                }

                $total += $subtotal;
            }

            if ($compra->itens()->count() === 0) {
                throw new RuntimeException('Nenhum produto válido foi informado para a compra.');
            }

            $compra->valor_total = round($total, 2);
            $compra->save();

            if (! empty($dados['gerar_conta_pagar']) && $compra->valor_total > 0) {
                ContaPagar::create([
                    'descricao' => 'Compra #'.$compra->id.' - '.$compra->fornecedor_nome,
                    'fornecedor_id' => $compra->fornecedor_id,
                    'compra_id' => $compra->id,
                    'valor' => $compra->valor_total,
                    'vencimento' => $compra->data->copy()->addDays($compra->prazo_pagamento)->toDateString(),
                    'status' => 'pendente',
                    'forma_pagamento' => $compra->forma_pagamento,
                ]);
            }

            return $compra->fresh(['itens.produto', 'fornecedor.pessoa']);
        });
    }
}
