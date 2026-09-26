<?php

namespace App\DataTables;

use App\Interfaces\MachineLogInterface;
use App\Models\Machine;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class MachineLogsDataTable extends DataTable
{
    protected Machine $machine;

    public function __construct(protected MachineLogInterface $machineLogs)
    {
        parent::__construct();
    }

    /**
     * Scope this DataTable instance to one machine. Must be called before ajax().
     */
    public function forMachine(Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->machineLogs->getQueryForDataTable(
            $this->machine,
            request()->only(['severity', 'log_type', 'date_from', 'date_to'])
        );
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->editColumn('logged_at', fn ($log) => view('machines._datatable-timestamp-cell', ['log' => $log])->render())
            ->editColumn('log_type', fn ($log) => '<div class="badge badge-outline badge-sm">' . e(str_replace('_', ' ', ucfirst($log->log_type))) . '</div>')
            ->editColumn('event', fn ($log) => view('machines._datatable-event-cell', ['log' => $log])->render())
            ->editColumn('severity', fn ($log) => view('machines._datatable-severity-cell', ['log' => $log])->render())
            ->addColumn('sensors', fn ($log) => view('machines._datatable-sensors-cell', ['log' => $log])->render())
            ->editColumn('performed_by', fn ($log) => $log->performed_by ?? 'System')
            ->rawColumns(['logged_at', 'log_type', 'event', 'severity', 'sensors']);
    }
}
