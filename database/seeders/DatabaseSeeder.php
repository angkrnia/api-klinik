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
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        \App\Models\User::create([
            'fullname' => 'dr Friska Sinambela',
            'email' => 'dr.friska@gmail.com',
            'password' => bcrypt('123456'),
            'role' => DOKTER,
        ]);
        \App\Models\User::create([
            'fullname' => 'Angga kurnia',
            'email' => 'anggakurnia712@gmail.com',
            'password' => bcrypt('angga9980'),
            'role' => ADMIN,
        ]);
    }
}
