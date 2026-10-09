<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ContaPagarFactory;

class ContaPagar extends Model
{
    use HasFactory;

    protected $table = 'contas_pagar';
    protected $fillable = [
        'descricao',
        'valor',
        'vencimento',
        'status',
        'fornecedor_id',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
