<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan penting: roles dulu, lalu demo data
        $this->call([
            RolesAndStagesSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
