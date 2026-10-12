<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineParameter>
 */
class MachineParameterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'key' => fake()->unique()->lexify('param_????'),
            'label' => fake()->words(2, true),
            'unit' => null,
            'data_type' => 'number',
            'required' => false,
            'sort_order' => 0,
        ];
    }

    public function key(string $key, ?string $label = null, ?string $unit = null): static
    {
        return $this->state(fn () => [
            'key' => $key,
            'label' => $label ?? ucfirst(str_replace('_', ' ', $key)),
            'unit' => $unit,
        ]);
    }

    public function boolean(): static
    {
        return $this->state(fn () => ['data_type' => 'boolean']);
    }

    public function text(): static
    {
        return $this->state(fn () => ['data_type' => 'text']);
    }

    public function required(): static
    {
        return $this->state(fn () => ['required' => true]);
    }

    public function range(float $min, float $max): static
    {
        return $this->state(fn () => ['min' => $min, 'max' => $max]);
    }

    /**
     * @param  array{warning_min?: ?float, warning_max?: ?float, critical_min?: ?float, critical_max?: ?float}  $bounds
     */
    public function withThresholds(array $bounds): static
    {
        return $this->state(fn () => $bounds);
    }
}
