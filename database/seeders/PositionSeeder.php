<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('positions')->insert([
            [
                'name' => 'ศาสตราจารย์',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองศาสตราจารย์',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ผู้ช่วยศาสตราจารย์',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าแผนก',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'หัวหน้าวิชาการ',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'คณบดี',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'รองคณบดี',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
