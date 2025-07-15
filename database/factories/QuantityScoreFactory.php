<?php

namespace Database\Factories;

use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'score_C' => fake()->numberBetween(1, 5),
            'score_D' => fake()->numberBetween(1, 40),
            'quantity_sub_criteria_id' => QuantitySubCriteria::inRandomOrder()->first()?->id,
            'report_id' => Reports::inRandomOrder()->first()?->id,
        ];
    }
}
