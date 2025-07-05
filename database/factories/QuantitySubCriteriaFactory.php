<?php

namespace Database\Factories;

use App\Models\QuantityMainCriteria;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QuantitySubCriteriaFactory extends Factory
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
            'sequence' => fake()->numberBetween(1,10),
            'score_a' => fake()->randomFloat(2, 1, 10),
            'score_b' => fake()->randomFloat(2, 1, 10),
            'quantity_main_criteria_id' => QuantityMainCriteria::inRandomOrder()->first()?->id,
            'criteria_version_id' => CriteriaVersion::inRandomOrder()->first()?->id,
            'evaluation_list_id' => EvaluationList::inRandomOrder()->first()?->id,
        ];
    }
}
