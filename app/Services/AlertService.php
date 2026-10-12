<?php

namespace App\Services;

use App\Events\AlertChanged;
use App\Interfaces\AlertInterface;
use App\Interfaces\SettingInterface;
use App\Models\Alert;
use App\Models\AlertEvent;
use App\Models\Machine;
use App\Models\MachineLog;
use App\Models\MachineParameter;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class AlertService implements AlertInterface
{
    public function __construct(
        protected SettingInterface $settings,
        protected SafeBroadcaster $broadcaster,
    ) {}

    public function evaluate(MachineLog $log): ?string
    {
        // A reading arriving is proof of life — clear any offline alert first. This MUST
        // stay above the thresholds early-return below, or a machine with no configured
        // thresholds could never clear its offline alert.
        if ($log->machine) {
            $this->resolveOfflineAlerts($log->machine);
        }

        $parameters = MachineParameter::where('machine_id', $log->machine_id)
            ->where('data_type', 'number')
            ->get()
            ->filter(fn (MachineParameter $parameter) => $parameter->hasThresholds());

        if ($parameters->isEmpty() || empty($log->readings)) {
            return null;
        }

        $machine = $log->machine;
        $worst = null;

        foreach ($parameters as $parameter) {
            $metric = $parameter->key;
            $value = $log->readings[$metric] ?? null;

            if ($value === null || ! is_numeric($value)) {
                continue;
            }

            $severity = $parameter->severityFor((float) $value);

            if ($severity === 'critical' || ($severity === 'warning' && $worst === null)) {
                $worst = $severity;
            }

            // An acknowledged alert is still an unresolved problem: it must auto-resolve when the
            // reading recovers, and a new breach must reuse it rather than open a duplicate.
            $openAlert = Alert::query()
                ->whereIn('status', ['open', 'acknowledged'])
                ->where('machine_id', $log->machine_id)
                ->where('type', 'threshold')
                ->where('metric', $metric)
                ->first();

            if ($severity === null) {
                // Reading is back within bounds — clear whatever was open for this metric.
                if ($openAlert) {
                    $this->resolve($openAlert, null, 'auto', $value);
                }

                continue;
            }

            if ($openAlert) {
                // Keep one alert per machine+metric. Only escalate (never downgrade —
                // downgrading to "still abnormal but less severe" isn't the same as normal,
                // so it doesn't get a fresh notification either) and always track the
                // latest reading behind it.
                $wasCritical = $openAlert->severity === 'critical';

                $openAlert->update([
                    'value' => $value,
                    'machine_log_id' => $log->id,
                    'severity' => $wasCritical ? 'critical' : $severity,
                ]);

                if (! $wasCritical && $severity === 'critical') {
                    $previousStatus = $openAlert->status;

                    // Getting worse after someone acknowledged it re-opens it: an earlier "I'm on
                    // it" must not hide a problem that has since become critical.
                    $openAlert->update([
                        'message' => $this->buildMessage($machine, $parameter, $value, $severity),
                        'status' => 'open',
                        'acknowledged_by' => null,
                        'acknowledged_at' => null,
                    ]);

                    $this->recordEvent($openAlert, 'escalated', null, $previousStatus, 'open', $value);
                    $this->notify($openAlert);
                    $this->maybeAutoFault($machine, $severity);
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
                'message' => $this->buildMessage($machine, $parameter, $value, $severity),
            ]);

            $this->recordEvent($alert, 'raised', null, null, 'open', $value);
            $this->notify($alert);
            $this->maybeAutoFault($machine, $severity);
        }

        return $worst;
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

            $this->recordEvent($alert, 'raised', null, null, 'open');
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

        $alerts->each(fn (Alert $alert) => $this->resolve($alert, null, 'device_back_online'));

        return $alerts->count();
    }

    public function getActiveForMachine(Machine $machine, int $limit = 10): Collection
    {
        return $machine->alerts()
            ->whereIn('status', ['open', 'acknowledged'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getQueryForDataTable(array $filters): Builder
    {
        $query = Alert::with(['machine', 'acknowledgedBy', 'resolvedBy']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['severity']) && $filters['severity'] !== 'all') {
            $query->where('severity', $filters['severity']);
        }

        if (! empty($filters['machine_id']) && $filters['machine_id'] !== 'all') {
            $query->where('machine_id', $filters['machine_id']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
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
        $previousStatus = $alert->status;

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);

        $this->recordEvent($alert, 'acknowledged', $user, $previousStatus, 'acknowledged');

        return $alert->fresh();
    }

    public function resolve(Alert $alert, ?User $by = null, string $resolution = 'manual', mixed $value = null): Alert
    {
        $previousStatus = $alert->status;

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $by?->id,
            'resolution' => $resolution,
        ]);

        $action = match ($resolution) {
            'auto' => 'auto_resolved',
            'work_order' => 'resolved_by_work_order',
            'device_back_online' => 'device_back_online',
            default => 'resolved',
        };

        $this->recordEvent($alert, $action, $by, $previousStatus, 'resolved', $value);

        return $alert->fresh();
    }

    public function getTrailForMachine(Machine $machine, int $limit = 20): Collection
    {
        return AlertEvent::query()
            ->whereHas('alert', fn (Builder $query) => $query->where('machine_id', $machine->id))
            ->with(['user', 'alert'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Append one entry to the alert's trail and push it to browsers. Every state change in this
     * class goes through here, so nothing can change an alert without being recorded and shown live.
     * $user null means the system acted (a device reading, the scheduler).
     */
    private function recordEvent(Alert $alert, string $action, ?User $user, ?string $from, ?string $to, mixed $value = null): AlertEvent
    {
        $event = AlertEvent::create([
            'alert_id' => $alert->id,
            'user_id' => $user?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'severity' => $alert->severity,
            'value' => is_numeric($value) ? $value : null,
        ]);

        $event->setRelation('alert', $alert->loadMissing('machine'))->setRelation('user', $user);

        $this->broadcaster->dispatch(new AlertChanged($event));

        return $event;
    }

    private function buildMessage(Machine $machine, MachineParameter $parameter, mixed $value, string $severity): string
    {
        $reading = $parameter->unit ? "{$value} {$parameter->unit}" : $value;

        return "{$parameter->label} on {$machine->name} reached {$reading} ({$severity} level).";
    }

    /**
     * Absolute timestamp, never diffForHumans() — this text is stored and would go stale.
     */
    private function buildOfflineMessage(Machine $machine, int $minutes): string
    {
        return "{$machine->name} has not sent a sensor reading since "
            .$machine->last_seen_at->format('M d, Y H:i')
            ." (offline for more than {$minutes} minutes).";
    }

    /**
     * Admins and managers get alert notifications — the roles with `alerts.resolve`
     * and oversight responsibility across machines (see docs/06-security.md) — plus every
     * member of the machine's assignment group, plus any extra addresses configured in
     * Settings' alert_emails, for recipients who don't hold either role (e.g. an external
     * contractor or a shared ops inbox). Nobody is notified twice for one alert: a user who
     * is both a manager and in the group, or whose address is also in alert_emails, gets one.
     */
    private function notify(Alert $alert): void
    {
        $recipients = User::role(['admin', 'manager'])->get()
            ->merge($alert->machine?->assignmentGroup?->users ?? [])
            ->unique('id')
            ->values();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new MachineAlertNotification($alert));
        }

        $alreadyEmailed = $recipients->pluck('email')->map(fn ($email) => strtolower((string) $email));

        foreach ($this->settings->get('alert_emails', []) as $email) {
            if ($alreadyEmailed->contains(strtolower($email))) {
                continue;
            }

            Notification::route('mail', $email)->notify(new MachineAlertNotification($alert));
        }
    }

    /**
     * When enabled in Settings, a newly-raised or newly-escalated critical alert flips the
     * machine to 'fault' status — but only from 'active', so a machine an operator has
     * already set to 'maintenance'/'inactive' isn't silently overridden.
     */
    private function maybeAutoFault(Machine $machine, string $severity): void
    {
        if ($severity !== 'critical' || ! $this->settings->get('auto_fault_on_critical', false)) {
            return;
        }

        if ($machine->status === 'active') {
            $machine->update(['status' => 'fault']);
        }
    }
}
