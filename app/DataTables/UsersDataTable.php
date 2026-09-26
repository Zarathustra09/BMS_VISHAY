<?php

namespace App\DataTables;

use App\Interfaces\UserInterface;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public function __construct(protected UserInterface $users)
    {
        parent::__construct();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->users->getQueryForDataTable(request()->only(['role']));
    }

    public function dataTable($query): EloquentDataTable
    {
        return DataTables::eloquent($query)
            ->filter(function ($query) {
                // Overrides automatic search — the old form searched name/email together.
                if ($search = request()->input('search.value')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            })
            ->addColumn('role', fn ($user) => view('users._datatable-role-cell', ['user' => $user])->render())
            ->addColumn('joined', fn ($user) => $user->created_at->format('M d, Y'))
            ->addColumn('action', fn ($user) => view('users._datatable-actions', ['user' => $user])->render())
            ->rawColumns(['role', 'action']);
    }
}
