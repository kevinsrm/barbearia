<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_FALTOU = 'faltou';
    public const STATUS_BLOQUEADO = 'bloqueado';

    public const STATUS = [
        self::STATUS_PENDENTE => 'Pendente',
        self::STATUS_CONFIRMADO => 'Confirmado',
        self::STATUS_CONCLUIDO => 'Concluído',
        self::STATUS_CANCELADO => 'Cancelado',
        self::STATUS_FALTOU => 'Não compareceu',
        self::STATUS_BLOQUEADO => 'Bloqueado',
    ];

    protected $fillable = [
        'cliente_id', 'funcionario_id', 'cliente_nome', 'cliente_telefone',
        'data', 'hora', 'duracao', 'status', 'origem',
        'valor_total', 'desconto', 'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'valor_total' => 'decimal:2',
            'desconto' => 'decimal:2',
            'duracao' => 'integer',
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

    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'agendamento_servicos')
            ->withPivot('valor')
            ->withTimestamps();
    }

    public function venda(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function comissoes(): HasMany
    {
        return $this->hasMany(Comissao::class);
    }

    public function getClienteNomeExibicaoAttribute(): string
    {
        return $this->cliente?->pessoa?->nome ?: ($this->cliente_nome ?: 'Cliente não informado');
    }

    public function getServicosTextoAttribute(): string
    {
        return $this->servicos->pluck('nome')->implode(', ');
    }

    public function getValorLiquidoAttribute(): float
    {
        return (float) $this->valor_total - (float) $this->desconto;
    }

    /** Horário de término calculado a partir da duração. */
    public function getHoraFimAttribute(): string
    {
        $inicio = \Illuminate\Support\Carbon::createFromFormat('H:i', substr((string) $this->hora, 0, 5));

        return $inicio->copy()->addMinutes((int) ($this->duracao ?: 30))->format('H:i');
    }

    public function scopeFuturos($query)
    {
        return $query->whereDate('data', '>=', today()->toDateString())
            ->whereNotIn('status', [self::STATUS_CANCELADO]);
    }

    public function scopeDoDia($query, $data = null)
    {
        $data = $data ? \Illuminate\Support\Carbon::parse($data)->toDateString() : today()->toDateString();

        return $query->whereDate('data', $data);
    }

    public function scopeOcupamHorario($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELADO, self::STATUS_FALTOU]);
    }
}
