<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Machine;
use App\Models\MachineType;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MachineAndLogSeeder extends Seeder
{
    /**
     * A hand-written type's raw name normalised to how MachineType rows are actually named —
     * only "CoolingTower" (no space) disagrees with the seeded default types.
     */
    private const TYPE_NAME_ALIASES = [
        'CoolingTower' => 'Cooling Tower',
    ];

    /**
     * The old spec-column keys this seeder's $machines array still writes, and the icon a
     * firstOrCreate()'d MachineType should get if it doesn't already exist.
     *
     * @var array<int, string>
     */
    private const SPEC_KEYS = [
        'capacity_tons', 'fuel', 'autostart', 'capacity_persons',
        'speed_m_s', 'flow_lpm', 'head_m', 'cells', 'makeup_valve',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $typeCache = [];
        $statuses = ['active', 'inactive', 'maintenance', 'fault'];
        $logTypes = ['status_change', 'maintenance', 'alarm', 'sensor_reading', 'operation', 'error'];
        $severities = ['info', 'warning', 'error', 'critical'];
        $performedByOptions = ['system', 'John Technician', 'Mary Engineer', 'Mike Maintenance', 'External Vendor', null];

        // Get existing buildings
        $buildings = Building::all();
        if ($buildings->isEmpty()) {
            $this->command->info('No buildings found, skipping machine seeding.');

            return;
        }

        // map machine type -> preferred building types
        $typeToBuildingTypes = [
            'HVAC' => ['Office', 'Manufacturing', 'Data Center', 'Training'],
            'AHU' => ['Office', 'Data Center', 'Training'],
            'Generator' => ['Manufacturing', 'Warehouse', 'Data Center', 'Support'],
            'Compressor' => ['Manufacturing', 'Warehouse'],
            'Chiller' => ['Manufacturing', 'Data Center'],
            'Boiler' => ['Manufacturing'],
            'Pump' => ['Manufacturing', 'Warehouse'],
            'CoolingTower' => ['Manufacturing', 'Data Center'],
            'CNC Machine' => ['Manufacturing'],
            'Injection Molding' => ['Manufacturing'],
            'Robotic Arm' => ['Manufacturing'],
            'Conveyor' => ['Manufacturing'],
            'Elevator' => ['Office', 'Manufacturing', 'Support'],
            'Transformer' => ['Manufacturing', 'Data Center'],
            'UPS' => ['Data Center', 'Office'],
            'AHU' => ['Office', 'Data Center', 'Training'],
        ];

        // Define 20 diverse machines for manufacturing BMS (location removed)
        $machines = [
            // HVAC Systems
            [
                'name' => 'Main Production HVAC Unit',
                'machine_code' => 'HVAC-PROD-01',
                'type' => 'HVAC',
                'manufacturer' => 'Trane',
                'model_number' => 'CVH-1500',
                'serial_number' => 'TRN-HVAC-2019-001',
                'description' => 'Primary climate control for main production area',
                'status' => 'active',
                'power_rating' => 125.00,
                'ip_address' => '192.168.10.11',
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 90,
                'capacity_tons' => 100.00,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Assembly Area HVAC',
                'machine_code' => 'HVAC-ASSY-02',
                'type' => 'HVAC',
                'manufacturer' => 'Carrier',
                'model_number' => 'AquaEdge 19DV',
                'serial_number' => 'CAR-HVAC-2020-045',
                'description' => 'Climate control for precision assembly operations',
                'status' => 'active',
                'power_rating' => 85.50,
                'ip_address' => '192.168.10.12',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(1)->toDateString(),
                'maintenance_interval_days' => 90,
                'capacity_tons' => 65.00,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],

            // Generators
            [
                'name' => 'Emergency Backup Generator 1',
                'machine_code' => 'GEN-EMERG-01',
                'type' => 'Generator',
                'manufacturer' => 'Cummins',
                'model_number' => 'C1000D5',
                'serial_number' => 'CUM-GEN-2018-101',
                'description' => 'Primary emergency power backup for entire facility',
                'status' => 'active',
                'power_rating' => 1000.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(5)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 60,
                'fuel' => 'diesel',
                'autostart' => true,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Backup Generator 2',
                'machine_code' => 'GEN-BAK-02',
                'type' => 'Generator',
                'manufacturer' => 'Caterpillar',
                'model_number' => 'CAT-750',
                'serial_number' => 'CAT-GEN-2019-055',
                'description' => 'Secondary backup generator',
                'status' => 'active',
                'power_rating' => 750.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(1)->toDateString(),
                'maintenance_interval_days' => 60,
                'fuel' => 'natural_gas',
                'autostart' => true,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],

            // Air Compressors
            [
                'name' => 'Main Air Compressor',
                'machine_code' => 'COMP-MAIN-01',
                'type' => 'Compressor',
                'manufacturer' => 'Atlas Copco',
                'model_number' => 'GA-315',
                'serial_number' => 'ATC-COMP-2020-078',
                'description' => 'Primary compressed air supply for pneumatic tools',
                'status' => 'active',
                'power_rating' => 315.00,
                'ip_address' => '192.168.10.20',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subWeeks(3)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 120,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Backup Air Compressor',
                'machine_code' => 'COMP-BAK-02',
                'type' => 'Compressor',
                'manufacturer' => 'Ingersoll Rand',
                'model_number' => 'R-Series 250',
                'serial_number' => 'IR-COMP-2021-033',
                'description' => 'Backup compressed air system',
                'status' => 'active',
                'power_rating' => 250.00,
                'ip_address' => '192.168.10.21',
                'installation_date' => Carbon::now()->subYears(2)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subWeeks(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 120,
                'spec_voltage' => 480.00,
                'is_critical' => false,
            ],

            // Chillers
            [
                'name' => 'Process Chiller Unit 1',
                'machine_code' => 'CHILL-PROC-01',
                'type' => 'Chiller',
                'manufacturer' => 'Daikin',
                'model_number' => 'WME-Z3000',
                'serial_number' => 'DAI-CHILL-2019-012',
                'description' => 'Main process cooling for manufacturing equipment',
                'status' => 'active',
                'power_rating' => 450.00,
                'ip_address' => '192.168.10.30',
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 90,
                'capacity_tons' => 300.00,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],

            // Boilers
            [
                'name' => 'Steam Boiler 1',
                'machine_code' => 'BOIL-STEAM-01',
                'type' => 'Boiler',
                'manufacturer' => 'Cleaver-Brooks',
                'model_number' => 'CB-700-500',
                'serial_number' => 'CB-BOIL-2017-089',
                'description' => 'Primary steam generation for process heating',
                'status' => 'active',
                'power_rating' => 500.00,
                'ip_address' => '192.168.10.40',
                'installation_date' => Carbon::now()->subYears(6)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(1)->toDateString(),
                'maintenance_interval_days' => 90,
                'fuel' => 'natural_gas',
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],

            // Pumps
            [
                'name' => 'Chilled Water Circulation Pump 1',
                'machine_code' => 'PUMP-CHW-01',
                'type' => 'Pump',
                'manufacturer' => 'Grundfos',
                'model_number' => 'NB-200',
                'serial_number' => 'GRU-PUMP-2020-156',
                'description' => 'Primary chilled water circulation',
                'status' => 'active',
                'power_rating' => 22.50,
                'ip_address' => '192.168.10.50',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(3)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 180,
                'flow_lpm' => 1500,
                'head_m' => 45.00,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Process Water Pump 2',
                'machine_code' => 'PUMP-PROC-02',
                'type' => 'Pump',
                'manufacturer' => 'Xylem',
                'model_number' => 'e-SV 50',
                'serial_number' => 'XYL-PUMP-2021-067',
                'description' => 'Process water supply pump',
                'status' => 'active',
                'power_rating' => 18.75,
                'ip_address' => '192.168.10.51',
                'installation_date' => Carbon::now()->subYears(2)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(4)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 180,
                'flow_lpm' => 800,
                'head_m' => 30.00,
                'spec_voltage' => 380.00,
                'is_critical' => false,
            ],

            // Cooling Towers
            [
                'name' => 'Cooling Tower North',
                'machine_code' => 'CT-NORTH-01',
                'type' => 'CoolingTower',
                'manufacturer' => 'Baltimore Aircoil',
                'model_number' => 'VTI-1500',
                'serial_number' => 'BAC-CT-2018-044',
                'description' => 'Main cooling tower for process heat rejection',
                'status' => 'active',
                'power_rating' => 75.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(5)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(3)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 180,
                'cells' => 4,
                'makeup_valve' => true,
                'is_critical' => true,
            ],
            [
                'name' => 'Cooling Tower South',
                'machine_code' => 'CT-SOUTH-02',
                'type' => 'CoolingTower',
                'manufacturer' => 'Evapco',
                'model_number' => 'AT-1200',
                'serial_number' => 'EVA-CT-2019-088',
                'description' => 'Secondary cooling tower',
                'status' => 'active',
                'power_rating' => 60.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(4)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 180,
                'cells' => 3,
                'makeup_valve' => true,
                'is_critical' => false,
            ],

            // Manufacturing Equipment
            [
                'name' => 'CNC Machining Center 1',
                'machine_code' => 'CNC-MC-01',
                'type' => 'CNC Machine',
                'manufacturer' => 'Haas',
                'model_number' => 'VF-6/50',
                'serial_number' => 'HAAS-CNC-2020-234',
                'description' => '5-axis CNC machining center',
                'status' => 'active',
                'power_rating' => 45.00,
                'ip_address' => '192.168.20.10',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subWeeks(6)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 120,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Injection Molding Machine A',
                'machine_code' => 'INJ-MOLD-A',
                'type' => 'Injection Molding',
                'manufacturer' => 'Engel',
                'model_number' => 'e-victory 500',
                'serial_number' => 'ENG-INJ-2019-077',
                'description' => 'High-precision injection molding machine',
                'status' => 'active',
                'power_rating' => 95.00,
                'ip_address' => '192.168.20.20',
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 90,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Robotic Arm Assembly Unit 3',
                'machine_code' => 'ROBOT-ASM-03',
                'type' => 'Robotic Arm',
                'manufacturer' => 'ABB',
                'model_number' => 'IRB 6700',
                'serial_number' => 'ABB-ROB-2021-145',
                'description' => '6-axis industrial robot for assembly operations',
                'status' => 'active',
                'power_rating' => 12.50,
                'ip_address' => '192.168.20.30',
                'installation_date' => Carbon::now()->subYears(2)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(4)->toDateString(),
                'maintenance_interval_days' => 180,
                'spec_voltage' => 380.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Conveyor Belt System Main',
                'machine_code' => 'CONV-MAIN-01',
                'type' => 'Conveyor',
                'manufacturer' => 'Dorner',
                'model_number' => '2200 Series',
                'serial_number' => 'DOR-CONV-2020-099',
                'description' => 'Main production line conveyor system',
                'status' => 'active',
                'power_rating' => 28.00,
                'ip_address' => '192.168.20.40',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
                'maintenance_interval_days' => 90,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],

            // Elevators
            [
                'name' => 'Freight Elevator A',
                'machine_code' => 'ELEV-FRT-A',
                'type' => 'Elevator',
                'manufacturer' => 'Otis',
                'model_number' => 'GeN2-MRL',
                'serial_number' => 'OTIS-ELEV-2018-234',
                'description' => 'Heavy-duty freight elevator',
                'status' => 'active',
                'power_rating' => 35.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(5)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(5)->toDateString(),
                'maintenance_interval_days' => 180,
                'capacity_persons' => 20,
                'speed_m_s' => 1.50,
                'spec_voltage' => 480.00,
                'is_critical' => false,
            ],

            // Electrical Systems
            [
                'name' => 'Main Transformer T1',
                'machine_code' => 'TRANS-MAIN-T1',
                'type' => 'Transformer',
                'manufacturer' => 'ABB',
                'model_number' => 'RESIBLOC 2500',
                'serial_number' => 'ABB-TRANS-2017-056',
                'description' => 'Main power distribution transformer',
                'status' => 'active',
                'power_rating' => 2500.00,
                'ip_address' => null,
                'installation_date' => Carbon::now()->subYears(6)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(6)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(6)->toDateString(),
                'maintenance_interval_days' => 365,
                'spec_voltage' => 13800.00,
                'is_critical' => true,
            ],
            [
                'name' => 'UPS System Main',
                'machine_code' => 'UPS-MAIN-01',
                'type' => 'UPS',
                'manufacturer' => 'Eaton',
                'model_number' => '9395',
                'serial_number' => 'EAT-UPS-2019-123',
                'description' => 'Uninterruptible power supply for critical systems',
                'status' => 'active',
                'power_rating' => 550.00,
                'ip_address' => '192.168.10.100',
                'installation_date' => Carbon::now()->subYears(4)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(3)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(3)->toDateString(),
                'maintenance_interval_days' => 180,
                'spec_voltage' => 480.00,
                'is_critical' => true,
            ],
            [
                'name' => 'Air Handling Unit Floor 2',
                'machine_code' => 'AHU-FL2-01',
                'type' => 'AHU',
                'manufacturer' => 'York',
                'model_number' => 'YVAA-120',
                'serial_number' => 'YORK-AHU-2020-188',
                'description' => 'Air handling unit for office areas',
                'status' => 'active',
                'power_rating' => 45.00,
                'ip_address' => '192.168.10.70',
                'installation_date' => Carbon::now()->subYears(3)->toDateString(),
                'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(1)->toDateString(),
                'maintenance_interval_days' => 90,
                'capacity_tons' => 40.00,
                'spec_voltage' => 480.00,
                'is_critical' => false,
            ],
        ];

        $logsPerMachine = 500;
        $insertChunkSize = 250;

        foreach ($machines as $mData) {
            // determine building for this machine type
            $preferredTypes = $typeToBuildingTypes[$mData['type']] ?? null;
            $building = null;

            if ($preferredTypes) {
                $building = $buildings->first(function ($b) use ($preferredTypes) {
                    return in_array($b->type, $preferredTypes);
                });
            }

            // fallback to any building
            if (! $building) {
                $building = $buildings->random();
            }

            // pick a random floor for that building if available
            $floor = $building->floors()->inRandomOrder()->first();

            // attach ids to payload
            $mData['building_id'] = $building->id;
            $mData['floor_id'] = $floor ? $floor->id : null;

            $machineType = $mData['type'];
            $mData['machine_type_id'] = $this->resolveMachineTypeId($machineType, $typeCache);
            $mData['specifications'] = $this->extractSpecifications($mData);
            unset($mData['type']);

            // create machine (location string removed)
            $machine = Machine::create($mData);

            $logs = [];

            for ($i = 0; $i < $logsPerMachine; $i++) {
                // Spread logs over last 120 days with varied distribution
                $minutesBack = $this->generateVariedTimestamp();
                $loggedAt = Carbon::now()->subMinutes($minutesBack);

                $logType = $logTypes[array_rand($logTypes)];
                $severity = $this->generateSeverity();

                // Status change logic
                $previousStatus = null;
                $currentStatus = null;
                if ($logType === 'status_change') {
                    $previousStatus = $statuses[array_rand($statuses)];
                    $currentStatus = $statuses[array_rand(array_diff($statuses, [$previousStatus]))];
                }

                // Generate sensor readings based on machine type
                $sensorData = $this->generateSensorData($machineType, $mData['power_rating'] ?? 100, $faker);

                $logs[] = array_merge([
                    'machine_id' => $machine->id,
                    'log_type' => $logType,
                    'event' => $this->generateEvent($machineType, $logType, $faker),
                    'severity' => $severity,
                    'previous_status' => $previousStatus,
                    'current_status' => $currentStatus,
                    'notes' => $faker->optional(0.4)->sentence(12),
                    'performed_by' => $performedByOptions[array_rand($performedByOptions)],
                    'logged_at' => $loggedAt->toDateTimeString(),
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ], $sensorData);

                if (count($logs) >= $insertChunkSize) {
                    DB::table('machine_logs')->insert($logs);
                    $logs = [];
                }
            }

            if (! empty($logs)) {
                DB::table('machine_logs')->insert($logs);
            }
        }
    }

    /**
     * Finds or creates the MachineType for one of this seeder's hand-written `type` strings,
     * caching the lookup for the rest of the run.
     *
     * @param  array<string, MachineType>  $typeCache
     */
    private function resolveMachineTypeId(string $rawType, array &$typeCache): int
    {
        $name = self::TYPE_NAME_ALIASES[$rawType] ?? $rawType;

        if (! isset($typeCache[$name])) {
            $typeCache[$name] = MachineType::firstOrCreate(
                ['name' => $name],
                ['icon' => 'cog', 'spec_fields' => []]
            );
        }

        return $typeCache[$name]->id;
    }

    /**
     * Pulls this seeder's old spec-column keys out of $mData (which otherwise has no fillable
     * home for them any more) and returns them as the `specifications` JSON payload.
     *
     * @param  array<string, mixed>  $mData
     * @return array<string, mixed>
     */
    private function extractSpecifications(array &$mData): array
    {
        $specifications = [];

        foreach (self::SPEC_KEYS as $key) {
            if (! array_key_exists($key, $mData)) {
                continue;
            }

            $value = $mData[$key];
            unset($mData[$key]);

            if ($value === null) {
                continue;
            }

            $specifications[$key] = in_array($key, ['autostart', 'makeup_valve'], true)
                ? ($value ? 'Yes' : 'No')
                : $value;
        }

        return $specifications;
    }

    /**
     * Generate varied timestamp distribution
     */
    private function generateVariedTimestamp(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 40) {
            // 40% recent (last 7 days)
            return rand(0, 7 * 24 * 60);
        } elseif ($rand <= 70) {
            // 30% medium (7-30 days)
            return rand(7 * 24 * 60, 30 * 24 * 60);
        } else {
            // 30% older (30-120 days)
            return rand(30 * 24 * 60, 120 * 24 * 60);
        }
    }

    /**
     * Generate severity with realistic distribution
     */
    private function generateSeverity(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 65) {
            return 'info';
        }
        if ($rand <= 88) {
            return 'warning';
        }
        if ($rand <= 97) {
            return 'error';
        }

        return 'critical';
    }

    /**
     * Generate sensor data based on machine type
     */
    private function generateSensorData(string $type, float $powerRating, $faker): array
    {
        $data = [
            'temperature' => null,
            'humidity' => null,
            'pressure' => null,
            'power_consumption' => null,
            'voltage' => null,
            'current' => null,
            'frequency' => null,
            'runtime_hours' => null,
            'vibration_rms' => null,
            'bearing_temp' => null,
            'oil_level' => null,
        ];

        switch ($type) {
            case 'HVAC':
            case 'AHU':
                $data['temperature'] = $faker->optional(0.95)->randomFloat(2, 18, 28);
                $data['humidity'] = $faker->optional(0.90)->randomFloat(2, 30, 70);
                $data['power_consumption'] = $faker->optional(0.85)->randomFloat(2, $powerRating * 0.3, $powerRating * 0.9);
                $data['voltage'] = $faker->optional(0.70)->randomFloat(2, 470, 490);
                $data['current'] = $faker->optional(0.70)->randomFloat(2, 50, 300);
                $data['frequency'] = $faker->optional(0.60)->randomFloat(2, 59.5, 60.5);
                $data['runtime_hours'] = $faker->optional(0.50)->numberBetween(5000, 50000);
                $data['vibration_rms'] = $faker->optional(0.40)->randomFloat(3, 0.5, 5.0);
                break;

            case 'Generator':
                $data['temperature'] = $faker->optional(0.90)->randomFloat(2, 60, 95);
                $data['power_consumption'] = $faker->optional(0.95)->randomFloat(2, 0, $powerRating);
                $data['voltage'] = $faker->optional(0.95)->randomFloat(2, 470, 490);
                $data['current'] = $faker->optional(0.95)->randomFloat(2, 100, 1500);
                $data['frequency'] = $faker->optional(0.90)->randomFloat(2, 59.8, 60.2);
                $data['runtime_hours'] = $faker->optional(0.85)->numberBetween(1000, 20000);
                $data['oil_level'] = $faker->optional(0.80)->randomFloat(2, 60, 100);
                $data['bearing_temp'] = $faker->optional(0.75)->randomFloat(2, 50, 90);
                $data['vibration_rms'] = $faker->optional(0.70)->randomFloat(3, 1.0, 8.0);
                break;

            case 'Compressor':
                $data['temperature'] = $faker->optional(0.90)->randomFloat(2, 70, 110);
                $data['pressure'] = $faker->optional(0.95)->randomFloat(2, 6, 10);
                $data['power_consumption'] = $faker->optional(0.90)->randomFloat(2, $powerRating * 0.4, $powerRating * 0.95);
                $data['voltage'] = $faker->optional(0.80)->randomFloat(2, 470, 490);
                $data['current'] = $faker->optional(0.80)->randomFloat(2, 200, 700);
                $data['runtime_hours'] = $faker->optional(0.75)->numberBetween(10000, 80000);
                $data['vibration_rms'] = $faker->optional(0.70)->randomFloat(3, 2.0, 12.0);
                $data['bearing_temp'] = $faker->optional(0.65)->randomFloat(2, 60, 100);
                $data['oil_level'] = $faker->optional(0.60)->randomFloat(2, 50, 100);
                break;

            case 'Pump':
                $data['temperature'] = $faker->optional(0.85)->randomFloat(2, 40, 80);
                $data['pressure'] = $faker->optional(0.90)->randomFloat(2, 2, 8);
                $data['power_consumption'] = $faker->optional(0.85)->randomFloat(2, $powerRating * 0.5, $powerRating * 0.90);
                $data['voltage'] = $faker->optional(0.75)->randomFloat(2, 370, 490);
                $data['current'] = $faker->optional(0.75)->randomFloat(2, 30, 150);
                $data['runtime_hours'] = $faker->optional(0.70)->numberBetween(15000, 100000);
                $data['vibration_rms'] = $faker->optional(0.65)->randomFloat(3, 1.5, 9.0);
                $data['bearing_temp'] = $faker->optional(0.55)->randomFloat(2, 45, 85);
                break;

            case 'Chiller':
            case 'Boiler':
                $data['temperature'] = $faker->optional(0.95)->randomFloat(2, $type === 'Chiller' ? 5 : 80, $type === 'Chiller' ? 15 : 120);
                $data['pressure'] = $faker->optional(0.90)->randomFloat(2, 1, 12);
                $data['power_consumption'] = $faker->optional(0.90)->randomFloat(2, $powerRating * 0.4, $powerRating * 0.85);
                $data['voltage'] = $faker->optional(0.80)->randomFloat(2, 470, 490);
                $data['current'] = $faker->optional(0.80)->randomFloat(2, 300, 1000);
                $data['runtime_hours'] = $faker->optional(0.75)->numberBetween(8000, 60000);
                $data['vibration_rms'] = $faker->optional(0.50)->randomFloat(3, 1.0, 6.0);
                break;

            case 'CNC Machine':
            case 'Injection Molding':
            case 'Robotic Arm':
                $data['temperature'] = $faker->optional(0.80)->randomFloat(2, 30, 70);
                $data['power_consumption'] = $faker->optional(0.85)->randomFloat(2, $powerRating * 0.2, $powerRating * 0.95);
                $data['voltage'] = $faker->optional(0.75)->randomFloat(2, 370, 490);
                $data['current'] = $faker->optional(0.75)->randomFloat(2, 20, 200);
                $data['runtime_hours'] = $faker->optional(0.80)->numberBetween(5000, 40000);
                $data['vibration_rms'] = $faker->optional(0.70)->randomFloat(3, 0.3, 4.0);
                break;

            case 'Conveyor':
                $data['temperature'] = $faker->optional(0.70)->randomFloat(2, 25, 55);
                $data['power_consumption'] = $faker->optional(0.80)->randomFloat(2, $powerRating * 0.3, $powerRating * 0.85);
                $data['voltage'] = $faker->optional(0.70)->randomFloat(2, 470, 490);
                $data['current'] = $faker->optional(0.70)->randomFloat(2, 15, 80);
                $data['runtime_hours'] = $faker->optional(0.75)->numberBetween(20000, 120000);
                $data['vibration_rms'] = $faker->optional(0.60)->randomFloat(3, 0.5, 3.5);
                break;

            case 'CoolingTower':
                $data['temperature'] = $faker->optional(0.90)->randomFloat(2, 20, 35);
                $data['humidity'] = $faker->optional(0.85)->randomFloat(2, 60, 95);
                $data['power_consumption'] = $faker->optional(0.75)->randomFloat(2, $powerRating * 0.3, $powerRating * 0.80);
                $data['voltage'] = $faker->optional(0.70)->randomFloat(2, 370, 490);
                $data['runtime_hours'] = $faker->optional(0.70)->numberBetween(10000, 70000);
                break;

            default:
                $data['temperature'] = $faker->optional(0.60)->randomFloat(2, 20, 80);
                $data['power_consumption'] = $faker->optional(0.70)->randomFloat(2, 0, $powerRating * 0.75);
                $data['voltage'] = $faker->optional(0.60)->randomFloat(2, 370, 490);
                $data['runtime_hours'] = $faker->optional(0.50)->numberBetween(1000, 50000);
                break;
        }

        return $data;
    }

    /**
     * Generate contextual event description
     */
    private function generateEvent(string $type, string $logType, $faker): string
    {
        $events = [
            'status_change' => [
                'System started successfully',
                'Emergency shutdown initiated',
                'Switched to backup mode',
                'Normal operation resumed',
                'Maintenance mode activated',
            ],
            'maintenance' => [
                'Scheduled maintenance completed',
                'Filter replacement performed',
                'Oil change completed',
                'Bearing lubrication done',
                'Preventive maintenance check',
                'Sensor calibration completed',
            ],
            'alarm' => [
                'High temperature alarm',
                'Low pressure warning',
                'Vibration threshold exceeded',
                'Power fluctuation detected',
                'Abnormal noise detected',
                'Communication timeout',
            ],
            'sensor_reading' => [
                'Routine sensor data collection',
                'Periodic monitoring cycle',
                'Scheduled data logging',
                'Performance metrics recorded',
            ],
            'operation' => [
                'Load increase detected',
                'Operating parameters adjusted',
                'Cycle completed successfully',
                'Production batch finished',
                'Efficiency optimization applied',
            ],
            'error' => [
                'Communication error occurred',
                'Sensor reading anomaly',
                'Control system fault',
                'Motor overcurrent detected',
                'Timeout error',
            ],
        ];

        $typeEvents = $events[$logType] ?? ['System event logged'];

        return $typeEvents[array_rand($typeEvents)];
    }
}
