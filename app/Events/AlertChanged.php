<?php

namespace App\Events;

use App\Models\AlertEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Something just happened to an alert - raised, escalated, acknowledged, resolved - pushed over
 * Laravel Reverb so the Alerts page, the header bell, the dashboard and the machine page can update
 * the moment it happens instead of on the next reload.
 *
 * ShouldBroadcastNow, like MachineReadingRecorded: queued broadcasts would wait for the queue
 * worker, which is slower than the page refresh this replaces. Always dispatched through
 * SafeBroadcaster, so a stopped Reverb can never fail the device request or the button click.
 *
 * Two private channels: `alerts` (every page that lists alerts) and `machines.{id}` (the machine
 * page, which already subscribes there for live readings). Both need `alerts.view` / `machines.view`
 * - see routes/channels.php. The payload is deliberately small: the pages re-fetch what they need.
 */
class AlertChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AlertEvent $event) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('alerts'),
            new PrivateChannel("machines.{$this->event->alert->machine_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'alert.changed';
    }

    /**
     * @return array{alert_id: int, machine_id: int, machine_name: ?string, metric: ?string, severity: string, status: string, message: string, action: string, label: string, actor: string, at: string}
     */
    public function broadcastWith(): array
    {
        $alert = $this->event->alert;

        return [
            'alert_id' => $alert->id,
            'machine_id' => $alert->machine_id,
            'machine_name' => $alert->machine?->name,
            'metric' => $alert->metric,
            'severity' => $alert->severity,
            'status' => $alert->status,
            'message' => $alert->message,
            'action' => $this->event->action,
            'label' => $this->event->label(),
            'actor' => $this->event->actorName(),
            'at' => $this->event->created_at->toIso8601String(),
        ];
    }
}
