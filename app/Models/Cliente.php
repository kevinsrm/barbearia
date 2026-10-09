<?php

namespace App\Models;

use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ClienteFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $fillable = [
        'pessoa_id',
        'codigo_cliente',
        'pontuacao',
        'status',
        'observacoes',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
    
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'cliente_id');
    }
    
    public function retornos()
    {
        return $this->hasMany(ClienteRetorno::class, 'cliente_id');
    }
}
