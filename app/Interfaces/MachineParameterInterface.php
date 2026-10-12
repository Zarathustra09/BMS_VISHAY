<?php

namespace App\Interfaces;

use App\Models\Machine;
use Illuminate\Support\Collection;

interface MachineParameterInterface
{
    /**
     * Replace this machine's parameter list with the rows from the machine form. A row with an
     * `id` updates that parameter — its `key` and `data_type` are never changed, whatever is
     * posted, since stored readings and devices refer to them. A row without an `id` creates
     * one (its `key` is generated from the label when left blank). Existing parameters absent
     * from $rows are deleted. Each row's position becomes its sort order.
     *
     * @param  array<int, array<string, mixed>>  $rows
     *
     * @throws \App\Exceptions\MachineParameterException if a row id isn't this machine's, or a
     *                                                   new key clashes with a kept parameter.
     */
    public function sync(Machine $machine, array $rows): void;

    /**
     * Another machine's parameters as id-less builder rows (key, label, unit, type, bounds,
     * thresholds) — for the "Copy parameters from…" picker on the machine form.
     *
     * @return list<array<string, mixed>>
     */
    public function copyableRows(Machine $source): array;

    /**
     * The `readings` validation rules (and human-readable attribute names for error messages)
     * for a log submitted by $machine's device. A parameter marked required is only enforced
     * for a `sensor_reading` log — an alarm or status-change log isn't rejected for lacking
     * telemetry. Unknown keys are rejected, so a typo can't silently store nothing.
     *
     * @return array{0: array<string, array<int, mixed>>, 1: array<string, string>}
     */
    public function readingRules(Machine $machine, string $logType): array;

    /**
     * Casts each submitted reading to its parameter's type and drops anything that isn't one
     * of the machine's parameters, plus null values (a stored JSON null would read back as the
     * string "null" under MySQL's JSON extraction).
     *
     * @param  array<string, mixed>  $readings
     * @return array<string, mixed>
     */
    public function normalizeReadings(Machine $machine, array $readings): array;

    /**
     * Everything the machine page's API Integration card shows, built from this machine's own
     * parameters: the endpoint, an example JSON body, a copy-ready curl command (with a
     * placeholder, never the real device key) and example 201/401/422 responses.
     *
     * @return array{endpoint: string, body_json: string, curl: string, responses: list<array{status: int, title: string, json: string}>, parameters: Collection}
     */
    public function apiExample(Machine $machine): array;

    /**
     * One row per parameter for the machine page's "Sensor parameters" table: what it is, its
     * accepted range, its alert limits in plain words (and any contradiction in them), the latest
     * value the machine reported for it and which alert state that value is in.
     *
     * `state` is 'critical' | 'warning' | 'normal' when there is a number to judge against limits,
     * 'no_limits' when the parameter has no alert limits (or is not a number), 'no_data' when it has
     * limits but nothing has been reported yet.
     *
     * @return list<array{key: string, label: string, unit: ?string, type: string, required: bool, range: string, limits: array<string, string>, problems: array<int, string>, value: mixed, display: string, ago: ?string, state: string}>
     */
    public function overview(Machine $machine): array;
}
