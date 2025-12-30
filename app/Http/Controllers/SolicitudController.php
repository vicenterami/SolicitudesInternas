<?php

namespace App\Http\Controllers;

use App\Models\Solicitud; // Importamos el modelo para poder usarlo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para saber quién está logueado

class SolicitudController extends Controller
{
    /**
     * Muestra la lista de solicitudes.
     */
    public function index()
    {
        // 1. Obtener las solicitudes de la base de datos.
        // Aquí usamos "Eloquent" (el ORM de Laravel).
        // "with('creador')" sirve para traer los datos del usuario de una vez (optimización).
        $solicitudes = Solicitud::with('creador', 'tecnico')->latest()->get();        
        // NOTA: Como aún no creamos solicitudes, esto vendrá vacío, pero la lógica es esta.
        
        // 2. Retornar una VISTA (el HTML) y enviarle los datos.
        // 'solicitudes.index' significa: carpeta "solicitudes", archivo "index.blade.php"
        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Muestra el formulario para crear una nueva solicitud.
     */
    public function create()
    {
        return view('solicitudes.create');
    }

    /**
     * Guarda la solicitud en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validar que los datos vengan bien
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'prioridad' => 'required|in:baja,media,alta', // Solo permitimos estos valores
        ]);

        // 2. Crear la solicitud usando la relación con el usuario
        // Esto equivale a: "Al usuario logueado, créale una solicitud con estos datos"
        $request->user()->solicitudes()->create([
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'],
            'prioridad' => $validated['prioridad'],
            'estado' => 'pendiente', // Correcto: Coincide con la migración
        ]);

        // 3. Redirigir al listado con un mensaje de éxito
        return redirect()->route('solicitudes.index')->with('status', 'Solicitud creada correctamente.');
    }

}