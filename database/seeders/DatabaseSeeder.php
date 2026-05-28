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

        $this->call([
            PositionSeeder::class,
            JobLeveSeederl::class,
            DepartmentSeeder::class,
            SubjectSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            ReportStructureRealSeeder::class,
        ]);
    }
}
