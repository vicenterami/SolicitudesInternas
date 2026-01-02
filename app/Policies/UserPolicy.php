<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // ¿Quién puede ver la lista de usuarios?
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    // ¿Quién puede crear usuarios?
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    // ¿Quién puede actualizar a OTRO usuario?
    public function update(User $user, User $model)
    {
        return $user->isAdmin();
    }

    // ¿Quién puede eliminar a un usuario?
    public function delete(User $user, User $model)
    {
        // 1. Debe ser Admin
        // 2. Y ADEMÁS, el usuario a borrar ($model) NO puede ser él mismo ($user)
        return $user->isAdmin() && $user->id !== $model->id;
    }
}