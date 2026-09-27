<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ReportInterface
{
    /**
     * Per-machine reading counts (total + per-severity) within $filters['date_from']..
     * ['date_to'], optionally scoped to $filters['building_id']. Already small and
     * CSV-ready (one row per machine, not per reading).
     */
    public function getSensorSummary(array $filters): Collection;

    /**
     * ['stats' => severity/status/type breakdown counts, 'preview' => latest 100 matching
     * alerts] for on-page display. Pair with getAlertHistoryQuery() for the full CSV export.
     */
    public function getAlertHistory(array $filters): array;

    public function getAlertHistoryQuery(array $filters): Builder;

    /**
     * ['stats' => status/type/priority breakdown counts, 'preview' => latest 100 matching
     * work orders]. Pair with getWorkOrderSummaryQuery() for the full CSV export.
     */
    public function getWorkOrderSummary(array $filters): array;

    public function getWorkOrderSummaryQuery(array $filters): Builder;

    /**
     * ['stats' => MaintenanceService::getStats(), 'breakdown' => per-building overdue/
     * due-soon counts, 'preview' => the 100 soonest-due machines matching $filters]. Reuses
     * MaintenanceInterface rather than recomputing its rules.
     */
    public function getMaintenanceCompliance(array $filters): array;

    public function getMaintenanceComplianceQuery(array $filters): Builder;
}
