<?php

namespace App\Services;

use App\Interfaces\MachineInterface;
use App\Interfaces\SettingInterface;
use App\Models\Alert;
use App\Models\Floor;
use App\Models\Machine;
use App\Models\SensorThreshold;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class MachineService implements MachineInterface
{
    public function __construct(protected SettingInterface $settings) {}

    public function getQueryForDataTable(array $filters): Builder
    {
        $query = Machine::with(['building', 'floor']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['building_id']) && $filters['building_id'] !== 'all') {
            $query->where('building_id', $filters['building_id']);
        }

        if (! empty($filters['floor_id']) && $filters['floor_id'] !== 'all') {
            $query->where('floor_id', $filters['floor_id']);
        }

        return $query;
    }

    public function getStats(): array
    {
        return [
            'total' => Machine::count(),
            'active' => Machine::where('status', 'active')->count(),
            'maintenance' => Machine::where('status', 'maintenance')->count(),
            'fault' => Machine::where('status', 'fault')->count(),
            'inactive' => Machine::where('status', 'inactive')->count(),
            'critical' => Machine::where('is_critical', true)->count(),
        ];
    }

    public function getDistinctTypes(): Collection
    {
        return Machine::select('type')->distinct()->whereNotNull('type')->pluck('type');
    }

    public function findById(int $id): Machine
    {
        return Machine::with(['building', 'floor'])->findOrFail($id);
    }

    public function getLogStats(Machine $machine): array
    {
        return [
            'total' => $machine->logs()->count(),
            'today' => $machine->logs()->whereDate('logged_at', today())->count(),
            'week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
            'critical' => $machine->logs()->where('severity', 'critical')->count(),
            'errors' => $machine->logs()->where('severity', 'error')->count(),
            'warnings' => $machine->logs()->where('severity', 'warning')->count(),
        ];
    }

    public function getRecentAlerts(Machine $machine, int $limit = 10): Collection
    {
        return $machine->logs()
            ->whereIn('severity', ['critical', 'error'])
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLogTypeDistribution(Machine $machine): Collection
    {
        return $machine->logs()
            ->selectRaw('log_type, COUNT(*) as count')
            ->groupBy('log_type')
            ->pluck('count', 'log_type');
    }

    public function getActivityFeed(Machine $machine, int $limit = 20): Collection
    {
        $workOrderIds = $machine->workOrders()->pluck('id');
        $alertIds = $machine->alerts()->pluck('id');

        return Activity::query()
            ->where(function (Builder $query) use ($machine, $workOrderIds, $alertIds) {
                $query->where(fn ($q) => $q->where('subject_type', Machine::class)->where('subject_id', $machine->id))
                    ->orWhere(fn ($q) => $q->where('subject_type', WorkOrder::class)->whereIn('subject_id', $workOrderIds))
                    ->orWhere(fn ($q) => $q->where('subject_type', Alert::class)->whereIn('subject_id', $alertIds));
            })
            ->with('causer')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function create(array $data): Machine
    {
        $machine = Machine::create($data);

        $this->applyDefaultThresholds($machine);

        return $machine;
    }

    public function update(Machine $machine, array $data): Machine
    {
        $machine->update($data);

        return $machine->fresh();
    }

    public function delete(Machine $machine): bool
    {
        return $machine->delete();
    }

    public function floorBelongsToBuilding(int $floorId, ?int $buildingId): bool
    {
        return Floor::where('id', $floorId)
            ->when($buildingId, fn (Builder $query) => $query->where('building_id', $buildingId))
            ->exists();
    }

    /**
     * Applies Settings' default_thresholds (configured on the Settings page) to a
     * newly-created machine — existing machines are never touched by this, only new ones.
     */
    private function applyDefaultThresholds(Machine $machine): void
    {
        $defaults = $this->settings->get('default_thresholds', []);

        foreach ($defaults as $metric => $bounds) {
            SensorThreshold::create(array_merge(
                ['machine_id' => $machine->id, 'metric' => $metric],
                $bounds
            ));
        }
    }
}
