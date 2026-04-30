<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event' => $this->faker->randomElement(['created', 'updated', 'deleted', 'login']),
            'target_type' => $this->faker->randomElement(['App\Models\Job', 'App\Models\User']),
            'target_id' => $this->faker->uuid,
        ];
    }
}
