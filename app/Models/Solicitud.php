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
}