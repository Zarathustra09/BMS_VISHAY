<?php

namespace App\Interfaces;

interface BuildingInterface
{
        /**
        * Get all buildings
        */
        public function getAllBuildings();

        /**
        * Get building by ID
        */
        public function getBuildingById($id);

        /**
        * Create a new building
        */
        public function createBuilding(array $data);

        /**
        * Update an existing building
        */
        public function updateBuilding($id, array $data);

        /**
        * Delete a building
        */
        public function deleteBuilding($id);
}
