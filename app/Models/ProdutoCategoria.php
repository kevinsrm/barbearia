<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoCategoria extends Model
{
    protected $table = 'produto_categorias';

    protected $fillable = ['nome', 'descricao', 'ordem', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean', 'ordem' => 'integer'];
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
