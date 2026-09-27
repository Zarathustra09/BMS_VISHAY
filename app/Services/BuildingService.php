<?php

namespace App\Services;

use App\Interfaces\BuildingInterface;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Builder;

class BuildingService implements BuildingInterface
{
    public function getQueryForDataTable(array $filters = []): Builder
    {
        $query = Building::withCount(['floors', 'machines']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    public function getStats(): array
    {
        return [
            'total' => Building::count(),
            'operational' => Building::where('status', 'Operational')->count(),
            // Matches the old collection-sum behaviour: only machines actually
            // assigned to a building (building_id is nullable on machines).
            'machines' => Machine::whereNotNull('building_id')->count(),
            'floors' => Floor::count(),
        ];
    }

    /**
     * Get building by ID
     */
    public function getBuildingById($id): Building
    {
        return Building::with(['floors', 'machines.machineType'])->findOrFail($id);
    }

    /**
     * Create a new building
     */
    public function createBuilding(array $data): Building
    {
        return Building::create($data);
    }

    /**
     * Update an existing building
     */
    public function updateBuilding($id, array $data): Building
    {
        $building = Building::findOrFail($id);
        $building->update($data);

        return $building;
    }

    /**
     * Delete a building
     */
    public function deleteBuilding($id): bool
    {
        $building = Building::findOrFail($id);

        return $building->delete();
    }
}
