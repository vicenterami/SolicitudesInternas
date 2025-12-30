<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            // 1. Datos básicos del problema
            $table->string('titulo');
            $table->text('descripcion');
            
            // Usamos enum para limitar las opciones y evitar errores de tipeo
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->enum('estado', ['pendiente', 'asignada', 'resuelta'])->default('pendiente');

            // 2. Relaciones (LA PARTE CLAVE)
            
            // El Creador (Usuario normal): Si borran al usuario, se borran sus tickets (cascade)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // El Técnico (Informático): Puede ser NULL al principio. 
            // Apunta también a la tabla 'users'.
            // Si despiden al técnico, el ticket NO se borra, solo queda sin técnico (set null)
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};