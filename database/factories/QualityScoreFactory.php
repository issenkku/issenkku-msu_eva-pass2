<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QualitySubCriteria;
use App\Models\Reports;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QualityScore>
 */
class QualityScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score' => fake()->randomFloat(2, 1, 10),
            'quality_sub_criteria_id' => QualitySubCriteria::inRandomOrder()->first()?->id,
            'report_id' => Reports::inRandomOrder()->first()?->id,
        ];
    }
}
