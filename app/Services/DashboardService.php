<?php

namespace App\Services;

use App\Enums\AppModule;
use App\Interfaces\AlertInterface;
use App\Interfaces\BuildingInterface;
use App\Interfaces\DashboardInterface;
use App\Interfaces\MachineInterface;
use App\Interfaces\WorkOrderInterface;
use App\Models\Alert;
use App\Models\Building;
use Illuminate\Support\Collection;

class DashboardService implements DashboardInterface
{
    public function __construct(
        protected MachineInterface $machines,
        protected WorkOrderInterface $workOrders,
        protected AlertInterface $alerts,
        protected BuildingInterface $buildings,
    ) {}

    public function getSummary(): array
    {
        $machines = $this->machines->getStats();

        $summary = [
            'machines' => $machines,
            'equipment_availability' => $machines['total'] > 0
                ? round($machines['active'] / $machines['total'] * 100, 1)
                : 0.0,
            'alerts' => $this->alerts->getStats(),
        ];

        // A switched-off module's stats are left out entirely rather than computed and hidden —
        // home.blade.php wraps every use of these two keys in the matching @module block.
        if (AppModule::WorkOrders->isEnabled()) {
            $summary['work_orders'] = $this->workOrders->getStats();
        }

        if (AppModule::Buildings->isEnabled()) {
            $summary['buildings'] = $this->buildings->getStats();
        }

        return $summary;
    }

    public function getAlertsTrend(int $days = 7): array
    {
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);

            $trend[] = [
                'label' => $date->format('D'),
                'count' => Alert::whereDate('created_at', $date)->count(),
            ];
        }

        return $trend;
    }

    public function getBuildingHealth(): Collection
    {
        if (! AppModule::Buildings->isEnabled()) {
            return collect();
        }

        return Building::query()
            ->withCount([
                'machines as machines_count',
                'machines as active_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getRecentAlerts(int $limit = 5): Collection
    {
        return $this->alerts->getQueryForDataTable([])->limit($limit)->get();
    }

    public function getRecentWorkOrders(int $limit = 5): Collection
    {
        if (! AppModule::WorkOrders->isEnabled()) {
            return collect();
        }

        return $this->workOrders->getQueryForDataTable([])->limit($limit)->get();
    }
}
