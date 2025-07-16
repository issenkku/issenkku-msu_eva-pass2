<?php

namespace Database\Factories;

use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
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
            'sequence' => fake()->numberBetween(1, 10),
            'score_a' => fake()->numberBetween(1, 20),
            'score_b' => fake()->numberBetween(20, 400),
            'quantity_main_criteria_id' => QuantityMainCriteria::inRandomOrder()->first()?->id,
            'criteria_version_id' => CriteriaVersion::inRandomOrder()->first()?->id,
            'evaluation_list_id' => EvaluationList::inRandomOrder()->first()?->id,
        ];
    }
}
