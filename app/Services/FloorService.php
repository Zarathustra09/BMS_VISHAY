<?php

namespace App\Services;

use App\Models\Floor;
use App\Interfaces\FloorInterface;

class FloorService implements FloorInterface
{
        /**
        * Get all floors for a building
        */
        public function getAllFloors($buildingId)
        {
            return Floor::where('building_id', $buildingId)->with('machines')->orderBy('floor_number')->get();
        }

        /**
        * Get floor by ID
        */
        public function getFloorById($id)
        {
            return Floor::with('machines')->findOrFail($id);
        }

        /**
        * Create a new floor
        */
        public function createFloor(array $data)
        {
            return Floor::create($data);
        }

        /**
        * Update an existing floor
        */
        public function updateFloor($id, array $data)
        {
            $floor = Floor::findOrFail($id);
            $floor->update($data);
            return $floor;
        }

        /**
        * Delete a floor
        */
        public function deleteFloor($id)
        {
            $floor = Floor::findOrFail($id);
            return $floor->delete();
        }

}
