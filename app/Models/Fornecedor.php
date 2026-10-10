<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $fillable = ['pessoa_id', 'codigo_fornecedor', 'contato_comercial', 'prazo_pagamento'];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function contasPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class);
    }

    public function getNomeAttribute(): string
    {
        return $this->pessoa?->nome ?? 'Fornecedor #'.$this->id;
    }
}
