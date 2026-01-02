<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Solicitud;
use App\Models\Comentario;
use App\Models\User;
use App\Models\Role;
use App\Models\Adjunto;

class SolicitudController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->rol_id == 2 || $user->rol_id == 3) {
            $solicitudes = Solicitud::with('creador', 'tecnico')->latest()->get();
        } else {
            $solicitudes = Solicitud::where('user_id', $user->id)
                                    ->with('creador', 'tecnico')
                                    ->latest()
                                    ->get();
        }
        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        return view('solicitudes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'required|in:baja,media,alta',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $solicitud = $request->user()->solicitudes()->create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'estado' => 'pendiente',
        ]);

        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store('adjuntos', 'public'); 
            \App\Models\Adjunto::create([
                'solicitud_id' => $solicitud->id,
                'ruta_archivo' => $ruta,
                'nombre_archivo' => $request->file('archivo')->getClientOriginalName(), 
            ]);
        }

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud creada con éxito.');
    }

    // Muestra el formulario para editar
    public function edit($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        
        // CORREGIDO: Usamos Gate::authorize en vez de $this->authorize
        Gate::authorize('update', $solicitud); 

        $tecnicos = \App\Models\User::where('rol_id', 2)->get();
        return view('solicitudes.edit', compact('solicitud', 'tecnicos'));
    }

    // Actualiza en BD
    public function update(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        // CORREGIDO: ¡Muy importante! Proteger también el update, no solo el edit
        Gate::authorize('update', $solicitud); 

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,asignada,resuelta',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

        $solicitud->update([
            'estado' => $validated['estado'],
            'tecnico_id' => $validated['tecnico_id'],
        ]);

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud actualizada correctamente.');
    }

    public function show($id)
    {
        $solicitud = Solicitud::with(['comentarios.user', 'creador', 'tecnico'])->findOrFail($id);

        // CORREGIDO: Usamos Gate
        Gate::authorize('view', $solicitud); 

        return view('solicitudes.show', compact('solicitud'));
    }

    public function storeComentario(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);
        
        // CORREGIDO: También protegemos quien puede comentar (generalmente quien puede ver, puede comentar)
        Gate::authorize('view', $solicitud);

        $request->validate([
            'comentario' => 'required|string',
        ]);

        \App\Models\Comentario::create([
            'comentario' => $request->comentario,
            'user_id' => Auth::id(),
            'solicitud_id' => $solicitud->id,
        ]);

        return redirect()->route('solicitudes.show', $id)->with('status', 'Comentario agregado.');
    }
}