<?php

namespace App\Services\Concerns;

use App\Enums\SpecFieldType;
use App\Models\Machine;

/**
 * Shared prompt-building logic for AIInterface implementations (OpenAIService, GeminiService),
 * so both providers analyze the same machine data the same way.
 */
trait BuildsMachineContext
{
    /**
     * Build context/metadata from machine logs for the AI prompt
     */
    public function buildMachineContext(Machine $machine): string
    {
        // Load recent logs for analysis
        $recentLogs = $machine->logs()
            ->orderBy('logged_at', 'desc')
            ->limit(50)
            ->get();

        // Calculate statistics
        $stats = [
            'total_logs' => $machine->logs()->count(),
            'critical_count' => $machine->logs()->where('severity', 'critical')->count(),
            'error_count' => $machine->logs()->where('severity', 'error')->count(),
            'warning_count' => $machine->logs()->where('severity', 'warning')->count(),
            'logs_today' => $machine->logs()->whereDate('logged_at', today())->count(),
            'logs_this_week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
        ];

        // Get sensor averages from recent logs
        $sensorAverages = [
            'avg_temperature' => $recentLogs->whereNotNull('temperature')->avg('temperature'),
            'avg_power_consumption' => $recentLogs->whereNotNull('power_consumption')->avg('power_consumption'),
            'avg_vibration_rms' => $recentLogs->whereNotNull('vibration_rms')->avg('vibration_rms'),
            'avg_pressure' => $recentLogs->whereNotNull('pressure')->avg('pressure'),
            'avg_humidity' => $recentLogs->whereNotNull('humidity')->avg('humidity'),
            'avg_voltage' => $recentLogs->whereNotNull('voltage')->avg('voltage'),
        ];

        // Get recent alerts
        $recentAlerts = $machine->logs()
            ->whereIn('severity', ['error', 'critical'])
            ->orderBy('logged_at', 'desc')
            ->limit(10)
            ->get(['event', 'severity', 'logged_at', 'notes']);

        // Build context string
        $context = "=== MACHINE INFORMATION ===\n";
        $context .= "Name: {$machine->name}\n";
        $context .= "Code: {$machine->machine_code}\n";
        $context .= 'Type: '.($machine->machineType?->name ?? 'Unknown')."\n";
        $context .= "Status: {$machine->status}\n";
        $context .= 'Location: '.($machine->building?->name ?? 'N/A').' / '.($machine->floor?->name ?? 'N/A')."\n";
        $context .= 'Manufacturer: '.($machine->manufacturer ?? 'N/A')."\n";
        $context .= 'Model: '.($machine->model_number ?? 'N/A')."\n";
        $context .= 'Is Critical: '.($machine->is_critical ? 'Yes' : 'No')."\n";

        foreach ($machine->machineType?->specFields() ?? [] as $field) {
            $kind = SpecFieldType::tryFrom($field['type'] ?? '') ?? SpecFieldType::Text;
            $value = $machine->specifications[$field['key']] ?? null;

            if ($kind->isEmpty($value)) {
                continue;
            }

            $unit = $field['unit'] ?? null;
            $context .= "{$field['label']}: ".$kind->display($value).($unit ? " {$unit}" : '')."\n";
        }

        if ($machine->power_rating) {
            $context .= "Power Rating: {$machine->power_rating} kW\n";
        }
        if ($machine->installation_date) {
            $context .= "Installation Date: {$machine->installation_date->format('Y-m-d')}\n";
            $context .= "Age: {$machine->installation_date->diffForHumans(null, true)}\n";
        }
        if ($machine->last_maintenance_date) {
            $context .= "Last Maintenance: {$machine->last_maintenance_date->format('Y-m-d')}\n";
        }
        if ($machine->next_maintenance_date) {
            $context .= "Next Maintenance: {$machine->next_maintenance_date->format('Y-m-d')}\n";
            $daysUntil = now()->diffInDays($machine->next_maintenance_date, false);
            $context .= 'Maintenance Status: '.($daysUntil < 0 ? 'OVERDUE by '.abs($daysUntil).' days' : "Due in {$daysUntil} days")."\n";
        }

        $context .= "\n=== LOG STATISTICS ===\n";
        $context .= "Total Logs: {$stats['total_logs']}\n";
        $context .= "Logs Today: {$stats['logs_today']}\n";
        $context .= "Logs This Week: {$stats['logs_this_week']}\n";
        $context .= "Critical Alerts: {$stats['critical_count']}\n";
        $context .= "Errors: {$stats['error_count']}\n";
        $context .= "Warnings: {$stats['warning_count']}\n";

        $context .= "\n=== SENSOR AVERAGES (Last 50 Logs) ===\n";
        foreach ($sensorAverages as $key => $value) {
            if ($value !== null) {
                $label = ucwords(str_replace(['avg_', '_'], ['', ' '], $key));
                $context .= "{$label}: ".round($value, 2)."\n";
            }
        }

        if ($recentAlerts->isNotEmpty()) {
            $context .= "\n=== RECENT ALERTS ===\n";
            foreach ($recentAlerts as $alert) {
                $context .= "[{$alert->severity}] {$alert->logged_at->format('Y-m-d H:i')} - {$alert->event}";
                if ($alert->notes) {
                    $context .= " (Note: {$alert->notes})";
                }
                $context .= "\n";
            }
        }

        // Add recent log entries for detailed analysis
        if ($recentLogs->isNotEmpty()) {
            $context .= "\n=== RECENT LOG ENTRIES (Last 10) ===\n";
            foreach ($recentLogs->take(10) as $log) {
                $context .= "[{$log->logged_at->format('Y-m-d H:i')}] ";
                $context .= "Type: {$log->log_type}, Severity: {$log->severity}, Event: {$log->event}";

                $sensors = [];
                if ($log->temperature) {
                    $sensors[] = "Temp: {$log->temperature}°C";
                }
                if ($log->power_consumption) {
                    $sensors[] = "Power: {$log->power_consumption}kW";
                }
                if ($log->vibration_rms) {
                    $sensors[] = "Vibration: {$log->vibration_rms}RMS";
                }
                if ($log->pressure) {
                    $sensors[] = "Pressure: {$log->pressure}bar";
                }

                if (! empty($sensors)) {
                    $context .= ' | '.implode(', ', $sensors);
                }
                $context .= "\n";
            }
        }

        return $context;
    }

    /**
     * Build the full prompt combining context and user message
     */
    protected function buildPrompt(string $context, string $userMessage): string
    {
        $systemPrompt = 'You are an expert Building Management System (BMS) AI assistant specializing in industrial machine analysis for manufacturing facilities. Your role is to analyze machine data, logs, and sensor readings to provide actionable insights.

When analyzing machine data:
1. Identify patterns in sensor readings and log events
2. Detect anomalies or concerning trends
3. Provide maintenance recommendations
4. Assess machine health and risk levels
5. Suggest optimizations for efficiency

Always be concise, professional, and data-driven in your responses. Format important findings with bullet points and highlight critical issues.

';

        return $systemPrompt.$context."\n\n=== USER QUESTION ===\n".$userMessage;
    }
}
