<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    protected $fillable = [
        'pessoa_id', 'usuario_id', 'cargo_id', 'codigo_funcional',
        'data_admissao', 'salario_base', 'percentual_comissao', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_admissao' => 'date',
            'salario_base' => 'decimal:2',
            'percentual_comissao' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function comissoes(): HasMany
    {
        return $this->hasMany(Comissao::class);
    }

    public function getNomeAttribute(): string
    {
        return $this->pessoa?->nome ?? 'Funcionário #'.$this->id;
    }

    public function getTelefoneAttribute(): ?string
    {
        return $this->pessoa?->telefone;
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /** Percentual de comissão efetivo (o do funcionário ou o padrão da configuração). */
    public function percentualComissaoEfetivo(): float
    {
        $proprio = (float) $this->percentual_comissao;

        return $proprio > 0 ? $proprio : (float) config('barbearia.comissao_padrao', 0);
    }
}
