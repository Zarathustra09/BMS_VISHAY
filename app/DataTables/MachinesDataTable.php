<?php

namespace App\DataTables;

use App\Interfaces\MachineInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class MachinesDataTable extends DataTable
{
    public function __construct(protected MachineInterface $machines)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->machines->getQueryForDataTable(request()->only(['status', 'type', 'building_id', 'floor_id']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                // Overrides automatic search — replicates the old form's OR search across
                // name/machine_code/model_number/serial_number.
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('machine_code', 'like', "%{$search}%")
                          ->orWhere('model_number', 'like', "%{$search}%")
                          ->orWhere('serial_number', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('machine', fn ($machine) => view('machines._datatable-name-cell', ['machine' => $machine])->render())
            ->editColumn('status', fn ($machine) => view('machines._datatable-status-cell', ['machine' => $machine])->render())
            ->addColumn('building_name', fn ($machine) => $machine->building?->name ?? '—')
            ->addColumn('floor_name', fn ($machine) => $machine->floor?->name ?? '—')
            ->editColumn('manufacturer', fn ($machine) => $machine->manufacturer ?? '—')
            ->addColumn('maintenance', fn ($machine) => view('machines._datatable-maintenance-cell', ['machine' => $machine])->render())
            ->addColumn('action', fn ($machine) => view('machines._datatable-actions', ['machine' => $machine])->render())
            ->rawColumns(['machine', 'status', 'maintenance', 'action']);
    }
}
