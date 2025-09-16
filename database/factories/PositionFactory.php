<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Setting\Positions;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting\Positions>
 */
class PositionFactory extends Factory
{
    protected $model = Positions::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
