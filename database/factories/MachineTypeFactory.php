<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineType>
 */
class MachineTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucwords(fake()->unique()->words(2, true)),
            'icon' => 'cog',
            'description' => null,
            'spec_fields' => [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    public function withFields(array $fields): static
    {
        return $this->state(fn (array $attributes) => ['spec_fields' => $fields]);
    }
}
