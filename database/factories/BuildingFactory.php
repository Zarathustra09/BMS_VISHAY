<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Building',
            'code' => strtoupper(fake()->unique()->bothify('BLD-##')),
            'type' => fake()->randomElement(['manufacturing', 'warehouse', 'office']),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'status' => 'active',
        ];
    }
}
