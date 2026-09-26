<?php

namespace App\Services;

use App\Interfaces\AlertInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineLog;
use App\Models\SensorThreshold;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class AlertService implements AlertInterface
{
    public function evaluate(MachineLog $log): void
    {
        // A reading arriving is proof of life — clear any offline alert first. This MUST
        // stay above the thresholds early-return below, or a machine with no configured
        // thresholds could never clear its offline alert.
        if ($log->machine) {
            $this->resolveOfflineAlerts($log->machine);
        }

        $thresholds = SensorThreshold::where('machine_id', $log->machine_id)->get()->keyBy('metric');

        if ($thresholds->isEmpty()) {
            return;
        }

        $machine = $log->machine;

        foreach (self::METRICS as $metric) {
            $value = $log->{$metric};

            if ($value === null) {
                continue;
            }

            $threshold = $thresholds->get($metric);

            if (!$threshold) {
                continue;
            }

            $severity = $this->determineSeverity($threshold, (float) $value);

            $openAlert = Alert::open()
                ->where('machine_id', $log->machine_id)
                ->where('type', 'threshold')
                ->where('metric', $metric)
                ->first();

            if ($severity === null) {
                // Reading is back within bounds — clear whatever was open for this metric.
                if ($openAlert) {
                    $this->resolve($openAlert);
                }

                continue;
            }

            if ($openAlert) {
                // Keep one open alert per machine+metric. Only escalate (never downgrade —
                // downgrading to "still abnormal but less severe" isn't the same as normal,
                // so it doesn't get a fresh notification either) and always track the
                // latest reading behind it.
                $openAlert->update([
                    'value' => $value,
                    'machine_log_id' => $log->id,
                    'severity' => $openAlert->severity === 'critical' ? 'critical' : $severity,
                ]);

                if ($openAlert->severity === 'critical' && $severity === 'critical' && $openAlert->wasChanged('severity')) {
                    $openAlert->update(['message' => $this->buildMessage($machine, $metric, $value, $severity)]);
                    $this->notify($openAlert);
                }

                continue;
            }

            $alert = Alert::create([
                'machine_id' => $log->machine_id,
                'machine_log_id' => $log->id,
                'type' => 'threshold',
                'metric' => $metric,
                'value' => $value,
                'severity' => $severity,
                'status' => 'open',
                'message' => $this->buildMessage($machine, $metric, $value, $severity),
            ]);

            $this->notify($alert);
        }
    }

    public function raiseOfflineAlerts(int $minutes = self::OFFLINE_AFTER_MINUTES): array
    {
        $cutoff = now()->subMinutes($minutes);

        $machines = Machine::query()
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', $cutoff)
            ->where('status', '!=', 'inactive')
            ->whereDoesntHave('alerts', function (Builder $query) {
                $query->where('type', 'offline')->whereIn('status', ['open', 'acknowledged']);
            })
            ->get();

        $raised = [];

        foreach ($machines as $machine) {
            $alert = Alert::create([
                'machine_id' => $machine->id,
                'machine_log_id' => null,
                'type' => 'offline',
                'metric' => null,
                'value' => null,
                'severity' => $machine->is_critical ? 'critical' : 'warning',
                'status' => 'open',
                'message' => $this->buildOfflineMessage($machine, $minutes),
            ]);

            $this->notify($alert);
            $raised[$machine->id] = $machine->name;
        }

        return ['checked' => $machines->count(), 'raised' => count($raised), 'machines' => $raised];
    }

    public function resolveOfflineAlerts(Machine $machine): int
    {
        $alerts = Alert::where('machine_id', $machine->id)
            ->where('type', 'offline')
            ->whereIn('status', ['open', 'acknowledged'])
            ->get();

        $alerts->each(fn (Alert $alert) => $this->resolve($alert));

        return $alerts->count();
    }

    public function getThresholds(Machine $machine): Collection
    {
        return $machine->thresholds()->get()->keyBy('metric');
    }

    public function getActiveForMachine(Machine $machine, int $limit = 10): Collection
    {
        return $machine->alerts()
            ->whereIn('status', ['open', 'acknowledged'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function saveThresholds(Machine $machine, array $thresholds): void
    {
        foreach (self::METRICS as $metric) {
            $row = $thresholds[$metric] ?? [];

            $bounds = [
                'warning_min' => $this->nullableDecimal($row['warning_min'] ?? null),
                'warning_max' => $this->nullableDecimal($row['warning_max'] ?? null),
                'critical_min' => $this->nullableDecimal($row['critical_min'] ?? null),
                'critical_max' => $this->nullableDecimal($row['critical_max'] ?? null),
            ];

            if (collect($bounds)->every(fn ($v) => $v === null)) {
                SensorThreshold::where('machine_id', $machine->id)->where('metric', $metric)->delete();

                continue;
            }

            SensorThreshold::updateOrCreate(
                ['machine_id' => $machine->id, 'metric' => $metric],
                $bounds
            );
        }
    }

    public function getQueryForDataTable(array $filters): Builder
    {
        $query = Alert::with('machine');

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['severity']) && $filters['severity'] !== 'all') {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['machine_id']) && $filters['machine_id'] !== 'all') {
            $query->where('machine_id', $filters['machine_id']);
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        return $query->orderByDesc('created_at');
    }

    public function getStats(): array
    {
        return [
            'open' => Alert::where('status', 'open')->count(),
            'critical' => Alert::where('status', 'open')->where('severity', 'critical')->count(),
            'acknowledged' => Alert::where('status', 'acknowledged')->count(),
            'resolved_today' => Alert::where('status', 'resolved')->whereDate('resolved_at', today())->count(),
        ];
    }

    public function acknowledge(Alert $alert, User $user): Alert
    {
        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);

        return $alert->fresh();
    }

    public function resolve(Alert $alert): Alert
    {
        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return $alert->fresh();
    }

    private function determineSeverity(SensorThreshold $threshold, float $value): ?string
    {
        if ($threshold->critical_min !== null && $value < (float) $threshold->critical_min) {
            return 'critical';
        }

        if ($threshold->critical_max !== null && $value > (float) $threshold->critical_max) {
            return 'critical';
        }

        if ($threshold->warning_min !== null && $value < (float) $threshold->warning_min) {
            return 'warning';
        }

        if ($threshold->warning_max !== null && $value > (float) $threshold->warning_max) {
            return 'warning';
        }

        return null;
    }

    private function buildMessage(Machine $machine, string $metric, mixed $value, string $severity): string
    {
        $label = ucfirst(str_replace('_', ' ', $metric));

        return "{$label} on {$machine->name} reached {$value} ({$severity} level).";
    }

    /**
     * Absolute timestamp, never diffForHumans() — this text is stored and would go stale.
     */
    private function buildOfflineMessage(Machine $machine, int $minutes): string
    {
        return "{$machine->name} has not sent a sensor reading since "
            . $machine->last_seen_at->format('M d, Y H:i')
            . " (offline for more than {$minutes} minutes).";
    }

    /**
     * Admins and managers get alert notifications — the roles with `alerts.resolve`
     * and oversight responsibility across machines (see docs/06-security.md).
     */
    private function notify(Alert $alert): void
    {
        $recipients = User::role(['admin', 'manager'])->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new MachineAlertNotification($alert));
        }
    }

    private function nullableDecimal(mixed $value): ?float
    {
        return ($value === null || $value === '') ? null : (float) $value;
    }
}
