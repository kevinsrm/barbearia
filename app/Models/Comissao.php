<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comissao extends Model
{
    protected $table = 'comissoes';

    public const STATUS = [
        'pendente' => 'Pendente',
        'paga' => 'Paga',
    ];

    protected $fillable = [
        'funcionario_id', 'venda_id', 'agendamento_id', 'valor_base',
        'percentual', 'valor', 'data', 'data_pagamento', 'status', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'data_pagamento' => 'date',
            'valor_base' => 'decimal:2',
            'percentual' => 'decimal:2',
            'valor' => 'decimal:2',
        ];
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function getFuncionarioNomeAttribute(): string
    {
        return $this->funcionario?->pessoa?->nome ?? 'Profissional #'.($this->funcionario_id ?? '-');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }
}
