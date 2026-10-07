<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineLog>
 */
class MachineLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'log_type' => 'sensor_reading',
            'severity' => 'info',
            'readings' => ['temperature' => fake()->randomFloat(2, 15, 35)],
            'logged_at' => now(),
        ];
    }
}
