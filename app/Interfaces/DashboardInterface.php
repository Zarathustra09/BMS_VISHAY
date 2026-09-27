<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface DashboardInterface
{
    /**
     * Everything the Home dashboard's stat tiles need: each module's own getStats(), plus
     * equipment_availability (active/total machines, %) since that's a named metric on the
     * page rather than a raw count.
     */
    public function getSummary(): array;

    /**
     * Open alerts raised per day for the last $days days (including days with zero), oldest
     * first — feeds the dashboard's alerts-trend bar chart.
     *
     * @return array<int, array{label: string, count: int}>
     */
    public function getAlertsTrend(int $days = 7): array;

    /**
     * Every building with its total and active machine counts, for the "Building Health"
     * card. Small, fixed-size (one row per building).
     */
    public function getBuildingHealth(): Collection;

    public function getRecentAlerts(int $limit = 5): Collection;

    public function getRecentWorkOrders(int $limit = 5): Collection;
}
