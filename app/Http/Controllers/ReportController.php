<?php

namespace App\Http\Controllers;

use App\Interfaces\ReportInterface;
use App\Models\Building;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const TYPES = ['sensor_summary', 'alert_history', 'work_order_summary', 'maintenance_compliance'];

    public function __construct(protected ReportInterface $reportService)
    {
        $this->middleware('permission:reports.view');
    }

    public function index(Request $request)
    {
        $type = $this->resolveType($request);
        $filters = $this->resolveFilters($request);
        $buildings = Building::select('id', 'name')->orderBy('name')->get();

        $report = match ($type) {
            'sensor_summary' => ['rows' => $this->reportService->getSensorSummary($filters)],
            'alert_history' => $this->reportService->getAlertHistory($filters),
            'work_order_summary' => $this->reportService->getWorkOrderSummary($filters),
            'maintenance_compliance' => $this->reportService->getMaintenanceCompliance($filters),
        };

        return view('reports.index', compact('type', 'filters', 'buildings', 'report'));
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $this->resolveType($request);
        $filters = $this->resolveFilters($request);
        $filename = "{$type}-{$filters['date_from']}-to-{$filters['date_to']}.csv";

        return match ($type) {
            'sensor_summary' => $this->csvResponse($filename,
                ['Machine', 'Code', 'Total Readings', 'Critical', 'Error', 'Warning'],
                $this->sensorSummaryRows($filters)),
            'alert_history' => $this->csvResponse($filename,
                ['Date', 'Machine', 'Type', 'Metric', 'Severity', 'Status', 'Message'],
                $this->alertHistoryRows($filters)),
            'work_order_summary' => $this->csvResponse($filename,
                ['Code', 'Title', 'Machine', 'Type', 'Priority', 'Status', 'Assigned To', 'Due Date', 'Created'],
                $this->workOrderSummaryRows($filters)),
            'maintenance_compliance' => $this->csvResponse($filename,
                ['Machine', 'Code', 'Building', 'Last Maintenance', 'Next Maintenance'],
                $this->maintenanceComplianceRows($filters)),
        };
    }

    private function resolveType(Request $request): string
    {
        return in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : 'sensor_summary';
    }

    private function resolveFilters(Request $request): array
    {
        return [
            'date_from' => $request->query('date_from') ?: now()->subDays(30)->toDateString(),
            'date_to' => $request->query('date_to') ?: now()->toDateString(),
            'building_id' => $request->query('building_id') ?: null,
        ];
    }

    private function sensorSummaryRows(array $filters): \Generator
    {
        foreach ($this->reportService->getSensorSummary($filters) as $row) {
            yield [$row->machine_name, $row->machine_code, $row->total_readings, $row->critical_count, $row->error_count, $row->warning_count];
        }
    }

    private function alertHistoryRows(array $filters): \Generator
    {
        foreach ($this->reportService->getAlertHistoryQuery($filters)->cursor() as $alert) {
            yield [
                $alert->created_at->format('Y-m-d H:i'),
                $alert->machine->name ?? '—',
                $alert->type,
                $alert->metric ?? '—',
                $alert->severity,
                $alert->status,
                $alert->message,
            ];
        }
    }

    private function workOrderSummaryRows(array $filters): \Generator
    {
        foreach ($this->reportService->getWorkOrderSummaryQuery($filters)->cursor() as $workOrder) {
            yield [
                $workOrder->code,
                $workOrder->title,
                $workOrder->machine->name ?? '—',
                $workOrder->type,
                $workOrder->priority,
                $workOrder->status,
                $workOrder->assignedTo->name ?? 'Unassigned',
                $workOrder->due_date?->format('Y-m-d') ?? '—',
                $workOrder->created_at->format('Y-m-d H:i'),
            ];
        }
    }

    private function maintenanceComplianceRows(array $filters): \Generator
    {
        foreach ($this->reportService->getMaintenanceComplianceQuery($filters)->cursor() as $machine) {
            yield [
                $machine->name,
                $machine->machine_code,
                $machine->building->name ?? '—',
                $machine->last_maintenance_date?->format('Y-m-d') ?? '—',
                $machine->next_maintenance_date?->format('Y-m-d') ?? '—',
            ];
        }
    }

    private function csvResponse(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
