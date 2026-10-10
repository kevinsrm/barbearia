<?php

namespace App\Services;

use App\Models\EstoqueMovimentacao;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * Registra uma movimentação e atualiza o saldo do produto.
     *
     * - entrada: soma a quantidade
     * - saida:   subtrai a quantidade
     * - ajuste:  define o saldo informado (o delta é registrado no histórico)
     */
    public function movimentar(Produto $produto, string $tipo, int $quantidade, array $extra = []): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($produto, $tipo, $quantidade, $extra) {
            $produto = DB::table('produtos')->where('id', $produto->id)->lockForUpdate()->first()
                ? $produto->fresh()
                : $produto;

            $saldoAnterior = (int) $produto->estoque;

            $novoSaldo = match ($tipo) {
                'entrada' => $saldoAnterior + abs($quantidade),
                'saida' => $saldoAnterior - abs($quantidade),
                'ajuste' => $quantidade,
                default => $saldoAnterior,
            };

            $delta = $novoSaldo - $saldoAnterior;

            $produto->estoque = $novoSaldo;
            $produto->save();

            return EstoqueMovimentacao::create([
                'produto_id' => $produto->id,
                'tipo' => $tipo,
                'quantidade' => $delta !== 0 ? $delta : $quantidade,
                'valor_unitario' => $extra['valor_unitario'] ?? $produto->preco_custo,
                'data' => $extra['data'] ?? now(),
                'usuario_id' => $extra['usuario_id'] ?? (auth()->id() ?: null),
                'fornecedor_id' => $extra['fornecedor_id'] ?? null,
                'venda_id' => $extra['venda_id'] ?? null,
                'compra_id' => $extra['compra_id'] ?? null,
                'observacoes' => $extra['observacoes'] ?? null,
            ]);
        });
    }

    public function darBaixaPorVenda(Produto $produto, int $quantidade, int $vendaId, ?float $valorUnitario = null): void
    {
        $this->movimentar($produto, 'saida', $quantidade, [
            'venda_id' => $vendaId,
            'valor_unitario' => $valorUnitario ?? $produto->preco_custo,
            'observacoes' => 'Saída por venda #'.$vendaId,
        ]);
    }

    public function entradaPorCompra(Produto $produto, int $quantidade, int $compraId, ?float $valorUnitario = null, ?int $fornecedorId = null): void
    {
        $this->movimentar($produto, 'entrada', $quantidade, [
            'compra_id' => $compraId,
            'fornecedor_id' => $fornecedorId,
            'valor_unitario' => $valorUnitario ?? $produto->preco_custo,
            'observacoes' => 'Entrada por compra #'.$compraId,
        ]);
    }
}
