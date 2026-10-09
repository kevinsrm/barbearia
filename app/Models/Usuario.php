<?php

namespace App\Models;

use App\Models\Pessoa;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'pessoa_id',
        'email',
        'password',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function funcionario()
    {
        return $this->hasOne(Funcionario::class, 'usuario_id');
    }
}
