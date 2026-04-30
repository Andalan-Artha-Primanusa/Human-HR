<?php

namespace Database\Factories;

use App\Models\Poh;
use Illuminate\Database\Eloquent\Factories\Factory;

class PohFactory extends Factory
{
    protected $model = Poh::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city . ' Office',
            'code' => strtoupper($this->faker->unique()->lexify('???-??')),
            'address' => $this->faker->address,
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
