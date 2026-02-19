<?php

namespace App\Services;

use App\Interfaces\BuildingInterface;
use App\Models\Building;

class BuildingService implements BuildingInterface
{
    /**
     * Get all buildings
     */
    public function getAllBuildings()
    {
        return Building::with(['floors', 'machines'])->get();
    }

    /**
     * Get building by ID
     */
    public function getBuildingById($id)
    {
        return Building::with(['floors', 'machines'])->findOrFail($id);
    }

    /**
     * Create a new building
     */
    public function createBuilding(array $data)
    {
        return Building::create($data);
    }

    /**
     * Update an existing building
     */
    public function updateBuilding($id, array $data)
    {
        $building = Building::findOrFail($id);
        $building->update($data);
        return $building;
    }

    /**
     * Delete a building
     */
    public function deleteBuilding($id)
    {
        $building = Building::findOrFail($id);
        return $building->delete();
    }
}
