<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('assignment_datas')->insert([
            [
                'evaluator_position_id' => 1,
                'evaluatee_position_id' => 2,
                'start_time' => Carbon::parse('2025-01-01'),
                'end_time' => Carbon::parse('2025-03-31'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('assignments')->insert([
            [
                'assignment_data_id' => 1,
                'report_id' => 1,
                'evaluatee' => 3,
            ],
            [
                'assignment_data_id' => 1,
                'report_id' => 2,
                'evaluatee' => 4,
            ],
        ]);
    }
}
