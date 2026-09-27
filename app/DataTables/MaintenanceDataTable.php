<?php

namespace App\DataTables;

use App\Interfaces\MaintenanceInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class MaintenanceDataTable extends DataTable
{
    public function __construct(protected MaintenanceInterface $maintenance)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->maintenance->getQueryForDataTable(request()->only(['status', 'building_id']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('machine_code', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('machine', fn ($machine) => view('maintenance._datatable-machine-cell', ['machine' => $machine])->render())
            ->addColumn('building_name', fn ($machine) => $machine->building?->name ?? '—')
            ->editColumn('last_maintenance_date', fn ($machine) => $machine->last_maintenance_date?->format('M d, Y') ?? '—')
            ->editColumn('next_maintenance_date', fn ($machine) => $machine->next_maintenance_date?->format('M d, Y') ?? '—')
            ->addColumn('status', fn ($machine) => view('maintenance._datatable-status-cell', ['machine' => $machine])->render())
            ->addColumn('action', fn ($machine) => view('maintenance._datatable-actions', ['machine' => $machine])->render())
            ->rawColumns(['machine', 'status', 'action']);
    }
}
