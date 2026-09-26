<?php

namespace App\Interfaces;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface MachineInterface
{
    /**
     * Query for the machines DataTable — filtered by search/status/type/building/floor.
     * Business logic (which columns, which filters) stays here; the DataTable class
     * only wires the query into Yajra's response format.
     */
    public function getQueryForDataTable(array $filters): Builder;

    public function getStats(): array;

    public function getDistinctTypes(): Collection;

    public function findById(int $id): Machine;

    public function getLogStats(Machine $machine): array;

    public function getRecentAlerts(Machine $machine, int $limit = 10): Collection;

    public function getLogTypeDistribution(Machine $machine): Collection;

    public function create(array $data): Machine;

    public function update(Machine $machine, array $data): Machine;

    public function delete(Machine $machine): bool;
}
