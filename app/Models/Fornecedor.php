<?php

namespace App\Models;

use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\FornecedorFactory;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';
    protected $fillable = [
        'pessoa_id',
        'codigo_fornecedor',
        'contato_comercial',
        'prazo_pagamento',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
    
    public function compras()
    {
        return $this->hasMany(Compra::class, 'fornecedor_id');
    }
}
