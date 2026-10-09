<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\PessoaFactory;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoas';
    protected $fillable = [
        'nome',
        'cpf',
        'telefone',
        'email',
        'endereco',
        'data_nascimento',
        'tipo_pessoa',
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'pessoa_id');
    }

    public function funcionario()
    {
        return $this->hasOne(Funcionario::class, 'pessoa_id');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'pessoa_id');
    }

    public function fornecedor()
    {
        return $this->hasOne(Fornecedor::class, 'pessoa_id');
    }
}
