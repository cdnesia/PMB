<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ProdiSeeder::class,
            DemoDataSeeder::class,
            WilayahSeeder::class,
            AgamaSeeder::class,
            JenisKelaminSeeder::class,
            PekerjaanSeeder::class,
            SumberInformasiSeeder::class,
        ]);
    }
}
