<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\MachineParameterInterface;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MachineLogController extends Controller
{
    /**
     * The fixed sensor fields the API used to accept at the top level of the body. They are now
     * parameters sent inside `readings`; a device still posting one the old way is told so,
     * instead of getting a 201 for a log whose values were silently thrown away.
     */
    private const PRE_PARAMETER_FIELDS = [
        'temperature', 'humidity', 'pressure', 'power_consumption', 'voltage', 'current',
        'frequency', 'runtime_hours', 'vibration_rms', 'bearing_temp', 'oil_level',
    ];

    public function __construct(
        protected MachineLogInterface $machineLogService,
        protected MachineParameterInterface $parameterService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        // Set by the device.auth middleware from the X-Device-Key header —
        // the device can only ever write logs for its own machine.
        /** @var Machine $machine */
        $machine = $request->attributes->get('device_machine');

        $misplaced = array_values(array_intersect(self::PRE_PARAMETER_FIELDS, array_keys($request->all())));

        if ($misplaced !== []) {
            throw ValidationException::withMessages(['readings' => [
                'Sensor values must be sent inside a "readings" object, not at the top level: '.implode(', ', $misplaced).'.',
            ]]);
        }

        // Sensor values are validated against this machine's own parameters (set up on its
        // edit page) — an unknown key, a wrong type, an out-of-range value or a missing
        // required parameter is a 422, never silently dropped.
        [$readingRules, $readingAttributes] = $this->parameterService->readingRules(
            $machine,
            (string) $request->input('log_type')
        );

        $validated = $request->validate([
            // enum('status_change','maintenance','alarm','sensor_reading','operation','error')
            'log_type' => 'required|string|in:status_change,maintenance,alarm,sensor_reading,operation,error',

            // string — nullable
            'event' => 'nullable|string|max:255',

            // enum('info','warning','error','critical') default 'info'
            'severity' => 'nullable|string|in:info,warning,error,critical',

            // string — nullable
            'previous_status' => 'nullable|string|max:255',
            'current_status' => 'nullable|string|max:255',

            // text — nullable
            'notes' => 'nullable|string|max:65535',

            // string — nullable
            'performed_by' => 'nullable|string|max:255',

            // timestamp — nullable, defaults to current via DB
            'logged_at' => 'nullable|date',

            ...$readingRules,
        ], [], $readingAttributes);

        $validated['machine_id'] = $machine->id;

        $readings = $this->parameterService->normalizeReadings($machine, $validated['readings'] ?? []);

        if ($readings === []) {
            unset($validated['readings']);
        } else {
            $validated['readings'] = $readings;
        }

        // last_seen_at is stamped inside MachineLogService::store(), not here — so every
        // caller of store() keeps offline detection in sync, not just this endpoint.
        $log = $this->machineLogService->store($validated);

        return response()->json([
            'success' => true,
            'message' => 'Machine log created successfully.',
            'data' => $log,
        ], 201);
    }
}
