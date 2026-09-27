<?php

namespace App\Services;

use App\Interfaces\MaintenanceInterface;
use App\Interfaces\ReportInterface;
use App\Models\Alert;
use App\Models\MachineLog;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService implements ReportInterface
{
    private const PREVIEW_LIMIT = 100;

    public function __construct(protected MaintenanceInterface $maintenance) {}

    public function getSensorSummary(array $filters): Collection
    {
        $query = MachineLog::query()
            ->join('machines', 'machines.id', '=', 'machine_logs.machine_id')
            ->select('machines.id as machine_id', 'machines.name as machine_name', 'machines.machine_code')
            ->selectRaw('COUNT(*) as total_readings')
            ->selectRaw("SUM(CASE WHEN machine_logs.severity = 'critical' THEN 1 ELSE 0 END) as critical_count")
            ->selectRaw("SUM(CASE WHEN machine_logs.severity = 'error' THEN 1 ELSE 0 END) as error_count")
            ->selectRaw("SUM(CASE WHEN machine_logs.severity = 'warning' THEN 1 ELSE 0 END) as warning_count")
            ->groupBy('machines.id', 'machines.name', 'machines.machine_code');

        $this->applyDateRange($query, 'machine_logs.logged_at', $filters);

        if (!empty($filters['building_id'])) {
            $query->where('machines.building_id', $filters['building_id']);
        }

        return $query->orderByDesc('total_readings')->get();
    }

    public function getAlertHistory(array $filters): array
    {
        $base = $this->getAlertHistoryQuery($filters);

        return [
            'stats' => [
                'total' => (clone $base)->count(),
                'critical' => (clone $base)->where('severity', 'critical')->count(),
                'open' => (clone $base)->where('status', 'open')->count(),
                'resolved' => (clone $base)->where('status', 'resolved')->count(),
            ],
            'preview' => (clone $base)->limit(self::PREVIEW_LIMIT)->get(),
        ];
    }

    public function getAlertHistoryQuery(array $filters): Builder
    {
        $query = Alert::with('machine')->orderByDesc('created_at');

        $this->applyDateRange($query, 'alerts.created_at', $filters);

        if (!empty($filters['building_id'])) {
            $query->whereHas('machine', fn ($q) => $q->where('building_id', $filters['building_id']));
        }

        return $query;
    }

    public function getWorkOrderSummary(array $filters): array
    {
        $base = $this->getWorkOrderSummaryQuery($filters);

        return [
            'stats' => [
                'total' => (clone $base)->count(),
                'completed' => (clone $base)->where('status', 'completed')->count(),
                'pending' => (clone $base)->where('status', 'pending')->count(),
                'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
                'preventive' => (clone $base)->where('type', 'preventive')->count(),
            ],
            'preview' => (clone $base)->limit(self::PREVIEW_LIMIT)->get(),
        ];
    }

    public function getWorkOrderSummaryQuery(array $filters): Builder
    {
        $query = WorkOrder::with(['machine', 'assignedTo'])->orderByDesc('created_at');

        $this->applyDateRange($query, 'work_orders.created_at', $filters);

        if (!empty($filters['building_id'])) {
            $query->whereHas('machine', fn ($q) => $q->where('building_id', $filters['building_id']));
        }

        return $query;
    }

    public function getMaintenanceCompliance(array $filters): array
    {
        return [
            'stats' => $this->maintenance->getStats(),
            'breakdown' => $this->maintenance->getBuildingBreakdown(),
            'preview' => $this->getMaintenanceComplianceQuery($filters)->limit(self::PREVIEW_LIMIT)->get(),
        ];
    }

    public function getMaintenanceComplianceQuery(array $filters): Builder
    {
        // Overdue/due-soon are inherently point-in-time, so date_from/date_to don't apply
        // here — only the building filter does.
        return $this->maintenance->getQueryForDataTable(['building_id' => $filters['building_id'] ?? null]);
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate($column, '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate($column, '<=', $filters['date_to']);
        }
    }
}
