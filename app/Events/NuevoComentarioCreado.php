<?php

namespace App\Events;

use App\Models\Comentario;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoComentarioCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;

    public function __construct(Comentario $comentario)
    {
        // Cargamos el usuario para mostrar su nombre en el chat
        $this->comentario = $comentario->load('user');
    }

    public function broadcastOn(): array
    {
        // CANAL DINÁMICO: solicitud.{id}
        // Solo escuchan quienes estén dentro de este ticket
        return [
            new Channel('solicitud.' . $this->comentario->solicitud_id),
        ];
    }

    public function broadcastAs()
    {
        return 'comentario.creado';
    }
}