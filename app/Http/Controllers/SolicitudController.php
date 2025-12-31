<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para saber quién está logueado
use App\Models\Solicitud; // Importamos el modelo para poder usarlo
use App\Models\Comentario; // Importamos el modelo Comentario
use App\Models\User; // Importamos el modelo User
use App\Models\Role; // Importamos el modelo Role

class SolicitudController extends Controller
{
    // Muestra el listado de solicitudes según el rol del usuario
    public function index()
    {
        // 1. Identificar quién está conectado
        $user = Auth::user();

        // 2. Decidir qué mostrar según el rol
        // Roles: 1=Usuario, 2=Informatica, 3=Admin
        
        if ($user->rol_id == 2 || $user->rol_id == 3) {
            // SI es Técnico o Admin: Traer TODO
            $solicitudes = Solicitud::with('creador', 'tecnico')->latest()->get();
        } else {
            // SI es Usuario Normal: Traer SOLO lo suyo (where user_id = mi id)
            $solicitudes = Solicitud::where('user_id', $user->id)
                                    ->with('creador', 'tecnico')
                                    ->latest()
                                    ->get();
        }
        
        // 3. Retornar la vista
        return view('solicitudes.index', compact('solicitudes'));
    }
    // Muestra el formulario para crear una nueva solicitud
    public function create()
    {
        return view('solicitudes.create');
    }
    // Guarda una nueva solicitud en la base de datos
    public function store(Request $request)
    {
        // 1. Validar que los datos vengan bien
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'required|in:baja,media,alta', // Solo permitimos estos valores
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Validación del archivo
        ]);

        // 1. Crear la solicitud
        $solicitud = $request->user()->solicitudes()->create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'estado' => 'pendiente',
        ]);

        // 2. Si viene un archivo, guardarlo
        if ($request->hasFile('archivo')) {
            // Guarda en storage/app/public/adjuntos
            $ruta = $request->file('archivo')->store('adjuntos', 'public'); 

            // Registrar en la base de datos
            \App\Models\Adjunto::create([
                'solicitud_id' => $solicitud->id,
                'ruta_archivo' => $ruta,
                'nombre_archivo' => $request->file('archivo')->getClientOriginalName(), 
            ]);
        }

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud creada con éxito.');
    }

    // Muestra el formulario para editar/gestionar una solicitud.
    public function edit($id)
    {
        // 1. Buscar la solicitud por ID
        $solicitud = Solicitud::findOrFail($id);

        // 2. Seguridad: ¿Quién puede editar?
        // Admin y Técnico pueden editar. El usuario normal NO debería editar el estado ni asignarse técnicos.
        // Por ahora, dejaremos entrar a Admin y Técnicos.
        $user = Auth::user();
        if ($user->rol_id == 1) { // 1 = Usuario normal
             return redirect()->route('solicitudes.index')->with('error', 'No tienes permiso para editar.');
        }

        // 3. Obtener lista de técnicos (rol_id = 2) para el menú desplegable
        // Necesitamos importar el modelo User arriba: use App\Models\User;
        $tecnicos = \App\Models\User::where('rol_id', 2)->get();

        return view('solicitudes.edit', compact('solicitud', 'tecnicos'));
    }

    //Actualiza la solicitud en la base de datos
    public function update(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        // Validamos los datos que llegan
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,asignada,resuelta',
            'tecnico_id' => 'nullable|exists:users,id', // Puede ser null o un ID válido
        ]);

        // Actualizamos
        $solicitud->update([
            'estado' => $validated['estado'],
            'tecnico_id' => $validated['tecnico_id'],
        ]);

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud actualizada correctamente.');
    }

    //Muestra el detalle de una solicitud y sus comentarios.

    public function show($id)
    {
        // Buscamos la solicitud
        $solicitud = Solicitud::with(['comentarios.user', 'creador', 'tecnico'])->findOrFail($id);

        // Seguridad: Si soy Juan, NO debería poder ver los tickets de otro usuario.
        $user = Auth::user();
        if ($user->rol_id == 1 && $solicitud->user_id != $user->id) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        return view('solicitudes.show', compact('solicitud'));
    }

    // Guarda un nuevo comentario en una solicitud
    public function storeComentario(Request $request, $id)
    {
        // Validamos 'comentario'
        $request->validate([
            'comentario' => 'required|string',
        ]);

        $solicitud = Solicitud::findOrFail($id);

        \App\Models\Comentario::create([
            'comentario' => $request->comentario, // CAMBIADO de contenido a comentario
            'user_id' => Auth::id(),
            'solicitud_id' => $solicitud->id,
        ]);

        return redirect()->route('solicitudes.show', $id)->with('status', 'Comentario agregado.');
    }
}