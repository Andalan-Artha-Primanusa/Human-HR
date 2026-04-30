<?php

namespace Database\Factories;

use App\Models\ManpowerRequirement;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManpowerRequirementFactory extends Factory
{
    protected $model = ManpowerRequirement::class;

    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'asset_name' => $this->faker->word,
            'assets_count' => $this->faker->numberBetween(1, 10),
            'ratio_per_asset' => $this->faker->randomFloat(1, 1, 5),
            'filled_headcount' => 0,
        ];
    }
}
