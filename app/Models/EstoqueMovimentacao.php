<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\EstoqueMovimentacaoFactory;

class EstoqueMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'estoque_movimentacoes';
    protected $fillable = [
        'produto_id',
        'tipo',
        'quantidade',
        'valor',
        'data',
        'usuario_id',
        'fornecedor_id',
        'observacoes',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
    
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
