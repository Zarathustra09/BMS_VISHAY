<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $query = Machine::with(['logs' => function($query) {
            $query->latest('logged_at')->limit(1);
        }]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('machine_code', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Zone filter
        if ($request->filled('zone') && $request->zone !== 'all') {
            $query->where('zone', $request->zone);
        }

        $machines = $query->paginate(12);

        // Stats
        $stats = [
            'total' => Machine::count(),
            'active' => Machine::where('status', 'active')->count(),
            'maintenance' => Machine::where('status', 'maintenance')->count(),
            'fault' => Machine::where('status', 'fault')->count(),
            'inactive' => Machine::where('status', 'inactive')->count(),
            'critical' => Machine::where('is_critical', true)->count(),
        ];

        // Get unique values for filters
        $types = Machine::select('type')->distinct()->pluck('type');
        $zones = Machine::select('zone')->distinct()->whereNotNull('zone')->pluck('zone');

        return view('machines.index', compact('machines', 'stats', 'types', 'zones'));
    }

    public function show(Machine $machine)
    {
        $machine->load(['logs' => function($query) {
            $query->orderBy('logged_at', 'desc');
        }]);

        // Get logs with pagination
        $logs = $machine->logs()->orderBy('logged_at', 'desc')->paginate(50);

        // Log statistics
        $logStats = [
            'total' => $machine->logs()->count(),
            'today' => $machine->logs()->whereDate('logged_at', today())->count(),
            'week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
            'critical' => $machine->logs()->where('severity', 'critical')->count(),
            'errors' => $machine->logs()->where('severity', 'error')->count(),
            'warnings' => $machine->logs()->where('severity', 'warning')->count(),
        ];

        // Recent critical/error logs
        $recentAlerts = $machine->logs()
            ->whereIn('severity', ['critical', 'error'])
            ->orderBy('logged_at', 'desc')
            ->limit(10)
            ->get();

        // Log type distribution
        $logTypeDistribution = $machine->logs()
            ->selectRaw('log_type, COUNT(*) as count')
            ->groupBy('log_type')
            ->pluck('count', 'log_type');

        return view('machines.show', compact('machine', 'logs', 'logStats', 'recentAlerts', 'logTypeDistribution'));
    }
}
