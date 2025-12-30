<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 🔑 RELACIÓN CON ROL
    public function rol()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Helpers útiles
    public function isAdmin(): bool
    {
        return $this->rol?->nombre === 'admin';
    }

    public function isInformatica(): bool
    {
        return $this->rol?->nombre === 'informatica';
    }

    // RELACIÓN CON SOLICITUDES CREADAS POR EL USUARIO
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'user_id');
    }

}
