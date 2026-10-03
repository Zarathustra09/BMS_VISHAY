<?php

namespace App\Http\Controllers;

use App\DataTables\MachineLogsDataTable;
use App\DataTables\MachinesDataTable;
use App\Enums\MachineStatus;
use App\Enums\SpecFieldType;
use App\Interfaces\AlertInterface;
use App\Interfaces\MachineInterface;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\MachineTypeInterface;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function __construct(
        protected MachineInterface $machineService,
        protected AlertInterface $alertService,
        protected MachineLogInterface $machineLogService,
        protected MachineTypeInterface $machineTypeService,
    ) {
        $this->middleware('permission:machines.view')->only(['index', 'show', 'data', 'logsData', 'readings']);
        $this->middleware('permission:machines.create')->only(['create', 'store']);
        $this->middleware('permission:machines.update')->only(['edit', 'update', 'updateThresholds']);
        $this->middleware('permission:machines.delete')->only(['destroy', 'confirmDelete']);
    }

    public function index()
    {
        $stats = $this->machineService->getStats();
        $machineTypes = $this->machineTypeService->getForSelect();
        $buildings = Building::select('id', 'name')->orderBy('name')->get();

        return view('machines.index', compact('stats', 'machineTypes', 'buildings'));
    }

    public function data(MachinesDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        $buildings = Building::select('id', 'name')->orderBy('name')->get();
        $machineTypes = $this->machineTypeService->getForSelect();

        return view('machines.create', compact('buildings', 'machineTypes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMachine($request);

        $machine = $this->machineService->create($validated);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine created successfully.');
    }

    public function show(Machine $machine)
    {
        $machine->load(['building', 'floor', 'machineType']);

        $logStats = $this->machineService->getLogStats($machine);
        $recentAlerts = $this->machineService->getRecentAlerts($machine);
        $logTypeDistribution = $this->machineService->getLogTypeDistribution($machine);
        $activeAlerts = $this->alertService->getActiveForMachine($machine);
        $activityFeed = $this->machineService->getActivityFeed($machine);
        $specRows = $this->machineService->specificationRows($machine);
        // Rendered once here so the page loads with the chart fully drawn — no fetch on
        // first paint, no flash while Alpine boots.
        $chart = $this->machineLogService->getReadingSeries($machine);

        $canViewDeviceKey = auth()->user()->can('machines.view-device-key');
        $deviceKeyPlain = $canViewDeviceKey ? $machine->decryptDeviceKey() : null;

        return view('machines.show', compact(
            'machine', 'logStats', 'recentAlerts', 'logTypeDistribution', 'activeAlerts', 'activityFeed', 'chart',
            'specRows', 'canViewDeviceKey', 'deviceKeyPlain'
        ));
    }

    /**
     * Ajax source for the machine-logs DataTable on machines/show.
     */
    public function logsData(Machine $machine, MachineLogsDataTable $dataTable)
    {
        return $dataTable->forMachine($machine)->ajax();
    }

    /**
     * JSON source for the sensor chart on machines/show — polled every 30s by Alpine.
     */
    public function readings(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'metric' => ['nullable', Rule::in(AlertInterface::METRICS)],
            'range' => ['nullable', Rule::in(['1h', '24h', '7d'])],
        ]);

        return response()->json(
            $this->machineLogService->getReadingSeries($machine, $validated['metric'] ?? null, $validated['range'] ?? '24h')
        );
    }

    public function edit(Machine $machine)
    {
        $buildings = Building::select('id', 'name')->orderBy('name')->get();
        $floors = $machine->building_id
            ? Floor::where('building_id', $machine->building_id)->get()
            : collect();
        $thresholds = $this->alertService->getThresholds($machine);
        $machineTypes = $this->machineTypeService->getForSelect();

        return view('machines.edit', compact('machine', 'buildings', 'floors', 'thresholds', 'machineTypes'));
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $this->validateMachine($request, $machine);

        $this->machineService->update($machine, $validated);

        return redirect()->route('machines.show', $machine)
            ->with('success', 'Machine updated successfully.');
    }

    /**
     * Saves the machine's alert thresholds — a separate form/route from the main wizard
     * above so this module's persistence (AlertService) stays independent of the
     * machine-attribute validation in validateMachine().
     */
    public function updateThresholds(Request $request, Machine $machine)
    {
        $bounds = 'nullable|numeric';

        $validated = $request->validate([
            'thresholds' => 'array',
            'thresholds.*.warning_min' => $bounds,
            'thresholds.*.warning_max' => $bounds,
            'thresholds.*.critical_min' => $bounds,
            'thresholds.*.critical_max' => $bounds,
        ]);

        $this->alertService->saveThresholds($machine, $validated['thresholds'] ?? []);

        return redirect()->route('machines.edit', $machine)
            ->with('success', 'Alert thresholds updated.');
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

    private function validateMachine(Request $request, ?Machine $machine = null): array
    {
        [$specRules, $specAttributes] = $this->specificationRules($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_code' => [
                'required', 'string', 'max:100',
                Rule::unique('machines', 'machine_code')->ignore($machine?->id),
            ],
            'machine_type_id' => ['required', 'exists:machine_types,id'],
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'building_id' => 'nullable|exists:buildings,id',
            // `Rule::exists('floors', 'id')->where(...)` can't be made conditional on whether
            // building_id was actually submitted (DatabaseRule::where() with a null value
            // becomes a whereNull constraint, not "no constraint") — a closure says exactly
            // what's meant: the floor must exist, and belong to the chosen building when one
            // was chosen. The existence check itself lives in the service, not here.
            'floor_id' => ['nullable', function ($attribute, $value, $fail) use ($request) {
                if (! $value) {
                    return;
                }

                $buildingId = $request->filled('building_id') ? (int) $request->input('building_id') : null;

                if (! $this->machineService->floorBelongsToBuilding((int) $value, $buildingId)) {
                    $fail('The selected floor is invalid.');
                }
            }],
            'description' => 'nullable|string',
            'status' => ['required', Rule::enum(MachineStatus::class)],
            'power_rating' => 'nullable|numeric|min:0',
            'ip_address' => 'nullable|ip',
            'installation_date' => 'nullable|date',
            // A plain `after_or_equal:installation_date` string rule misbehaves when the
            // other field is absent (Laravel falls back to parsing the field *name* itself
            // as a date), and both dates here are genuinely optional — so the comparison is
            // a closure that only fires when both sides are actually present.
            'last_maintenance_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->filled('installation_date') && $value < $request->input('installation_date')) {
                    $fail('The last maintenance date must be on or after the installation date.');
                }
            }],
            'next_maintenance_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->filled('last_maintenance_date') && $value < $request->input('last_maintenance_date')) {
                    $fail('The next maintenance date must be on or after the last maintenance date.');
                }
            }],
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'spec_voltage' => 'nullable|numeric|min:0',
            'is_critical' => 'boolean',
            'specifications' => ['nullable', 'array'],
            ...$specRules,
        ], [], $specAttributes);

        // Checkboxes submit nothing at all when unchecked, so this normalises them to
        // booleans after validation, matching the app's existing convention for this.
        $validated['is_critical'] = $request->boolean('is_critical');

        return $validated;
    }

    /**
     * Builds `specifications.{key}` rules (and their human-readable attribute names for error
     * messages) from the request's selected machine type's own spec_fields — looked up through
     * the service, never a direct model query, per the "no model queries in controllers" rule.
     *
     * @return array{0: array<string, array<int, string>>, 1: array<string, string>}
     */
    private function specificationRules(Request $request): array
    {
        $typeId = $request->input('machine_type_id');

        if (! $typeId || ! is_numeric($typeId)) {
            return [[], []];
        }

        try {
            $type = $this->machineTypeService->findById((int) $typeId);
        } catch (ModelNotFoundException) {
            return [[], []];
        }

        $rules = [];
        $attributes = [];

        foreach ($type->specFields() as $field) {
            $key = $field['key'];
            $kind = SpecFieldType::tryFrom($field['type'] ?? '') ?? SpecFieldType::Text;
            $required = $kind->supportsRequired() && ($field['required'] ?? false);

            foreach ($kind->valueRules($field, $required) as $suffix => $kindRules) {
                $path = $suffix === '' ? "specifications.{$key}" : "specifications.{$key}.{$suffix}";
                $rules[$path] = $kindRules;
                $attributes[$path] = $field['label'];
            }
        }

        return [$rules, $attributes];
    }
}
