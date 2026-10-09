<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = ['pessoa_id', 'codigo_cliente', 'pontuacao', 'status', 'observacoes'];

    protected function casts(): array
    {
        return ['pontuacao' => 'integer'];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class);
    }

    public function getNomeAttribute(): string
    {
        return $this->pessoa?->nome ?? 'Cliente #'.$this->id;
    }

    public function getTelefoneAttribute(): ?string
    {
        return $this->pessoa?->telefone;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->pessoa?->email;
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
