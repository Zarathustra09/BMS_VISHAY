<?php

namespace App\Http\Controllers;

use App\DataTables\MachineLogsDataTable;
use App\DataTables\MachinesDataTable;
use App\Enums\AppModule;
use App\Enums\MachineStatus;
use App\Enums\ParameterDataType;
use App\Enums\SpecFieldType;
use App\Exceptions\MachineParameterException;
use App\Interfaces\AlertInterface;
use App\Interfaces\MachineInterface;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\MachineParameterInterface;
use App\Interfaces\MachineTypeInterface;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function __construct(
        protected MachineInterface $machineService,
        protected AlertInterface $alertService,
        protected MachineLogInterface $machineLogService,
        protected MachineTypeInterface $machineTypeService,
        protected MachineParameterInterface $parameterService,
    ) {
        $this->middleware('permission:machines.view')->only(['index', 'show', 'data', 'logsData', 'readings', 'parameters']);
        $this->middleware('permission:machines.create')->only(['create', 'store']);
        $this->middleware('permission:machines.update')->only(['edit', 'update']);
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
        $copySources = $this->machineService->getParameterCopySources();

        return view('machines.create', compact('buildings', 'machineTypes', 'copySources'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMachine($request);

        try {
            $machine = $this->machineService->create($validated);
        } catch (MachineParameterException $e) {
            return back()->withInput()->withErrors(['parameters' => $e->getMessage()]);
        }

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
        $apiExample = $this->parameterService->apiExample($machine);

        $canViewDeviceKey = auth()->user()->can('machines.view-device-key');
        $deviceKeyPlain = $canViewDeviceKey ? $machine->decryptDeviceKey() : null;

        return view('machines.show', compact(
            'machine', 'logStats', 'recentAlerts', 'logTypeDistribution', 'activeAlerts', 'activityFeed', 'chart',
            'specRows', 'canViewDeviceKey', 'deviceKeyPlain', 'apiExample'
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
     * JSON source for the sensor chart on machines/show — re-polled by Alpine at the
     * `poll_seconds` the response asks for. `range` is a preset, `auto` (the default) or
     * `custom` with an explicit `from`/`to` of at most 90 days.
     */
    public function readings(Request $request, Machine $machine)
    {
        $isCustom = $request->input('range') === 'custom';

        $validated = $request->validate([
            // Any string is accepted - an unknown key falls back to the machine's first number
            // parameter (MachineLogService::getReadingSeries()), exactly as a missing one does.
            'metric' => ['nullable', 'string', 'max:50'],
            'range' => ['nullable', Rule::in(['auto', 'custom', ...MachineLogInterface::RANGE_PRESETS])],
            'from' => [Rule::requiredIf($isCustom), 'nullable', 'date'],
            'to' => [Rule::requiredIf($isCustom), 'nullable', 'date', 'after:from', function ($attribute, $value, $fail) use ($request, $isCustom) {
                if ($isCustom && $request->filled('from') && strtotime((string) $value) - strtotime((string) $request->input('from')) > 90 * 86400) {
                    $fail('A custom range can span at most 90 days.');
                }
            }],
        ]);

        return response()->json($this->machineLogService->getReadingSeries(
            $machine,
            $validated['metric'] ?? null,
            $validated['range'] ?? 'auto',
            $isCustom ? Carbon::parse($validated['from']) : null,
            $isCustom ? Carbon::parse($validated['to']) : null,
        ));
    }

    /**
     * JSON source for the machine form's "Copy parameters from..." picker - another machine's
     * parameters as id-less builder rows.
     */
    public function parameters(Machine $machine)
    {
        return response()->json($this->parameterService->copyableRows($machine));
    }

    public function edit(Machine $machine)
    {
        $buildings = Building::select('id', 'name')->orderBy('name')->get();
        $floors = $machine->building_id
            ? Floor::where('building_id', $machine->building_id)->get()
            : collect();
        $machineTypes = $this->machineTypeService->getForSelect();
        $machine->load('parameters');

        return view('machines.edit', compact('machine', 'buildings', 'floors', 'machineTypes'));
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $this->validateMachine($request, $machine);

        try {
            $this->machineService->update($machine, $validated);
        } catch (MachineParameterException $e) {
            return back()->withInput()->withErrors(['parameters' => $e->getMessage()]);
        }

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
            ...$this->parameterRules($request),
        ], [], $specAttributes + $this->parameterAttributes());

        // Checkboxes submit nothing at all when unchecked, so this normalises them to
        // booleans after validation, matching the app's existing convention for this.
        $validated['is_critical'] = $request->boolean('is_critical');

        // The form is the source of truth for a machine's parameters: no rows posted means the
        // machine has none, so the list is always handed to the service (an empty one deletes).
        // Sorted by posted index first: validated() rebuilds the array in rule order, which puts
        // a row with an `id` ahead of one without and would scramble the user's ordering.
        $validated['parameters'] = collect($validated['parameters'] ?? [])->sortKeys()->values()->all();

        // With Buildings switched off (config/modules.php) the form has no Building/Floor
        // selects, so a posted value can only be hand-crafted — dropping the keys (rather than
        // nulling them) also leaves a machine's existing location untouched on edit.
        if (! AppModule::Buildings->isEnabled()) {
            unset($validated['building_id'], $validated['floor_id']);
        }

        return $validated;
    }

    /**
     * Rules for the machine form's Sensor Parameters builder. `key` is only honoured on a new
     * row (the service never changes an existing parameter's key or data type); threshold bounds
     * are numeric and ignored by the service for a non-number parameter.
     *
     * @return array<string, array<int, mixed>>
     */
    private function parameterRules(Request $request): array
    {
        $number = ['nullable', 'numeric', 'between:-9999999999999,9999999999999'];

        return [
            'parameters' => ['nullable', 'array', 'max:50'],
            'parameters.*' => ['array'],
            'parameters.*.id' => ['nullable', 'integer'],
            'parameters.*.key' => ['nullable', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'],
            'parameters.*.label' => ['required', 'string', 'max:255'],
            'parameters.*.unit' => ['nullable', 'string', 'max:30'],
            'parameters.*.data_type' => ['required', Rule::enum(ParameterDataType::class)],
            'parameters.*.required' => ['nullable', 'boolean'],
            'parameters.*.min' => [...$number, function ($attribute, $value, $fail) use ($request) {
                $index = explode('.', $attribute)[1];
                $max = $request->input("parameters.{$index}.max");

                if (is_numeric($value) && is_numeric($max) && (float) $value > (float) $max) {
                    $fail('The minimum accepted value must not be greater than the maximum.');
                }
            }],
            'parameters.*.max' => $number,
            'parameters.*.warning_min' => $number,
            'parameters.*.warning_max' => $number,
            'parameters.*.critical_min' => $number,
            'parameters.*.critical_max' => $number,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parameterAttributes(): array
    {
        return [
            'parameters.*.key' => 'parameter key',
            'parameters.*.label' => 'parameter label',
            'parameters.*.unit' => 'parameter unit',
            'parameters.*.data_type' => 'parameter data type',
            'parameters.*.min' => 'parameter minimum',
            'parameters.*.max' => 'parameter maximum',
            'parameters.*.warning_min' => 'parameter warning minimum',
            'parameters.*.warning_max' => 'parameter warning maximum',
            'parameters.*.critical_min' => 'parameter critical minimum',
            'parameters.*.critical_max' => 'parameter critical maximum',
        ];
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
