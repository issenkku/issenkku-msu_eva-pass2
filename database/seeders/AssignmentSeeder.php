<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('assignments')->insert([
            [
                'period' => '1 มกราคม 2568 - 31 มีนาคม 2568',
                'start_time' => Carbon::parse('2025-01-01'),
                'end_time' => Carbon::parse('2025-03-31'),
                'report_id' => 1,
                'evaluatee' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'period' => '1 มกราคม 2568 - 31 มีนาคม 2568',
                'start_time' => Carbon::parse('2025-01-01'),
                'end_time' => Carbon::parse('2025-03-31'),
                'report_id' => 2,
                'evaluatee' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('evaluators')->insert([
            [
                'assignment_id' => 1,
                'user_id' => 2
            ],
            [
                'assignment_id' => 2,
                'user_id' => 2
            ],
        ]);
    }
}
