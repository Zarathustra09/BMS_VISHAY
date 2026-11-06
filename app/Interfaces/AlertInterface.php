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
     * How long readings must stay within bounds before an open threshold alert auto-resolves,
     * counted from the last reading that breached it. A device sending many readings a second
     * that sits on a limit would otherwise raise, clear and re-raise (e-mailing everyone) every
     * reading; a device sending one reading every few seconds is barely affected.
     */
    public const RESOLVE_AFTER_NORMAL_SECONDS = 5;

    /**
     * Check a newly stored sensor reading against the alert bounds on the machine's number parameters,
     * raising, escalating, or auto-resolving alerts as needed. Also clears any open/
     * acknowledged offline alert for this machine, since a reading is proof of life.
     * Called from MachineLogService::store() so it runs for every reading regardless of caller.
     *
     * @param  array<string, mixed>|null  $readings  the values to judge, when they are not the
     *                                               log's own: a fast device's reading is merged
     *                                               into that second's log, but each reading is
     *                                               judged as sent, so a one-sample spike is not
     *                                               averaged away
     * @param  bool  $clearOffline  false when this device was already proven alive within the
     *                              last second, which skips one query per reading
     * @return string|null the worst severity this reading breaches ('critical' or 'warning'), or null
     *                     when it is within bounds (or the machine has no thresholds) - used to stamp
     *                     the log's own severity
     */
    public function evaluate(MachineLog $log, ?array $readings = null, bool $clearOffline = true): ?string;

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
     * This machine's open/acknowledged alerts, most recent first — for the "Active Alerts"
     * card on machines/show. Small, fixed-size per machine, so a plain collection (not a
     * DataTable) is fine here.
     */
    public function getActiveForMachine(Machine $machine, int $limit = 10): Collection;

    /**
     * Query for the alerts DataTable — every alert, optionally filtered by status/severity/machine.
     */
    public function getQueryForDataTable(array $filters): Builder;

    public function getStats(): array;

    public function acknowledge(Alert $alert, User $user): Alert;

    /**
     * Resolve an alert and record who did it. $by is null when the system did (a reading back in
     * range, the device coming back online). $resolution is one of manual, auto, work_order,
     * device_back_online - it decides the trail entry. $value is the reading that cleared it, if any.
     */
    public function resolve(Alert $alert, ?User $by = null, string $resolution = 'manual', mixed $value = null): Alert;

    /**
     * The latest entries of every alert trail on this machine (who raised/acknowledged/resolved
     * what, and when), newest first - the machine page's "Alert trail".
     */
    public function getTrailForMachine(Machine $machine, int $limit = 20): Collection;
}
