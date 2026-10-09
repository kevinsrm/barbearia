<?php

namespace App\Models;

use App\Models\Pessoa;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\FuncionarioFactory;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionarios';
    protected $fillable = [
        'pessoa_id',
        'usuario_id',
        'cargo_id',
        'codigo_funcional',
        'data_admissao',
        'salario_base',
        'ativo',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
    
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }
    
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'funcionario_id');
    }
    
    public function comissoes()
    {
        return $this->hasMany(Comissao::class, 'funcionario_id');
    }
}
