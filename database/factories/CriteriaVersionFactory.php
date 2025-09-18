<?php

namespace Database\Factories;

use App\Models\CriteriaVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CriteriaVersionFactory extends Factory
{
    protected $model = CriteriaVersion::class;

    public function definition(): array
    {
        return [
            'version_name' => $this->faker->words(3, true),
            'created_by' => User::factory(),
        ];
    }
}
