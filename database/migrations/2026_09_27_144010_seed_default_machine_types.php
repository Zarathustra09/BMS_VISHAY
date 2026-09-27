<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Seeds the nine machine types the app previously hard-coded in the (now-removed)
     * App\Enums\MachineType, with the same spec fields each type's machine form used to show
     * — see docs/03-data-model.md. Seeding here, rather than in a seeder class, means every
     * environment has these rows before any machine references a machine_type_id, including
     * the SQLite :memory: database RefreshDatabase builds fresh for each test.
     *
     * Field keys equal the old dedicated columns on `machines` (capacity_tons, fuel, etc.) so
     * a later migration that backfills existing machines' data into `specifications` can map
     * one to one.
     */
    public function up(): void
    {
        $now = now();

        $types = [
            [
                'name' => 'Chiller',
                'icon' => '🧊',
                'spec_fields' => [
                    ['key' => 'capacity_tons', 'label' => 'Capacity', 'type' => 'number', 'unit' => 'tons', 'step' => '0.01', 'min' => 0, 'required' => false],
                ],
            ],
            [
                'name' => 'Cooling Tower',
                'icon' => '🏗️',
                'spec_fields' => [
                    ['key' => 'capacity_tons', 'label' => 'Capacity', 'type' => 'number', 'unit' => 'tons', 'step' => '0.01', 'min' => 0, 'required' => false],
                    ['key' => 'cells', 'label' => 'Cells', 'type' => 'number', 'unit' => null, 'step' => '1', 'min' => 0, 'required' => false],
                    ['key' => 'makeup_valve', 'label' => 'Makeup Valve', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Generator',
                'icon' => '⚡',
                'spec_fields' => [
                    ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'text', 'required' => false],
                    ['key' => 'autostart', 'label' => 'Autostart', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Elevator',
                'icon' => '🛗',
                'spec_fields' => [
                    ['key' => 'capacity_persons', 'label' => 'Capacity', 'type' => 'number', 'unit' => 'persons', 'step' => '1', 'min' => 0, 'required' => false],
                    ['key' => 'speed_m_s', 'label' => 'Speed', 'type' => 'number', 'unit' => 'm/s', 'step' => '0.01', 'min' => 0, 'required' => false],
                ],
            ],
            [
                'name' => 'Pump',
                'icon' => '💧',
                'spec_fields' => [
                    ['key' => 'flow_lpm', 'label' => 'Flow', 'type' => 'number', 'unit' => 'L/min', 'step' => '1', 'min' => 0, 'required' => false],
                    ['key' => 'head_m', 'label' => 'Head', 'type' => 'number', 'unit' => 'm', 'step' => '0.01', 'min' => 0, 'required' => false],
                ],
            ],
            [
                'name' => 'HVAC',
                'icon' => '🌡️',
                'spec_fields' => [
                    ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Compressor',
                'icon' => '🔧',
                'spec_fields' => [
                    ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Boiler',
                'icon' => '🔥',
                'spec_fields' => [
                    ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Other',
                'icon' => '⚙️',
                'spec_fields' => [
                    ['key' => 'fuel', 'label' => 'Fuel', 'type' => 'text', 'required' => false],
                ],
            ],
        ];

        foreach ($types as $type) {
            DB::table('machine_types')->insert([
                'name' => $type['name'],
                'icon' => $type['icon'],
                'description' => null,
                'spec_fields' => json_encode($type['spec_fields']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Only removes rows this migration itself created (matched by name) — a type an admin
     * has since edited (renamed, or given different fields) is left alone, since rolling back
     * this migration shouldn't discard real user changes.
     */
    public function down(): void
    {
        DB::table('machine_types')->whereIn('name', [
            'Chiller', 'Cooling Tower', 'Generator', 'Elevator', 'Pump',
            'HVAC', 'Compressor', 'Boiler', 'Other',
        ])->delete();
    }
};
