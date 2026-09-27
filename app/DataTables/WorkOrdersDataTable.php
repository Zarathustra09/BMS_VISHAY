<?php

namespace App\DataTables;

use App\Interfaces\WorkOrderInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class WorkOrdersDataTable extends DataTable
{
    public function __construct(protected WorkOrderInterface $workOrders)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->workOrders->getQueryForDataTable(request()->only(['status', 'priority', 'type', 'machine_id']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                          ->orWhere('title', 'like', "%{$search}%")
                          ->orWhereHas('machine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
                    });
                }
            })
            ->addColumn('work_order', fn ($wo) => view('work_orders._datatable-title-cell', ['workOrder' => $wo])->render())
            ->editColumn('type', fn ($wo) => '<div class="badge badge-outline badge-sm">' . e(ucfirst($wo->type)) . '</div>')
            ->editColumn('priority', fn ($wo) => view('work_orders._datatable-priority-cell', ['workOrder' => $wo])->render())
            ->editColumn('status', fn ($wo) => view('work_orders._datatable-status-cell', ['workOrder' => $wo])->render())
            ->addColumn('assignee', fn ($wo) => $wo->assignedTo->name ?? '<span class="opacity-50">Unassigned</span>')
            ->editColumn('due_date', fn ($wo) => $wo->due_date?->format('M d, Y') ?? '—')
            ->addColumn('action', fn ($wo) => view('work_orders._datatable-actions', ['workOrder' => $wo])->render())
            ->rawColumns(['work_order', 'type', 'priority', 'status', 'assignee', 'action']);
    }
}
