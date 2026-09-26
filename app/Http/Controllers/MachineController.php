<?php

namespace App\Http\Controllers;

use App\Interfaces\MachineInterface;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MachineController extends Controller
{
    public function __construct(protected MachineInterface $machineService) {}

    public function index(Request $request)
    {
        $filters   = $request->only(['search', 'status', 'type', 'building_id', 'floor_id']);
        $machines  = $this->machineService->getAllPaginated($filters);
        $stats     = $this->machineService->getStats();
        $types     = $this->machineService->getDistinctTypes();
        $buildings = Building::select('id', 'name')->orderBy('name')->get();

        return view('machines.index', compact('machines', 'stats', 'types', 'buildings'));
    }

    public function create()
    {
        $buildings = Building::select('id', 'name')->orderBy('name')->get();

        return view('machines.create', compact('buildings'));
    }

    public function store(Request $request)
    {
        Log::info('Creating machine with data: ' . json_encode($request->all()));
        $validated = $this->validateMachine($request);

        $machine = $this->machineService->create($validated);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine created successfully.');
    }

    public function show(Machine $machine)
    {
        $machine->load(['building', 'floor']);

        $logs                = $this->machineService->getLogsForMachine($machine);
        $logStats            = $this->machineService->getLogStats($machine);
        $recentAlerts        = $this->machineService->getRecentAlerts($machine);
        $logTypeDistribution = $this->machineService->getLogTypeDistribution($machine);

        $canViewDeviceKey = auth()->user()->can('machines.view-device-key');
        $deviceKeyPlain   = $canViewDeviceKey ? $machine->decryptDeviceKey() : null;

        return view('machines.show', compact(
            'machine', 'logs', 'logStats', 'recentAlerts', 'logTypeDistribution',
            'canViewDeviceKey', 'deviceKeyPlain'
        ));
    }

    public function edit(Machine $machine)
    {
        $buildings = Building::select('id', 'name')->orderBy('name')->get();
        $floors    = $machine->building_id
            ? Floor::where('building_id', $machine->building_id)->get()
            : collect();

        return view('machines.edit', compact('machine', 'buildings', 'floors'));
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $this->validateMachine($request, $machine->id);

        $this->machineService->update($machine, $validated);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine updated successfully.');
    }

    public function confirmDelete(Machine $machine)
    {
        return view('machines.delete', compact('machine'));
    }

    public function destroy(Machine $machine)
    {
        $this->machineService->delete($machine);

        return redirect()->route('machines.index')
            ->with('success', 'Machine deleted successfully.');
    }

    /**
     * Generate (or regenerate) this machine's device API key.
     * Route is gated by the `machines.view-device-key` permission — generating
     * a key necessarily reveals it, so the same permission covers both.
     */
    public function generateDeviceKey(Machine $machine)
    {
        $plainKey = $machine->generateDeviceKey();

        return redirect()->route('machines.show', $machine)
            ->with('device_key_plain', $plainKey);
    }

    private function validateMachine(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:255',
            'machine_code'              => 'required|string|max:100|unique:machines,machine_code' . ($ignoreId ? ",{$ignoreId}" : ''),
            'type'                      => 'required|string|max:100',
            'manufacturer'              => 'nullable|string|max:255',
            'model_number'              => 'nullable|string|max:255',
            'serial_number'             => 'nullable|string|max:255',
            'building_id'               => 'nullable|exists:buildings,id',
            'floor_id'                  => 'nullable|exists:floors,id',
            'description'               => 'nullable|string',
            'status'                    => 'required|in:active,inactive,maintenance,fault',
            'power_rating'              => 'nullable|numeric|min:0',
            'ip_address'                => 'nullable|string|max:255',
            'installation_date'         => 'nullable|date',
            'last_maintenance_date'     => 'nullable|date',
            'next_maintenance_date'     => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'capacity_tons'             => 'nullable|numeric|min:0',
            'spec_voltage'              => 'nullable|numeric|min:0',
            'fuel'                      => 'nullable|string|max:255',
            'autostart'                 => 'boolean',
            'capacity_persons'          => 'nullable|integer|min:0',
            'speed_m_s'                 => 'nullable|numeric|min:0',
            'flow_lpm'                  => 'nullable|integer|min:0',
            'head_m'                    => 'nullable|numeric|min:0',
            'cells'                     => 'nullable|integer|min:0',
            'makeup_valve'              => 'boolean',
            'is_critical'               => 'boolean',
        ]);

        // Checkboxes default to false when unchecked
        $validated['is_critical']  = $request->boolean('is_critical');
        $validated['autostart']    = $request->boolean('autostart');
        $validated['makeup_valve'] = $request->boolean('makeup_valve');

        return $validated;
    }
}
