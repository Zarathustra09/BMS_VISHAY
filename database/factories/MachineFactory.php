<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Machine>
 */
class MachineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Unit',
            'machine_code' => strtoupper(fake()->unique()->bothify('MC-####')),
            'type' => fake()->randomElement(['HVAC', 'Generator', 'Elevator', 'Pump', 'Cooling Tower']),
            'building_id' => Building::factory(),
            'status' => 'active',
            'is_critical' => false,
            'autostart' => false,
            'makeup_valve' => false,
        ];
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => ['is_critical' => true]);
    }
}
