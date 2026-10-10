<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $table = 'compras';

    public const STATUS = [
        'pendente' => 'Pendente',
        'recebida' => 'Recebida',
        'cancelada' => 'Cancelada',
    ];

    protected $fillable = [
        'fornecedor_id', 'usuario_id', 'data', 'valor_total',
        'forma_pagamento', 'status', 'prazo_pagamento', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'datetime',
            'valor_total' => 'decimal:2',
            'prazo_pagamento' => 'integer',
        ];
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }

    public function contaPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    public function getFornecedorNomeAttribute(): string
    {
        return $this->fornecedor?->pessoa?->nome ?? 'Fornecedor não informado';
    }
}
