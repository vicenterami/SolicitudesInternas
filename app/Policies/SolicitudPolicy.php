<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    // ¿Quién puede ver el detalle de una solicitud?
    public function view(User $user, Solicitud $solicitud)
    {
        // El ADMIN (3) y TÉCNICO (2) pueden ver todo.
        if ($user->rol_id === 2 || $user->rol_id === 3) {
            return true;
        }

        // El usuario normal SOLO puede ver SUS propias solicitudes.
        return $user->id === $solicitud->user_id;
    }

    // ¿Quién puede editar/gestionar una solicitud?
    public function update(User $user, Solicitud $solicitud)
    {
        // Solo Admin y Técnicos pueden editar
        return $user->rol_id === 2 || $user->rol_id === 3;
    }
}