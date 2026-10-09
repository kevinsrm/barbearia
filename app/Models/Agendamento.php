<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\AgendamentoFactory;

class Agendamento extends Model
{
    use HasFactory;

    protected $table = 'agendamentos';
    protected $fillable = [
        'cliente_id',
        'funcionario_id',
        'data',
        'hora',
        'status',
        'observacoes',
        'valor_total',
        'desconto',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
    
    public function servicos()
    {
        return $this->hasMany(AgendamentoServico::class);
    }
    
    public function scopeAgendamentosFuturos($query)
    {
        return $query->where('data', '>=', now()->toDateString());
    }
}
