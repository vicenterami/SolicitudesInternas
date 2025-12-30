<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = ['comentario', 'user_id', 'solicitud_id'];

    // Un comentario pertenece a un Usuario (el que escribió)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un comentario pertenece a una Solicitud
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }
}