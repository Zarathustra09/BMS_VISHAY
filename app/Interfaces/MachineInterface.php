<?php

namespace App\Interfaces;

use App\Models\Machine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MachineInterface
{
    public function getAllPaginated(array $filters, int $perPage = 12): LengthAwarePaginator;

    public function getStats(): array;

    public function getDistinctTypes(): Collection;

    public function findById(int $id): Machine;

    public function getLogsForMachine(Machine $machine, int $perPage = 50): LengthAwarePaginator;

    public function getLogStats(Machine $machine): array;

    public function getRecentAlerts(Machine $machine, int $limit = 10): Collection;

    public function getLogTypeDistribution(Machine $machine): Collection;

    public function create(array $data): Machine;

    public function update(Machine $machine, array $data): Machine;

    public function delete(Machine $machine): bool;
}
