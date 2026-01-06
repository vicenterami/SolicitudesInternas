<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // Importante
use App\Events\ComentarioActualizado;
use App\Events\ComentarioEliminado;

class ComentarioController extends Controller
{
    public function update(Request $request, $id)
    {
        $comentario = Comentario::findOrFail($id);

        // AUTORIZACIÓN VÍA POLICY
        Gate::authorize('update', $comentario);

        $request->validate([
            'comentario' => 'required|string|max:1000',
        ]);

        $comentario->update([
            'comentario' => $request->comentario
        ]);

        // Disparar evento de comentario actualizado
        ComentarioActualizado::dispatch($comentario);

        return back()->with('status', 'Comentario actualizado.');
    }

    public function destroy($id)
    {
        $comentario = Comentario::findOrFail($id);

        // AUTORIZACIÓN VÍA POLICY
        Gate::authorize('delete', $comentario);

        // Guardar IDs antes de borrar
        $comentarioId = $comentario->id;
        $solicitudId = $comentario->solicitud_id;
        
        $comentario->delete();

        // Disparar evento de comentario eliminado
        ComentarioEliminado::dispatch($comentarioId, $solicitudId);

        return back()->with('status', 'Comentario eliminado.');
    }
}