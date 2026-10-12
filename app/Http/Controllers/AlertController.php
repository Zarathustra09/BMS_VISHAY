<?php

namespace App\Http\Controllers;

use App\DataTables\AlertsDataTable;
use App\Interfaces\AlertInterface;
use App\Models\Alert;
use App\Models\Machine;

class AlertController extends Controller
{
    public function __construct(protected AlertInterface $alertService)
    {
        $this->middleware('permission:alerts.view')->only(['index', 'data', 'show', 'stats']);
        $this->middleware('permission:alerts.acknowledge')->only(['acknowledge']);
        $this->middleware('permission:alerts.resolve')->only(['resolve']);
    }

    public function index()
    {
        $stats = $this->alertService->getStats();
        $machines = Machine::select('id', 'name')->orderBy('name')->get();

        return view('alerts.index', compact('stats', 'machines'));
    }

    public function data(AlertsDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    /**
     * One alert with its full trail: what happened to it, who did it and when.
     */
    public function show(Alert $alert)
    {
        $alert->load(['machine', 'acknowledgedBy', 'resolvedBy', 'events.user']);

        return view('alerts.show', compact('alert'));
    }

    public function stats()
    {
        return response()->json($this->alertService->getStats());
    }

    public function acknowledge(Alert $alert)
    {
        $this->alertService->acknowledge($alert, auth()->user());

        return back()->with('success', 'Alert acknowledged.');
    }

    public function resolve(Alert $alert)
    {
        $this->alertService->resolve($alert, auth()->user());

        return back()->with('success', 'Alert resolved.');
    }
}
