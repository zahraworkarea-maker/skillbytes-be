<?php

namespace Database\Factories;

use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        
        return [
            'slug' => str($title)->slug('-'),
            'title' => $title,
            'description' => $this->faker->paragraph(),
            'time_limit' => $this->faker->randomElement([15, 30, 45, 60]),
        ];
    }
}
