<?php

namespace App\Interfaces;

use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

interface MachineLogInterface
{
    /**
     * The fixed windows the sensor chart can show, shortest first. `auto` and `custom` are the
     * two other values getReadingSeries() accepts for $range.
     */
    public const RANGE_PRESETS = ['5m', '15m', '1h', '6h', '24h', '7d', '30d'];

    public function store(array $data): MachineLog;

    /**
     * Query for the machine-logs DataTable on machines/show — scoped to one machine,
     * optionally filtered by severity, log_type, and a logged_at date range.
     */
    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder;

    /**
     * Time series for one of the machine's number parameters, plus everything the sensor chart
     * needs to draw itself: the machine's chartable parameters, that parameter's threshold
     * bounds, how the points were built, how often to re-poll, and the machine's online state.
     *
     * With few readings in the window every reading is its own point ("Raw readings"); with
     * many they are averaged into buckets sized to the span the data covers.
     *
     * @param  string|null  $metric  a parameter key; null or unknown falls back to the machine's
     *                               first number parameter
     * @param  string  $range  one of RANGE_PRESETS, `auto` (the smallest preset that contains the
     *                         parameter's latest reading) or `custom` ($from/$to, both required)
     */
    public function getReadingSeries(Machine $machine, ?string $metric = null, string $range = 'auto', ?Carbon $from = null, ?Carbon $to = null): array;
}
