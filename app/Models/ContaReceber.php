<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ContaReceberFactory;

class ContaReceber extends Model
{
    use HasFactory;

    protected $table = 'contas_receber';
    protected $fillable = [
        'descricao',
        'valor',
        'vencimento',
        'status',
        'cliente_id',
        'venda_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
