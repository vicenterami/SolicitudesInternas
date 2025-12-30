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

    // Módulo de Solicitudes (TU NUEVO CÓDIGO)
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    
    // Formulario de creación
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    
    // Guardar datos (Es POST porque enviamos datos)
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
});

// Carga las rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';