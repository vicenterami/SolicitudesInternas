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
use App\Events\NuevaSolicitudCreada;
use App\Events\NuevoComentarioCreado;
use App\Events\SolicitudActualizada;

class SolicitudController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // LÓGICA SEMÁNTICA:
        // El controlador decide QUÉ datos traer basándose en el tipo de usuario.
        
        if ($user->esPersonalTecnico()) {
            // Si es TI, traemos TODO (usando el modelo limpio)
            $solicitudes = Solicitud::with('creador', 'tecnico')->latest()->get();
        } else {
            // Si es Cliente, usamos la RELACIÓN de Eloquent (Laravel filtra solo)
            $solicitudes = $user->solicitudes()->with('creador', 'tecnico')->latest()->get();
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

        event(new NuevaSolicitudCreada($solicitud));

        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store('adjuntos', 'public'); 
            Adjunto::create([
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
        
        // Usamos Gate::authorize en vez de $this->authorize
        Gate::authorize('update', $solicitud); 

        // Traemos los técnicos para el select, usamos el helper del modelo User
        $tecnicos = User::where('rol_id', 2)->get();  

        return view('solicitudes.edit', compact('solicitud', 'tecnicos'));
    }

    // Actualiza en BD
    public function update(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        Gate::authorize('update', $solicitud); 

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,asignada,resuelta',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

        $solicitud->update([
            'estado' => $validated['estado'],
            'tecnico_id' => $validated['tecnico_id'],
        ]);

        event(new SolicitudActualizada($solicitud->load('tecnico', 'creador'))); // Cargamos relaciones para el JS

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud actualizada correctamente.');
    }

    public function show($id)
    {
        $solicitud = Solicitud::with(['comentarios.user', 'creador', 'tecnico'])->findOrFail($id);

        Gate::authorize('view', $solicitud); 

        return view('solicitudes.show', compact('solicitud'));
    }

    public function storeComentario(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);
        
        // Protegemos quien puede comentar (generalmente quien puede ver, puede comentar)
        Gate::authorize('view', $solicitud);

        $request->validate([
            'comentario' => 'required|string',
        ]);

        $comentario = Comentario::create([
            'comentario' => $request->comentario,
            'user_id' => Auth::id(),
            'solicitud_id' => $solicitud->id,
        ]);

        // DISPARAMOS EL EVENTO A LA COLA
        event(new NuevoComentarioCreado($comentario));

        return redirect()->route('solicitudes.show', $id)->with('status', 'Comentario agregado.');
    }
}