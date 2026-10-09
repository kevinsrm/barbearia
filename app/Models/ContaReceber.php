<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    protected $table = 'contas_receber';

    public const STATUS = [
        'pendente' => 'Pendente',
        'recebida' => 'Recebida',
    ];

    protected $fillable = [
        'descricao', 'cliente_id', 'venda_id', 'valor', 'vencimento',
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
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
