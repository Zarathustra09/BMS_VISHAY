<?php

namespace App\Console\Commands;

use App\Interfaces\AlertInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOfflineDevices extends Command
{
    protected $signature = 'app:check-offline-devices {--minutes= : Minutes without a reading before a machine counts as offline (default 10)}';
    protected $description = 'Raise an offline alert for machines that have stopped sending sensor readings';

    public function handle(AlertInterface $alerts)
    {
        // Not `?:` — the string "0" is falsy in PHP, so --minutes=0 would silently fall
        // through to the default instead of being rejected by the check below.
        $minutesOption = $this->option('minutes');
        $minutes = $minutesOption !== null ? (int) $minutesOption : AlertInterface::OFFLINE_AFTER_MINUTES;

        if ($minutes < 1) {
            $this->error('--minutes must be at least 1.');

            return 1;
        }

        $result = $alerts->raiseOfflineAlerts($minutes);

        if ($result['raised'] === 0) {
            $this->info("No newly offline devices (threshold: {$minutes} minutes).");

            return 0;
        }

        $this->warn("{$result['raised']} device(s) went offline: " . implode(', ', $result['machines']));

        // Only logged when something was raised — this runs every minute, so logging on
        // every no-op run would add 1,440 useless lines/day to storage/logs/laravel.log.
        Log::warning("Offline device check raised {$result['raised']} alert(s) at " . now()->toDateTimeString()
            . ' — ' . implode(', ', $result['machines']));

        return 0;
    }
}
