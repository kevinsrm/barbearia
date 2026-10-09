<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ProdutoCategoriaFactory;

class ProdutoCategoria extends Model
{
    use HasFactory;

    protected $table = 'produto_categorias';
    protected $fillable = [
        'nome',
        'descricao',
        'ordem',
    ];
    
    public function produtos()
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}
