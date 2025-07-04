<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CriteriaVersion;
use App\Models\QualityMainCriteria;

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
            'categorie_id' => Category::inRandomOrder()->first()?->id,
            'criteria_version_id' => CriteriaVersion::inRandomOrder()->first()?->id,
        ];
    }
}
