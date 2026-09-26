<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineLogController extends Controller
{
    public function __construct(protected MachineLogInterface $machineLogService) {}

    public function store(Request $request): JsonResponse
    {
        // Set by the device.auth middleware from the X-Device-Key header —
        // the device can only ever write logs for its own machine.
        /** @var Machine $machine */
        $machine = $request->attributes->get('device_machine');

        $validated = $request->validate([
            // enum('status_change','maintenance','alarm','sensor_reading','operation','error')
            'log_type'          => 'required|string|in:status_change,maintenance,alarm,sensor_reading,operation,error',

            // string — nullable
            'event'             => 'nullable|string|max:255',

            // enum('info','warning','error','critical') default 'info'
            'severity'          => 'nullable|string|in:info,warning,error,critical',

            // string — nullable
            'previous_status'   => 'nullable|string|max:255',
            'current_status'    => 'nullable|string|max:255',

            // decimal(8,2) — can be negative (e.g. sub-zero temps)
            'temperature'       => 'nullable|numeric|between:-999999.99,999999.99',

            // decimal(5,2) — 0.00 to 100.00
            'humidity'          => 'nullable|numeric|between:0,100',

            // decimal(10,2) — can be negative
            'pressure'          => 'nullable|numeric|between:-99999999.99,99999999.99',

            // decimal(10,2) — kW, non-negative
            'power_consumption' => 'nullable|numeric|min:0|max:99999999.99',

            // decimal(8,2) — non-negative
            'voltage'           => 'nullable|numeric|min:0|max:999999.99',

            // decimal(8,2) — non-negative
            'current'           => 'nullable|numeric|min:0|max:999999.99',

            // decimal(8,2) — non-negative
            'frequency'         => 'nullable|numeric|min:0|max:999999.99',

            // integer — non-negative
            'runtime_hours'     => 'nullable|integer|min:0',

            // decimal(8,3) — non-negative
            'vibration_rms'     => 'nullable|numeric|min:0|max:99999.999',

            // decimal(8,2) — can be negative
            'bearing_temp'      => 'nullable|numeric|between:-999999.99,999999.99',

            // decimal(6,2) — 0.00 to 9999.99 (percentage or litres)
            'oil_level'         => 'nullable|numeric|min:0|max:9999.99',

            // text — nullable
            'notes'             => 'nullable|string|max:65535',

            // string — nullable
            'performed_by'      => 'nullable|string|max:255',

            // timestamp — nullable, defaults to current via DB
            'logged_at'         => 'nullable|date',
        ]);

        $validated['machine_id'] = $machine->id;

        $machine->forceFill(['last_seen_at' => now()])->save();

        $log = $this->machineLogService->store($validated);

        return response()->json([
            'success' => true,
            'message' => 'Machine log created successfully.',
            'data'    => $log,
        ], 201);
    }
}
