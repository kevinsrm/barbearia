<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    protected $table = 'produtos';

    protected $fillable = [
        'categoria_id', 'nome', 'descricao', 'codigo', 'codigo_barras',
        'preco_custo', 'preco_venda', 'estoque', 'estoque_minimo', 'unidade', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_custo' => 'decimal:2',
            'preco_venda' => 'decimal:2',
            'estoque' => 'integer',
            'estoque_minimo' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(ProdutoCategoria::class, 'categoria_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class)->latest('data');
    }

    public function getValorFormatadoAttribute(): string
    {
        return 'R$ '.number_format((float) $this->preco_venda, 2, ',', '.');
    }

    public function getEstoqueBaixoAttribute(): bool
    {
        return $this->estoque <= $this->estoque_minimo;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeEstoqueBaixo($query)
    {
        return $query->whereColumn('estoque', '<=', 'estoque_minimo');
    }
}
