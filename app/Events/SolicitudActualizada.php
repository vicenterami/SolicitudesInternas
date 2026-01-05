<?php

namespace App\Events;

use App\Models\Solicitud;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// IMPORTANTE: implements ShouldBroadcast
class SolicitudActualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitud;

    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function broadcastOn(): array
    {
        // Transmitimos al canal específico de la solicitud (para el show.blade.php)
        // Y TAMBIÉN al canal general 'solicitudes' (para el Dashboard)
        return [
            // Canal específico (para quien está viendo el show.blade.php)
            new Channel('solicitud.' . $this->solicitud->id),
            // Canal general (para el Dashboard)
            new Channel('solicitudes')
        ];
    }

    // Nombre del evento en el cliente JS que escuchará app.js
    public function broadcastAs()
    {
        return 'solicitud.actualizada';
    }
}