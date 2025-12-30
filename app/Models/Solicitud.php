<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opcional si usas factories luego
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    // Tabla asociada (opcional, Laravel asume 'solicituds' o 'solicitudes', mejor asegurarnos)
    protected $table = 'solicitudes';

    // Campos que permitimos guardar masivamente
    protected $fillable = [
        'titulo',
        'descripcion',
        'prioridad',  // baja, media, alta
        'estado',     // pendiente, asignada, resuelta
        'user_id',    // ID del usuario que crea el ticket
        'tecnico_id', // ID del usuario informático asignado
    ];

    // RELACIÓN 1: El usuario normal que creó la solicitud
    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELACIÓN 2: El técnico de informática asignado
    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }
}