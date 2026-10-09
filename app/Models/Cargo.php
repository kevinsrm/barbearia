<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\CargoFactory;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';
    protected $fillable = [
        'nome',
        'descricao',
        'codigo',
    ];
    
    public function funcionarios()
    {
        return $this->hasMany(Funcionario::class, 'cargo_id');
    }
}
