<?php

namespace App\Interfaces;

use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

interface BuildingInterface
{
    /**
     * Get all buildings
     */
    public function getAllBuildings(): Collection;

    /**
     * Get building by ID
     */
    public function getBuildingById($id): Building;

    /**
     * Create a new building
     */
    public function createBuilding(array $data): Building;

    /**
     * Update an existing building
     */
    public function updateBuilding($id, array $data): Building;

    /**
     * Delete a building
     */
    public function deleteBuilding($id): bool;
}
