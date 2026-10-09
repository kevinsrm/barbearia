<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ServicoFactory;

class Servico extends Model
{
    use HasFactory;

    protected $table = 'servicos';
    protected $fillable = [
        'nome',
        'descricao',
        'duracao',
        'valor',
        'servico_categoria_id',
        'ativo',
    ];

    public function categoria()
    {
        return $this->belongsTo(ServicoCategoria::class, 'servico_categoria_id');
    }
    
    public function agendamentos()
    {
        return $this->hasMany(AgendamentoServico::class, 'servico_id');
    }
    
    public function valor()
    {
        return $this->hasOne(ServicoValor::class, 'servico_id');
    }
}
