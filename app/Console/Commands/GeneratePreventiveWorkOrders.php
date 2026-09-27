<?php

namespace App\Console\Commands;

use App\Interfaces\WorkOrderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GeneratePreventiveWorkOrders extends Command
{
    protected $signature = 'app:generate-preventive-work-orders {--lead-days= : How many days ahead of next_maintenance_date to generate a work order (default 7)}';
    protected $description = 'Create a preventive work order for machines approaching their next maintenance date';

    private const DEFAULT_LEAD_DAYS = 7;

    public function handle(WorkOrderInterface $workOrders)
    {
        // Not `?:` — the string "0" is falsy in PHP, so --lead-days=0 would silently fall
        // through to the default instead of being honoured (same trap fixed in
        // CheckOfflineDevices).
        $leadDaysOption = $this->option('lead-days');
        $leadDays = $leadDaysOption !== null ? (int) $leadDaysOption : self::DEFAULT_LEAD_DAYS;

        if ($leadDays < 0) {
            $this->error('--lead-days must be zero or greater.');

            return 1;
        }

        $result = $workOrders->generatePreventiveWorkOrders($leadDays);

        if ($result['created'] === 0) {
            $this->info("No new preventive work orders needed (lead time: {$leadDays} days).");

            return 0;
        }

        $this->info("{$result['created']} preventive work order(s) created: " . implode(', ', $result['machines']));

        // Only logged when something was created — this runs daily, but a per-run log line
        // regardless would still be near-silent noise; consistent with app:check-offline-devices.
        Log::info("Preventive work order generation created {$result['created']} work order(s) at " . now()->toDateTimeString()
            . ' — ' . implode(', ', $result['machines']));

        return 0;
    }
}
