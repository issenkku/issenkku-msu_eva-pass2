<?php

namespace Database\Factories;

use App\Models\EvaluationList;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvidenceAnswer>
 */
class EvidenceAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link' => fake()->url(),
            'evaluation_list_id' => EvaluationList::inRandomOrder()->first()?->id,
            'report_id' => Report::inRandomOrder()->first()?->id,
        ];
    }
}
