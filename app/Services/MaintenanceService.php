<?php

namespace App\Services;

use App\Interfaces\MaintenanceInterface;
use App\Models\Building;
use App\Models\Machine;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MaintenanceService implements MaintenanceInterface
{
    public function getStats(): array
    {
        $today = today();

        return [
            'overdue' => $this->dueMachines()
                ->where('next_maintenance_date', '<', $today)
                ->count(),
            'due_soon' => $this->dueMachines()
                ->whereBetween('next_maintenance_date', [$today, $today->copy()->addDays(7)])
                ->count(),
            'due_month' => $this->dueMachines()
                ->whereBetween('next_maintenance_date', [$today, $today->copy()->addDays(30)])
                ->count(),
            'completed_this_month' => WorkOrder::where('type', 'preventive')
                ->where('status', 'completed')
                ->whereMonth('completed_at', $today->month)
                ->whereYear('completed_at', $today->year)
                ->count(),
        ];
    }

    public function getBuildingBreakdown(): Collection
    {
        $today = today();

        // An inactive machine is deliberately out of service, so it's excluded from every
        // maintenance count here — the same reasoning already applied to offline-device
        // detection and preventive work order generation (see docs/03-data-model.md).
        return Building::query()
            ->withCount([
                'machines as overdue_count' => fn ($q) => $q->whereNotNull('next_maintenance_date')
                    ->where('status', '!=', 'inactive')
                    ->where('next_maintenance_date', '<', $today),
                'machines as due_soon_count' => fn ($q) => $q->whereNotNull('next_maintenance_date')
                    ->where('status', '!=', 'inactive')
                    ->whereBetween('next_maintenance_date', [$today, $today->copy()->addDays(7)]),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getQueryForDataTable(array $filters): Builder
    {
        $today = today();
        $query = $this->dueMachines()->with('building');

        match ($filters['status'] ?? null) {
            'overdue' => $query->where('next_maintenance_date', '<', $today),
            'due_soon' => $query->whereBetween('next_maintenance_date', [$today, $today->copy()->addDays(7)]),
            'due_month' => $query->whereBetween('next_maintenance_date', [$today, $today->copy()->addDays(30)]),
            default => null,
        };

        if (!empty($filters['building_id']) && $filters['building_id'] !== 'all') {
            $query->where('building_id', $filters['building_id']);
        }

        return $query->orderBy('next_maintenance_date');
    }

    /**
     * Base query shared by every maintenance count: has a schedule, and isn't deliberately
     * out of service.
     */
    private function dueMachines(): Builder
    {
        return Machine::whereNotNull('next_maintenance_date')->where('status', '!=', 'inactive');
    }
}
