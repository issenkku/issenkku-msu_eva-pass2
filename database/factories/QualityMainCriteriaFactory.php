<?php

namespace Database\Factories;

use App\Models\CriteriaVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QualityMainCriteriaFactory extends Factory
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
            'ratio' => fake()->numberBetween(20, 100),
            'tooltips' => fake()->text(30),
            'criteria_version_id' => CriteriaVersion::factory(),
        ];
    }
}
