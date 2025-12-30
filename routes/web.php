<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

// 1. Página de inicio (Pública)
Route::get('/', function () {
    // Si ya está logueado, mándalo a solicitudes, si no, al login
    return auth()->check() ? redirect()->route('solicitudes.index') : redirect()->route('login');
});

// 2. Rutas Protegidas (Solo usuarios logueados)
Route::middleware('auth')->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulo de Perfil (Profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulo de Solicitudes
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

    // NUEVAS RUTAS:
    // 1. Mostrar formulario de edición
    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    // 2. Guardar los cambios
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');

    // Ver detalle (Para todos)
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    
    // Guardar comentario
    Route::post('/solicitudes/{id}/comentarios', [SolicitudController::class, 'storeComentario'])->name('solicitudes.comentarios.store');
});

// Carga las rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';