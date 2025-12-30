<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Vicente (El Admin - Rol 3)
        User::create([
            'name' => 'Vicente Admin',
            'email' => 'admin@muni.cl',
            'password' => Hash::make('password'),
            'rol_id' => 3, 
        ]);

        // 2. Pedro (El Técnico - Rol 2)
        User::create([
            'name' => 'Pedro Tecnico',
            'email' => 'tecnico@muni.cl',
            'password' => Hash::make('password'),
            'rol_id' => 2, 
        ]);

        // 3. Juan (El Usuario Normal - Rol 1)
        User::create([
            'name' => 'Juan Ciudadano',
            'email' => 'juan@muni.cl',
            'password' => Hash::make('password'),
            'rol_id' => 1, 
        ]);
    }
}