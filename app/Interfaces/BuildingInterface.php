<?php

namespace App\Interfaces;

use App\Models\Building;
use Illuminate\Database\Eloquent\Builder;

interface BuildingInterface
{
    /**
     * Query for the buildings DataTable — filtered by status/type. Replaces the old
     * unbounded getAllBuildings(); floors/machines counts come via withCount, not
     * eager-loaded full relations.
     */
    public function getQueryForDataTable(array $filters = []): Builder;

    /**
     * Dashboard stat tiles, computed with count queries rather than by summing a
     * fully-loaded collection in PHP.
     */
    public function getStats(): array;

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
