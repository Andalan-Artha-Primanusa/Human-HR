<?php

namespace Database\Factories;

use App\Models\McuTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class McuTemplateFactory extends Factory
{
    protected $model = McuTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'company_name' => $this->faker->company,
            'is_active' => false,
        ];
    }
}
