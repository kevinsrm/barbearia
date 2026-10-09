<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ProdutoFactory;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';
    protected $fillable = [
        'nome',
        'descricao',
        'codigo',
        'preco_custo',
        'preco_venda',
        'estoque_minimo',
        'unidade',
        'categoria_id',
        'ativo',
    ];

    public function categoria()
    {
        return $this->belongsTo(ProdutoCategoria::class, 'categoria_id');
    }
    
    public function estoque()
    {
        return $this->hasOne(Estoque::class, 'produto_id');
    }
    
    public function movimentacoes()
    {
        return $this->hasMany(EstoqueMovimentacao::class, 'produto_id');
    }
    
    public function vendas()
    {
        return $this->hasMany(Venda::class, 'produto_id');
    }
}
