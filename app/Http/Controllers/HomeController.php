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

    /**
     * Fresh alert counters and the recent-alerts list, for the live refresh on the dashboard.
     */
    public function alertsLive()
    {
        $alerts = $this->dashboardService->getSummary()['alerts'];

        return response()->json([
            'open' => $alerts['open'],
            'critical' => $alerts['critical'],
            'recent_html' => view('partials._recent-alerts', ['recentAlerts' => $this->dashboardService->getRecentAlerts()])->render(),
        ]);
    }
}
