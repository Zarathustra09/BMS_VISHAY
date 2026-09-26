<?php

namespace App\Interfaces;

use App\Models\Machine;
use App\Models\MachineLog;
use Illuminate\Database\Eloquent\Builder;

interface MachineLogInterface
{
    public function store(array $data): MachineLog;

    /**
     * Query for the machine-logs DataTable on machines/show — scoped to one machine,
     * optionally filtered by severity, log_type, and a logged_at date range.
     */
    public function getQueryForDataTable(Machine $machine, array $filters = []): Builder;
}
