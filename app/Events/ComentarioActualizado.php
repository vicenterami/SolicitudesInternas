<?php

namespace App\Events;

use App\Models\Comentario;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComentarioActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;

    public function __construct(Comentario $comentario)
    {
        $this->comentario = $comentario;
    }

    public function broadcastOn()
    {
        // Se emite en el mismo canal de la solicitud
        return new Channel('solicitud.' . $this->comentario->solicitud_id);
    }

    public function broadcastAs()
    {
        return 'comentario.actualizado';
    }
}