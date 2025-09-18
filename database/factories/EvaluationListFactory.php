<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CriteriaVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EvaluationListFactory extends Factory
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
            'sum_score' => fake()->numberBetween(1, 100),
            'sequence' => fake()->numberBetween(1, 5),
            'annotation' => fake()->text(30),
            'categorie_id' => Category::factory(),
            'criteria_version_id' => CriteriaVersion::factory(),
        ];
    }
}
