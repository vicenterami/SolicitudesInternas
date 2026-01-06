<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ComentarioController;

// 1. Página de inicio (Pública)
Route::get('/', function () {
    // Si ya está logueado, mándalo a solicitudes, si no, al login
    return auth()->check() ? redirect()->route('solicitudes.index') : redirect()->route('login');
});

// 2. Rutas Protegidas (Solo usuarios logueados)
Route::middleware('auth')->group(function () {
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
    // Módulo de Perfil (Profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulo de Solicitudes
    Route::get('/solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    // Ver detalle (Para todos)
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    
    // Guardar comentario
    Route::post('/solicitudes/{id}/comentarios', [SolicitudController::class, 'storeComentario'])->name('solicitudes.comentarios.store');
    // Rutas para editar y borrar comentarios
    Route::put('/comentarios/{id}', [ComentarioController::class, 'update'])->name('comentarios.update');
    Route::delete('/comentarios/{id}', [ComentarioController::class, 'destroy'])->name('comentarios.destroy');

    // Módulo de Administración de Usuarios (Solo Admin)
    // Solo permitimos entrar si el middleware 'auth' pasa, la seguridad extra está en el controlador
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
    });
});

// Carga las rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';