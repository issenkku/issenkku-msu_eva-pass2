<?php

namespace Database\Factories;

use App\Models\AssignmentData;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AssignmentsFactory extends Factory
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
            'report_id' => Reports::factory(),
            'evaluatee_id' => User::factory(),
        ];
    }
}
