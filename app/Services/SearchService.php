<?php

namespace App\Services;

use App\Enums\MachineStatus;
use App\Interfaces\SearchInterface;
use App\Models\Machine;

class SearchService implements SearchInterface
{
    public function searchMachines(string $term, int $limit = 8): array
    {
        return Machine::query()
            ->with('machineType:id,name')
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function (Machine $machine): array {
                $status = MachineStatus::tryFrom((string) $machine->status);

                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'code' => $machine->machine_code,
                    'type' => $machine->machineType?->name,
                    'status' => (string) $machine->status,
                    'status_label' => $status?->label() ?? ucfirst((string) $machine->status),
                    'status_badge' => $status?->badgeClass() ?? 'badge-ghost',
                    'url' => route('machines.show', $machine),
                ];
            })
            ->all();
    }
}
