<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjunto extends Model
{
    use HasFactory;

    // CAMBIO AQUÍ: Cambiamos 'nombre_original' por 'nombre_archivo'
    protected $fillable = ['ruta_archivo', 'nombre_archivo', 'solicitud_id'];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }
}