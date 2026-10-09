<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public const ROLE_ADMIN = 'admin';
    public const ROLE_GERENTE = 'gerente';
    public const ROLE_RECEPCAO = 'recepcao';
    public const ROLE_BARBEIRO = 'barbeiro';
    public const ROLE_CLIENTE = 'cliente';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_GERENTE => 'Gerente',
        self::ROLE_RECEPCAO => 'Recepção',
        self::ROLE_BARBEIRO => 'Barbeiro',
        self::ROLE_CLIENTE => 'Cliente',
    ];

    protected $fillable = [
        'pessoa_id', 'nome', 'email', 'password', 'role', 'ativo', 'ultimo_acesso',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ativo' => 'boolean',
            'email_verified_at' => 'datetime',
            'ultimo_acesso' => 'datetime',
        ];
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class);
    }

    public function cliente(): ?Cliente
    {
        return $this->pessoa?->cliente;
    }

    public function getNomeExibicaoAttribute(): string
    {
        return $this->pessoa?->nome ?: $this->nome;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Para onde mandar o usuário depois do login: o cliente do site volta para o
     * site; os demais entram no painel.
     */
    public function destinoPosLogin(): string
    {
        return $this->hasRole(self::ROLE_CLIENTE) ? route('site.index') : route('admin.dashboard');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
