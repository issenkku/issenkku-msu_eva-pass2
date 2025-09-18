<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Setting\Settings;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting\Settings>
 */
class SettingsFactory extends Factory
{
    protected $model = Settings::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faculty' => $this->faker->name,
            'university' => $this->faker->name,
            'notification_days' => fake()->numberBetween(1,7)
        ];
    }
}
