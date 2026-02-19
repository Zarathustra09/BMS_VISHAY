<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildingService;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    protected $buildingService;

    public function __construct(BuildingService $buildingService)
    {
        $this->buildingService = $buildingService;
    }

    public function index()
    {
        $buildings = $this->buildingService->getAllBuildings();

        // Calculate statistics
        $totalBuildings = $buildings->count();
        $operationalBuildings = $buildings->where('status', 'Operational')->count();
        $totalOccupancy = $buildings->sum(function($building) {
            return $building->machines->count();
        });

        return view('buildings.index', compact('buildings', 'totalBuildings', 'operationalBuildings', 'totalOccupancy'));
    }

    public function show($id)
    {
        $building = $this->buildingService->getBuildingById($id);
        return view('buildings.show', compact('building'));
    }

    public function create()
    {
        return view('buildings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:buildings,code',
            'type' => 'required|string|in:Production Facility,Warehouse,Office Building,R&D Center,Distribution Center',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:Operational,Under Construction,Maintenance,Inactive',
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Building name is required.',
            'code.required' => 'Building code is required.',
            'code.unique' => 'This building code already exists.',
            'type.required' => 'Please select a building type.',
            'type.in' => 'Invalid building type selected.',
            'status.required' => 'Please select a building status.',
            'status.in' => 'Invalid building status selected.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a jpeg, png, jpg, or gif file.',
            'image.max' => 'Image size must not exceed 2MB.',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('buildings', 'public');
        }

        $this->buildingService->createBuilding($validated);

        return redirect()->route('buildings.index')
            ->with('success', 'Building "' . $validated['name'] . '" created successfully!');
    }

    public function edit($id)
    {
        $building = $this->buildingService->getBuildingById($id);
        return view('buildings.edit', compact('building'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:buildings,code,' . $id,
            'type' => 'required|string|in:Production Facility,Warehouse,Office Building,R&D Center,Distribution Center',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:Operational,Under Construction,Maintenance,Inactive',
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Building name is required.',
            'code.required' => 'Building code is required.',
            'code.unique' => 'This building code already exists.',
            'type.required' => 'Please select a building type.',
            'type.in' => 'Invalid building type selected.',
            'status.required' => 'Please select a building status.',
            'status.in' => 'Invalid building status selected.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a jpeg, png, jpg, or gif file.',
            'image.max' => 'Image size must not exceed 2MB.',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $building = $this->buildingService->getBuildingById($id);

            // Delete old image if exists
            if ($building->image && Storage::disk('public')->exists($building->image)) {
                Storage::disk('public')->delete($building->image);
            }

            $validated['image'] = $request->file('image')->store('buildings', 'public');
        }

        $this->buildingService->updateBuilding($id, $validated);

        return redirect()->route('buildings.show', $id)
            ->with('success', 'Building "' . $validated['name'] . '" updated successfully!');
    }

    public function confirmDelete($id)
    {
        $building = $this->buildingService->getBuildingById($id);
        return view('buildings.delete', compact('building'));
    }

    public function destroy(Request $request, $id)
    {
        $building = $this->buildingService->getBuildingById($id);
        $buildingName = $building->name ?? 'Building';

        $request->validate([
            'confirmation_name' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($buildingName) {
                    if (trim($value) !== $buildingName) {
                        $fail('The building name does not match. Please type "' . $buildingName . '" exactly to confirm deletion.');
                    }
                },
            ],
            'acknowledge1' => ['required', 'accepted'],
            'acknowledge2' => ['required', 'accepted'],
            'acknowledge3' => ['required', 'accepted'],
        ], [
            'acknowledge1.required' => 'You must acknowledge that this action cannot be undone.',
            'acknowledge1.accepted' => 'You must acknowledge that this action cannot be undone.',
            'acknowledge2.required' => 'You must acknowledge that all floors will be deleted.',
            'acknowledge2.accepted' => 'You must acknowledge that all floors will be deleted.',
            'acknowledge3.required' => 'You must acknowledge that all machines will be deleted.',
            'acknowledge3.accepted' => 'You must acknowledge that all machines will be deleted.',
        ]);

        // Count related records before deletion
        $floorCount = $building->floors?->count() ?? 0;
        $machineCount = $building->machines?->count() ?? 0;

        // Delete image if exists
        if ($building->image && Storage::disk('public')->exists($building->image)) {
            Storage::disk('public')->delete($building->image);
        }

        $this->buildingService->deleteBuilding($id);

        return redirect()->route('buildings.index')
            ->with('success', "Building \"$buildingName\" and its $floorCount floor(s) and $machineCount machine(s) have been permanently deleted.");
    }
}
