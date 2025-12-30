<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Borramos para evitar duplicados si corres el seeder solo
        DB::table('roles')->delete();

        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'usuario'],      // El ciudadano de a pie
            ['id' => 2, 'nombre' => 'informatica'],  // El técnico
            ['id' => 3, 'nombre' => 'admin'],        // El jefe (Tú)
        ]);
    }
}