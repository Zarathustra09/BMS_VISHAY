<?php

namespace App\Interfaces;

use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface AlertInterface
{
    /**
     * Minutes without a reading before a machine is considered offline
     * (CheckOfflineDevices command default, overridable via --minutes).
     */
    public const OFFLINE_AFTER_MINUTES = 10;

    /**
     * The machine_logs columns that can have a threshold configured against them.
     */
    public const METRICS = [
        'temperature',
        'humidity',
        'pressure',
        'power_consumption',
        'voltage',
        'current',
        'frequency',
        'vibration_rms',
        'bearing_temp',
        'oil_level',
    ];

    /**
     * Display label (with unit) per metric — single source for the thresholds table on the
     * machine edit page and the sensor chart's metric dropdown.
     */
    public const METRIC_LABELS = [
        'temperature' => 'Temperature (°C)',
        'humidity' => 'Humidity (%)',
        'pressure' => 'Pressure',
        'power_consumption' => 'Power Consumption (kW)',
        'voltage' => 'Voltage (V)',
        'current' => 'Current (A)',
        'frequency' => 'Frequency (Hz)',
        'vibration_rms' => 'Vibration RMS',
        'bearing_temp' => 'Bearing Temp (°C)',
        'oil_level' => 'Oil Level',
    ];

    /**
     * Check a newly stored sensor reading against the machine's configured thresholds,
     * raising, escalating, or auto-resolving alerts as needed. Also clears any open/
     * acknowledged offline alert for this machine, since a reading is proof of life.
     * Called from MachineLogService::store() so it runs for every reading regardless of caller.
     */
    public function evaluate(MachineLog $log): void;

    /**
     * Raise an 'offline' alert for every machine whose last reading is older than $minutes.
     * Machines with last_seen_at = null are skipped (never commissioned isn't an outage), as
     * are machines with status 'inactive' (deliberately out of service). Idempotent: a machine
     * that already has an open or acknowledged offline alert is left alone.
     *
     * @return array{checked:int, raised:int, machines:array<int,string>}
     */
    public function raiseOfflineAlerts(int $minutes = self::OFFLINE_AFTER_MINUTES): array;

    /**
     * Clear this machine's offline alert(s) because a reading just arrived. Resolves both
     * 'open' and 'acknowledged' ones — the device coming back is objective, and leaving an
     * acknowledged one open would permanently block raiseOfflineAlerts()'s dedupe.
     *
     * @return int how many were resolved (0 or 1 in practice)
     */
    public function resolveOfflineAlerts(Machine $machine): int;

    /**
     * This machine's configured thresholds, keyed by metric.
     */
    public function getThresholds(Machine $machine): Collection;

    /**
     * This machine's open/acknowledged alerts, most recent first — for the "Active Alerts"
     * card on machines/show. Small, fixed-size per machine, so a plain collection (not a
     * DataTable) is fine here.
     */
    public function getActiveForMachine(Machine $machine, int $limit = 10): Collection;

    /**
     * Replace this machine's thresholds from the edit-page form. A metric with no
     * non-empty bound in $thresholds has its threshold row removed (no longer monitored).
     */
    public function saveThresholds(Machine $machine, array $thresholds): void;

    /**
     * Query for the alerts DataTable — every alert, optionally filtered by status/severity/machine.
     */
    public function getQueryForDataTable(array $filters): Builder;

    public function getStats(): array;

    public function acknowledge(Alert $alert, User $user): Alert;

    public function resolve(Alert $alert): Alert;
}
