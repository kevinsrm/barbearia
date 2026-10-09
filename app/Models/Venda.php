<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\VendaFactory;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';
    protected $fillable = [
        'cliente_id',
        'funcionario_id',
        'data',
        'valor_total',
        'forma_pagamento',
        'status',
        'observacoes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
    
    public function itens()
    {
        return $this->hasMany(VendaItem::class);
    }
    
    public function scopeVendasDiarias($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('data', $date);
    }
}
