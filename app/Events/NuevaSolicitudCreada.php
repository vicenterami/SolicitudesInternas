<?php

namespace App\Events;

use App\Models\Solicitud;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <--- IMPORTANTE
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Agregamos "implements ShouldBroadcast"
class NuevaSolicitudCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitud;

    // Recibimos la solicitud que se acaba de crear.
    public function __construct(Solicitud $solicitud)
    {
        // Cargamos las relaciones necesarias para el front-end.
        $this->solicitud = $solicitud->load('creador', 'tecnico');
    }

    // Definimos en qué canal se transmitirá.
    // Por ahora usaremos un canal público llamado 'solicitudes' para probar fácil.    
    public function broadcastOn(): array
    {
        return [
            new Channel('solicitudes'),
        ];
    }
    
     //(Opcional) El nombre del evento en JS.
     //Si no lo pones, será el nombre de la clase completo.
    public function broadcastAs()
    {
        return 'solicitud.creada';
    }
}