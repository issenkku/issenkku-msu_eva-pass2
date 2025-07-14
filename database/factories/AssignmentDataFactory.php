<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssignmentData>
 */
class AssignmentDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $endTime = Carbon::instance($startTime)->addDays($this->faker->numberBetween(1, 7));

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
