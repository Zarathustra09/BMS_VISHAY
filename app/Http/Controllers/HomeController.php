<?php

namespace App\Http\Controllers;

use App\Interfaces\DashboardInterface;

class HomeController extends Controller
{
    public function __construct(protected DashboardInterface $dashboardService)
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $summary = $this->dashboardService->getSummary();
        $alertsTrend = $this->dashboardService->getAlertsTrend();
        $buildingHealth = $this->dashboardService->getBuildingHealth();
        $recentAlerts = $this->dashboardService->getRecentAlerts();
        $recentWorkOrders = $this->dashboardService->getRecentWorkOrders();

        return view('home', compact('summary', 'alertsTrend', 'buildingHealth', 'recentAlerts', 'recentWorkOrders'));
    }
}
