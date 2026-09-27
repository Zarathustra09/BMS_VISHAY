<?php

namespace App\Http\Controllers;

use App\DataTables\MaintenanceDataTable;
use App\Interfaces\MaintenanceInterface;
use App\Models\Building;

class MaintenanceController extends Controller
{
    public function __construct(protected MaintenanceInterface $maintenanceService)
    {
        $this->middleware('permission:maintenance.view')->only(['index', 'data']);
    }

    public function index()
    {
        $stats = $this->maintenanceService->getStats();
        $buildingBreakdown = $this->maintenanceService->getBuildingBreakdown();
        $buildings = Building::select('id', 'name')->orderBy('name')->get();

        return view('maintenance.index', compact('stats', 'buildingBreakdown', 'buildings'));
    }

    public function data(MaintenanceDataTable $dataTable)
    {
        return $dataTable->ajax();
    }
}
