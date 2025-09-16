<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Setting\Departments;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting\Departments>
 */
class DepartmentFactory extends Factory
{
    protected $model = Departments::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_name' => $this->faker->name
        ];
    }
}
