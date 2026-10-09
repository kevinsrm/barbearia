<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ServicoCategoriaFactory;

class ServicoCategoria extends Model
{
    use HasFactory;

    protected $table = 'servico_categorias';
    protected $fillable = [
        'nome',
        'descricao',
        'ordem',
    ];
    
    public function servicos()
    {
        return $this->hasMany(Servico::class, 'servico_categoria_id');
    }
}
