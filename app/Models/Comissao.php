<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ComissaoFactory;

class Comissao extends Model
{
    use HasFactory;

    protected $table = 'comissoes';
    protected $fillable = [
        'funcionario_id',
        'venda_id',
        'valor',
        'data',
        'status',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
    
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
