<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El orden importa: Primero roles, luego usuarios (porque usuarios dependen de roles)
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}