<?php

namespace Database\Factories;

use App\Models\CriteriaVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'main_categories' => fake()->word(),
            'sub_categories' => fake()->word(),
            'sequence' => fake()->numberBetween(1, 5),
            'criteria_version_id' => CriteriaVersion::first()?->id,
        ];
    }
}
