<?php

namespace App\Interfaces;

use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;

interface MachineLogInterface
{
    public function store(array $data): MachineLog;

    /**
     * Query for the machine-logs DataTable on machines/show — scoped to one machine,
     * optionally filtered by severity, log_type, and a logged_at date range.
     */
    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder;

    /**
     * Bucketed time series for one metric on one machine, plus everything the sensor chart
     * needs to draw itself: the metrics that actually have data, that metric's threshold
     * bounds, and the machine's current online state.
     *
     * @param  string|null  $metric  null, unknown, or no-data for this machine falls back to
     *                                the metric with the most readings
     * @param  string  $range  '1h' | '24h' | '7d'
     */
    public function getReadingSeries(Machine $machine, ?string $metric = null, string $range = '24h'): array;
}
