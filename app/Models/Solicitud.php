<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'titulo', 'descripcion', 'prioridad', 'estado', 'user_id', 'tecnico_id',
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'solicitud_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class, 'solicitud_id');
    }

    // Obtener las clases CSS para el badge según el estado
    public function getColorClaseAttribute()
    {
        return match ($this->estado) {
            'pendiente' => 'bg-red-100 text-red-800 border-red-200',
            'asignada'  => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'resuelta'  => 'bg-green-100 text-green-800 border-green-200',
            default     => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    // Obtener el nombre del estado con primera letra mayúscula
    public function getNombreEstadoAttribute()
    {
        return ucfirst($this->estado);
    }
}