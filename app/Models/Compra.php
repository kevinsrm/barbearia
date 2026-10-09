<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\CompraFactory;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';
    protected $fillable = [
        'fornecedor_id',
        'data',
        'valor_total',
        'forma_pagamento',
        'status',
        'observacoes',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
    
    public function itens()
    {
        return $this->hasMany(CompraItem::class);
    }
}
