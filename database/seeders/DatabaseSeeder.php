<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los roles primero
        $this->call(RoleSeeder::class);

        // 2. Crear usuario Administrador (Tú)
        User::factory()->create([
            'name' => 'Vicente Admin',
            'email' => 'admin@muni.cl',
            'password' => Hash::make('password'), // La clave será "password"
            'rol_id' => 3, // Asumiendo que 3 es Admin según tu RoleSeeder
        ]);

        // 3. Crear un usuario funcionario normal
        User::factory()->create([
            'name' => 'Funcionario Muni',
            'email' => 'funcionario@muni.cl',
            'password' => Hash::make('password'),
            'rol_id' => 1, // Usuario normal
        ]);
        
        // 4. Crear un técnico de informática
         User::factory()->create([
            'name' => 'Tecnico Soporte',
            'email' => 'soporte@muni.cl',
            'password' => Hash::make('password'),
            'rol_id' => 2, // Informática
        ]);
    }
}