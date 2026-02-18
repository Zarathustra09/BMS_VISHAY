<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = Building::all();

        if ($buildings->isEmpty()) {
            $this->command->info('No buildings found, skipping floor seeding.');
            return;
        }

        // Configuration by building type: count = total above ground floors (including ground as 0),
        // basement = whether to add a basement (-1)
        $config = [
            'Office' => ['above_ground' => 5, 'basement' => true],
            'Warehouse' => ['above_ground' => 2, 'basement' => false],
            'Laboratory' => ['above_ground' => 3, 'basement' => true],
            'Annex' => ['above_ground' => 2, 'basement' => false],
            'Remote' => ['above_ground' => 1, 'basement' => false],
            'Manufacturing' => ['above_ground' => 3, 'basement' => true],
            'Data Center' => ['above_ground' => 2, 'basement' => true],
            'Training' => ['above_ground' => 4, 'basement' => false],
            'Retail' => ['above_ground' => 2, 'basement' => false],
            'Support' => ['above_ground' => 3, 'basement' => false],
        ];

        foreach ($buildings as $building) {
            $type = $building->type ?? 'Other';
            $cfg = $config[$type] ?? ['above_ground' => 3, 'basement' => false];

            $floorsToCreate = [];

            // optional basement
            if (!empty($cfg['basement'])) {
                $floorsToCreate[] = [
                    'name' => 'Basement',
                    'floor_number' => -1,
                    'description' => 'Basement level',
                    'status' => 'available',
                ];
            }

            // ground floor
            $floorsToCreate[] = [
                'name' => 'Ground Floor',
                'floor_number' => 0,
                'description' => 'Main entrance level',
                'status' => 'occupied',
            ];

            // above-ground floors (1 .. above_ground-1)
            for ($i = 1; $i < $cfg['above_ground']; $i++) {
                $floorsToCreate[] = [
                    'name' => ($i === 1) ? 'First Floor' : "Floor {$i}",
                    'floor_number' => $i,
                    'description' => "Level {$i}",
                    'status' => ($i % 2 === 0) ? 'available' : 'occupied',
                ];
            }

            // create floors via relation
            foreach ($floorsToCreate as $f) {
                $building->floors()->create($f);
            }

            $this->command->info("Created " . count($floorsToCreate) . " floors for building: {$building->name}");
        }
    }
}
