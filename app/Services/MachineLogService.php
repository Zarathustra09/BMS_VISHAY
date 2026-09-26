<?php

namespace App\Services;

use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;

class MachineLogService implements MachineLogInterface
{
    public function store(array $data): MachineLog
    {
        if (empty($data['logged_at'])) {
            $data['logged_at'] = now();
        }

        return MachineLog::create($data);
    }

    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder
    {
        $query = MachineLog::query()->where('machine_id', $machine->id);

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['log_type'])) {
            $query->where('log_type', $filters['log_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('logged_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('logged_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('logged_at');
    }
}
