<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\MachineType;
use Illuminate\Database\Seeder;

/**
 * The single machine a fresh (Docker) install ships with, ready for a NodeMCU/ESP8266 to POST
 * temperature and humidity readings to /api/machine-logs. Its device key is
 * config('app.demo_device_key') when set, otherwise a random one (printed below).
 */
class DemoMachineSeeder extends Seeder
{
    public function run(): void
    {
        $building = Building::create([
            'name' => 'Main Building',
            'code' => 'MB-001',
            'type' => 'Manufacturing',
            'status' => 'Operational',
        ]);

        $floor = Floor::create([
            'building_id' => $building->id,
            'name' => 'Ground Floor',
            'floor_number' => 0,
            'status' => 'Active',
        ]);

        $machine = Machine::create([
            'name' => 'Demo Machine',
            'machine_code' => 'DEMO-001',
            'machine_type_id' => MachineType::where('name', 'Other')->value('id'),
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'description' => 'Sample machine for testing a NodeMCU sensor. POST readings to /api/machine-logs.',
            'status' => 'active',
            'is_critical' => false,
        ]);

        MachineParameter::create([
            'machine_id' => $machine->id,
            'key' => 'temperature',
            'label' => 'Temperature',
            'unit' => '°C',
            'data_type' => 'number',
            'required' => false,
            'sort_order' => 0,
        ]);

        MachineParameter::create([
            'machine_id' => $machine->id,
            'key' => 'humidity',
            'label' => 'Humidity',
            'unit' => '%',
            'data_type' => 'number',
            'required' => false,
            'sort_order' => 1,
        ]);

        $deviceKey = $machine->generateDeviceKey(config('app.demo_device_key'));

        $this->command?->info("Demo machine DEMO-001 created. Device key: {$deviceKey}");
    }
}
