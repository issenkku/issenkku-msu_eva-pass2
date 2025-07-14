<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityMainCriteria;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QualitySubCriteriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(2),
            'sequence' => fake()->numberBetween(1, 10),
            'num_score' => fake()->randomFloat(2, 6, 8),
            'quality_main_criteria_id' => QualityMainCriteria::inRandomOrder()->first()?->id,
            'criteria_version_id' => CriteriaVersion::inRandomOrder()->first()?->id,
            'evaluation_list_id' => EvaluationList::inRandomOrder()->first()?->id,
        ];
    }
}
