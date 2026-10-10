<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaPagar extends Model
{
    protected $table = 'contas_pagar';

    public const STATUS = [
        'pendente' => 'Pendente',
        'paga' => 'Paga',
    ];

    protected $fillable = [
        'descricao', 'fornecedor_id', 'compra_id', 'valor', 'vencimento',
        'data_pagamento', 'forma_pagamento', 'status', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'data_pagamento' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function getVencidaAttribute(): bool
    {
        return $this->status === 'pendente' && $this->vencimento && $this->vencimento->lt(today());
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }
}
