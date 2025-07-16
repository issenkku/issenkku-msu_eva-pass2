<?php

namespace Database\Factories;

use App\Models\CriteriaVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReportDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assessment_type_arr = [
            'สายวิชาการ',
            'สายสนับสนุน',
        ];

        return [
            'report_title' => fake()->sentence(2),
            'report_description' => fake()->text(20),
            'assessment_type' => fake()->randomElement($assessment_type_arr),
            'comment' => fake()->text(20),
            'criteria_version_id' => CriteriaVersion::first()?->id,
        ];
    }
}
