<?php

namespace App\Services;

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

        return [
            'machines' => $machines,
            'equipment_availability' => $machines['total'] > 0
                ? round($machines['active'] / $machines['total'] * 100, 1)
                : 0.0,
            'work_orders' => $this->workOrders->getStats(),
            'alerts' => $this->alerts->getStats(),
            'buildings' => $this->buildings->getStats(),
        ];
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
        return $this->workOrders->getQueryForDataTable([])->limit($limit)->get();
    }
}
