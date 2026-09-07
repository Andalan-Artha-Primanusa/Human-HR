<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'code' => strtoupper($this->faker->unique()->bothify('??-###')),
            'division' => strtoupper($this->faker->lexify('???')),
            'description' => $this->faker->paragraph,
            'status' => 'open',
            'level' => $this->faker->randomElement(\App\Models\Job::LEVELS),
            'employment_type' => 'fulltime',
        ];
    }
}
