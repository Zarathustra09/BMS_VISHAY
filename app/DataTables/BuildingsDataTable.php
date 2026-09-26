<?php

namespace App\DataTables;

use App\Interfaces\BuildingInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class BuildingsDataTable extends DataTable
{
    public function __construct(protected BuildingInterface $buildings)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->buildings->getQueryForDataTable(request()->only(['status', 'type']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                // Overrides DataTables' automatic per-column search (which can't search
                // computed columns like the combined "building" cell) with the same
                // multi-field OR search the old card-grid filter form used.
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('city', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('building', fn ($building) => view('buildings._datatable-name-cell', ['building' => $building])->render())
            ->editColumn('status', fn ($building) => view('buildings._datatable-status-cell', ['building' => $building])->render())
            ->editColumn('city', fn ($building) => $building->city ?? '—')
            ->addColumn('action', fn ($building) => view('buildings._datatable-actions', ['building' => $building])->render())
            ->rawColumns(['building', 'status', 'action']);
    }
}
