<?php

namespace App\Services;

use App\Interfaces\BuildingInterface;
use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

class BuildingService implements BuildingInterface
{
    /**
     * Get all buildings
     */
    public function getAllBuildings(): Collection
    {
        return Building::with(['floors', 'machines'])->get();
    }

    /**
     * Get building by ID
     */
    public function getBuildingById($id): Building
    {
        return Building::with(['floors', 'machines'])->findOrFail($id);
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
