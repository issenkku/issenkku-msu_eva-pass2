<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ReportData;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = ['ASSIGNED', 'DRAFT', 'PENDING', 'COMPLETED'];

        return [
            'status' => fake()->randomElement($status),
            // or if you want to always use 'Assigned':
            // 'status' => 'Assigned',
            'report_data_id' => ReportData::inRandomOrder()->first()?->id,
        ];
    }
}
