<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->company,
            'region' => $this->faker->state,
            'timezone' => 'Asia/Jakarta',
            'address' => $this->faker->address,
            'is_active' => true,
            'meta' => ['type' => 'test'],
            'notes' => $this->faker->sentence,
            'latitude' => $this->faker->latitude(-8, 6),
            'longitude' => $this->faker->longitude(95, 141),
        ];
    }
}
