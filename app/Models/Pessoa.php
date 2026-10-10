<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pessoa extends Model
{
    protected $table = 'pessoas';

    public const TIPOS = [
        'cliente' => 'Cliente',
        'funcionario' => 'Funcionário',
        'fornecedor' => 'Fornecedor',
        'usuario' => 'Usuário',
    ];

    protected $fillable = [
        'nome', 'tipo_pessoa', 'cpf', 'telefone', 'email', 'data_nascimento',
        'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep',
        'observacoes', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class);
    }

    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class);
    }

    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    public function fornecedor(): HasOne
    {
        return $this->hasOne(Fornecedor::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo_pessoa] ?? ucfirst((string) $this->tipo_pessoa);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo_pessoa', $tipo);
    }

    public function scopeAniversariantesDoMes($query, ?int $mes = null)
    {
        $mes = $mes ?? now()->month;

        return $query->whereNotNull('data_nascimento')->whereMonth('data_nascimento', $mes);
    }
}
