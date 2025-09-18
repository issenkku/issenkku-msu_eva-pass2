<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = ['นาย', 'นางสาว', 'นาง'];
        $personnelTypes = ['วิชาการ', 'สนับสนุน'];

        return [
            'prefix' => $this->faker->randomElement($prefixes),
            'name' => $this->faker->name,
            'employee_id' => str_pad($this->faker->unique()->numberBetween(8, 999), 3, '0', STR_PAD_LEFT),
            'password' => Hash::make('password123'), // default password
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'personnel_type' => $this->faker->randomElement($personnelTypes),
            'bio' => null,
            'status' => 'active',
            'position_id' => \Database\Factories\PositionFactory::new()->create(),
            'department_id' => \Database\Factories\DepartmentFactory::new()->create(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
