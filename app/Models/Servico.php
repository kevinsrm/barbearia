<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servico extends Model
{
    protected $table = 'servicos';

    protected $fillable = [
        'servico_categoria_id', 'nome', 'descricao', 'duracao',
        'valor', 'percentual_comissao', 'ativo', 'agendamento_online',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'percentual_comissao' => 'decimal:2',
            'duracao' => 'integer',
            'ativo' => 'boolean',
            'agendamento_online' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(ServicoCategoria::class, 'servico_categoria_id');
    }

    public function agendamentos(): BelongsToMany
    {
        return $this->belongsToMany(Agendamento::class, 'agendamento_servicos')
            ->withPivot('valor')
            ->withTimestamps();
    }

    public function getValorFormatadoAttribute(): string
    {
        return 'R$ '.number_format((float) $this->valor, 2, ',', '.');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeDisponiveisOnline($query)
    {
        return $query->where('ativo', true)->where('agendamento_online', true);
    }
}
