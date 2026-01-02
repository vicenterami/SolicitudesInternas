<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    // ¿Quién puede ver la lista general? (El index)
    // Todos pueden entrar al index, pero el controlador decide qué datos mostrar.
    public function viewAny(User $user)
    {
        return true;
    }

    // ¿Quién puede ver UN detalle específico?
    public function view(User $user, Solicitud $solicitud)
    {
        // Usamos el helper nuevo del modelo User, para leer mejor
        if ($user->esPersonalTecnico()) {
            return true;
        }
        return $user->id === $solicitud->user_id;
    }

    // ¿Quién puede editar?
    public function update(User $user, Solicitud $solicitud)
    {
        return $user->esPersonalTecnico();
    }
}