<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QuantitySubCriteria;
use App\Models\Report;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuantityScore>
 */
class QuantityScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score_C' => fake()->randomFloat(2,1, 10),
            'score_D' => fake()->randomFloat(2,1, 10),
            'quantity_sub_criteria_id' => QuantitySubCriteria::inRandomOrder()->first()?->id,
            'report_id' => Report::inRandomOrder()->first()?->id,
        ];
    }
}
