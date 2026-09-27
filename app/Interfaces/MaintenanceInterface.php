<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface MaintenanceInterface
{
    /**
     * Fleet-wide maintenance counts: machines already overdue, due within 7 days, due
     * within 30 days (all three based on next_maintenance_date vs today), and preventive
     * work orders completed this calendar month.
     */
    public function getStats(): array;

    /**
     * Overdue/due-soon machine counts grouped by building, for the dashboard's per-building
     * breakdown. Small, fixed-size (one row per building), so a plain collection is fine.
     */
    public function getBuildingBreakdown(): Collection;

    /**
     * Query for the "Machines Due for Maintenance" DataTable — every machine with a
     * next_maintenance_date set, optionally filtered by status (overdue/due_soon/due_month)
     * and building.
     */
    public function getQueryForDataTable(array $filters): Builder;
}
