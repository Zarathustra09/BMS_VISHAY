<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\Request;
use App\Interfaces\FloorInterface;
use Illuminate\Support\Facades\Validator;

class FloorController extends Controller
{
    public function __construct(protected FloorInterface $floorService) {}

    public function index(Request $request)
    {
        $floors = Floor::when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($request->expectsJson()) {
            return response()->json($floors);
        }

        // fallback if needed
        return response()->json($floors);
    }

    /**
     * Store a newly created floor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $floor = $this->floorService->createFloor($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Floor created successfully',
                'floor' => $floor->load('machines')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create floor'], 500);
        }
    }

    /**
     * Display the specified floor
     */
    public function show($id)
    {
        try {
            $floor = $this->floorService->getFloorById($id);
            return response()->json(['floor' => $floor]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Floor not found'], 404);
        }
    }

    /**
     * Update the specified floor
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $floor = $this->floorService->updateFloor($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Floor updated successfully',
                'floor' => $floor->load('machines')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update floor'], 500);
        }
    }

    /**
     * Remove the specified floor
     */
    public function destroy($id)
    {
        try {
            $this->floorService->deleteFloor($id);
            return response()->json([
                'success' => true,
                'message' => 'Floor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete floor'], 500);
        }
    }

}
