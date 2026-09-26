<?php

namespace App\DataTables;

use App\Interfaces\AlertInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class AlertsDataTable extends DataTable
{
    public function __construct(protected AlertInterface $alerts)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->alerts->getQueryForDataTable(request()->only(['status', 'severity', 'machine_id', 'type']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('message', 'like', "%{$search}%")
                          ->orWhereHas('machine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
                    });
                }
            })
            ->addColumn('machine', fn ($alert) => $alert->machine
                ? '<a href="' . route('machines.show', $alert->machine) . '" class="link link-hover font-medium">' . e($alert->machine->name) . '</a>'
                : '—')
            ->editColumn('metric', fn ($alert) => $alert->metric ? e(ucfirst(str_replace('_', ' ', $alert->metric))) : e(ucfirst($alert->type)))
            ->editColumn('severity', fn ($alert) => view('alerts._datatable-severity-cell', ['alert' => $alert])->render())
            ->editColumn('status', fn ($alert) => view('alerts._datatable-status-cell', ['alert' => $alert])->render())
            ->editColumn('created_at', fn ($alert) => $alert->created_at->format('M d, Y H:i'))
            ->addColumn('action', fn ($alert) => view('alerts._datatable-actions', ['alert' => $alert])->render())
            ->rawColumns(['machine', 'severity', 'status', 'action']);
    }
}
