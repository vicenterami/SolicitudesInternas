<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate; // No olvides importar esto
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        // Autorizamos ver la lista (método viewAny de UserPolicy)
        Gate::authorize('viewAny', User::class);

        $users = User::with('rol')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        // Autorizamos crear (método create de UserPolicy)
        Gate::authorize('create', User::class);
        
        $roles = Role::all(); 
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $userToEdit = User::findOrFail($id);

        // Autorizamos actualizar ESTE usuario específico
        Gate::authorize('update', $userToEdit);

        $roles = Role::all();

        // CORRECCIÓN: Usamos un array asociativo para mapear los datos
        return view('admin.users.edit', [
            'user' => $userToEdit, // Aquí decimos: "En la vista se llamará 'user', pero toma el valor de '$userToEdit'"
            'roles' => $roles
        ]);
    }

    public function update(Request $request, $id)
    {
        $userToUpdate = User::findOrFail($id);

        Gate::authorize('update', $userToUpdate);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($userToUpdate->id)],
            'rol_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $userToUpdate->name = $request->name;
        $userToUpdate->email = $request->email;
        $userToUpdate->rol_id = $request->rol_id;

        if ($request->filled('password')) {
            $userToUpdate->password = Hash::make($request->password);
        }

        $userToUpdate->save();

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $userToDelete = User::findOrFail($id);

        // UX: Mantenemos este check manual solo para dar feedback amigable al usuario
        if (auth()->id() == $userToDelete->id) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }

        // SEGURIDAD REAL: La Policy también valida esto.
        // Si alguien intenta saltarse el if anterior (ej: vía API), esto lo detiene.
        Gate::authorize('delete', $userToDelete);

        $userToDelete->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado correctamente.');
    }
}