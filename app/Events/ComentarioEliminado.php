<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComentarioEliminado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentarioId;
    public $solicitudId;

    // Solo pasamos IDs porque el comentario ya no existirá en la BD
    public function __construct($comentarioId, $solicitudId)
    {
        $this->comentarioId = $comentarioId;
        $this->solicitudId = $solicitudId;
    }

    public function broadcastOn()
    {
        return new Channel('solicitud.' . $this->solicitudId);
    }

    public function broadcastAs()
    {
        return 'comentario.eliminado';
    }
}