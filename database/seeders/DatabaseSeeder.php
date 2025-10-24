<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Solo ejecutar seeders si las tablas están vacías
        if (\App\Models\User::count() === 0) {
            \App\Models\User::factory()->create(['email' => 'mikysama1234@gmail.com', 'password' => 'Contraseña']);
            \App\Models\User::factory(10)->create();
        }

        if (\App\Models\Category::count() === 0) {
            \App\Models\Category::factory(10)
                ->hasThreads(10)
                ->create();
        }

        if (\App\Models\Reply::count() === 0) {
            \App\Models\Reply::factory(200)->create();
        }
    }
}
