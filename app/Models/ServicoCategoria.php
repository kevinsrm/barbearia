<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicoCategoria extends Model
{
    protected $table = 'servico_categorias';

    protected $fillable = ['nome', 'descricao', 'ordem', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean', 'ordem' => 'integer'];
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class, 'servico_categoria_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
