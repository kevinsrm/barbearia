<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    protected $table = 'vendas';

    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CANCELADA = 'cancelada';

    public const STATUS = [
        self::STATUS_CONCLUIDA => 'Concluída',
        self::STATUS_PENDENTE => 'Pendente',
        self::STATUS_CANCELADA => 'Cancelada',
    ];

    public const FORMAS_PAGAMENTO = [
        'dinheiro' => 'Dinheiro',
        'pix' => 'Pix',
        'debito' => 'Cartão de débito',
        'credito' => 'Cartão de crédito',
        'fiado' => 'Fiado / a prazo',
    ];

    protected $fillable = [
        'cliente_id', 'funcionario_id', 'agendamento_id', 'usuario_id',
        'data', 'valor_total', 'desconto', 'forma_pagamento', 'status', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'datetime',
            'valor_total' => 'decimal:2',
            'desconto' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    public function comissoes(): HasMany
    {
        return $this->hasMany(Comissao::class);
    }

    public function contaReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    public function getValorLiquidoAttribute(): float
    {
        return (float) $this->valor_total - (float) $this->desconto;
    }

    public function getClienteNomeAttribute(): string
    {
        return $this->cliente?->pessoa?->nome ?? 'Consumidor final';
    }

    public function getFormaPagamentoLabelAttribute(): string
    {
        return self::FORMAS_PAGAMENTO[$this->forma_pagamento] ?? ucfirst((string) $this->forma_pagamento);
    }

    public function scopeDoDia($query, $data = null)
    {
        $data = $data ? \Illuminate\Support\Carbon::parse($data)->toDateString() : today()->toDateString();

        return $query->whereDate('data', $data);
    }

    public function scopeNaoCanceladas($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELADA);
    }
}
