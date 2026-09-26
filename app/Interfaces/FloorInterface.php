<?php

namespace App\Interfaces;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Collection;

interface FloorInterface
{
    /**
     * Get all floors for a building
     */
    public function getAllFloors($buildingId): Collection;

    /**
     * Get floor by ID
     */
    public function getFloorById($id): Floor;

    /**
     * Create a new floor
     */
    public function createFloor(array $data): Floor;

    /**
     * Update an existing floor
     */
    public function updateFloor($id, array $data): Floor;

    /**
     * Delete a floor
     */
    public function deleteFloor($id): bool;
}
