<?php

namespace Database\Factories;

use App\Models\AssignmentData;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_data_id' => AssignmentData::factory(),
            'report_id' => Report::factory(),
            'evaluatee' => User::factory(),
            'evaluator' => User::factory(),
        ];
    }
}
