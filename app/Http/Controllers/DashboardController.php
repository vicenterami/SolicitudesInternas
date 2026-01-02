<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Solicitud;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Solicitud::query();

        // Si es usuario normal (Rol 1), solo contamos SUS tickets
        if ($user->rol_id == 1) {
            $query->where('user_id', $user->id);
        }
        // Si es Técnico/Admin, ven todo (no aplicamos filtro extra)

        // 1. Contadores para las Tarjetas (Cards)
        // Usamos clone para no afectar la query principal en cada conteo
        $total = (clone $query)->count();
        $pendientes = (clone $query)->where('estado', 'pendiente')->count();
        $asignadas = (clone $query)->where('estado', 'asignada')->count();
        $resueltas = (clone $query)->where('estado', 'resuelta')->count();

        // 2. Datos para el Gráfico (Tickets por Prioridad)
        // Esto devuelve algo como: [{'prioridad': 'alta', 'total': 5}, ...]
        $datosGrafico = (clone $query)
            ->select('prioridad', \DB::raw('count(*) as total'))
            ->groupBy('prioridad')
            ->get();
            
        // Preparamos los arrays para JS
        $labels = $datosGrafico->pluck('prioridad')->map(function($p){ return ucfirst($p); });
        $data = $datosGrafico->pluck('total');

        return view('dashboard', compact('total', 'pendientes', 'asignadas', 'resueltas', 'labels', 'data'));
    }
}