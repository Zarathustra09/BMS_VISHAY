<?php

namespace App\Interfaces;

interface FloorInterface
{
    /**
     * Get all floors
     */
    public function getAllFloors($buildingId);

    /**
     * Get floor by ID
     */
    public function getFloorById($id);

    /**
     * Create a new floor
     */
    public function createFloor(array $data);

    /**
     * Update an existing floor
     */
    public function updateFloor($id, array $data);

    /**
     * Delete a floor
     */
    public function deleteFloor($id);
}
