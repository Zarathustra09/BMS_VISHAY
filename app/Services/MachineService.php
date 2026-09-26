<?php

namespace App\Services;

use App\Interfaces\MachineInterface;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MachineService implements MachineInterface
{
    public function getQueryForDataTable(array $filters): Builder
    {
        $query = Machine::with(['building', 'floor']);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['building_id']) && $filters['building_id'] !== 'all') {
            $query->where('building_id', $filters['building_id']);
        }

        if (!empty($filters['floor_id']) && $filters['floor_id'] !== 'all') {
            $query->where('floor_id', $filters['floor_id']);
        }

        return $query;
    }

    public function getStats(): array
    {
        return [
            'total'       => Machine::count(),
            'active'      => Machine::where('status', 'active')->count(),
            'maintenance' => Machine::where('status', 'maintenance')->count(),
            'fault'       => Machine::where('status', 'fault')->count(),
            'inactive'    => Machine::where('status', 'inactive')->count(),
            'critical'    => Machine::where('is_critical', true)->count(),
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
            'total'    => $machine->logs()->count(),
            'today'    => $machine->logs()->whereDate('logged_at', today())->count(),
            'week'     => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
            'critical' => $machine->logs()->where('severity', 'critical')->count(),
            'errors'   => $machine->logs()->where('severity', 'error')->count(),
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

    public function create(array $data): Machine
    {
        return Machine::create($data);
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
}
