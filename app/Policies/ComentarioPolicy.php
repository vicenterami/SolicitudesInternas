<?php

namespace App\Policies;

use App\Models\Comentario;
use App\Models\User;

class ComentarioPolicy
{
    // ¿Quién puede actualizar (editar)? Solo el dueño.
    public function update(User $user, Comentario $comentario)
    {
        return $user->id === $comentario->user_id;
    }

    // ¿Quién puede eliminar? El dueño O un Administrador (para moderar insultos, etc).
    public function delete(User $user, Comentario $comentario)
    {
        return $user->id === $comentario->user_id || $user->isAdmin();
    }
}