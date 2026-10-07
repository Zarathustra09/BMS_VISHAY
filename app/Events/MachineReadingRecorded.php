<?php

namespace App\Events;

use App\Models\MachineLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A device's sensor reading was just stored — pushed to every browser watching that machine
 * (machines/show) over Laravel Reverb, so the chart updates the moment the reading arrives
 * instead of on its next poll.
 *
 * ShouldBroadcastNow, not ShouldBroadcast: queued broadcasts would wait for the queue worker
 * (in this deployment, once a minute from the scheduler), which is slower than the polling it
 * replaces. The payload is deliberately small and carries the time labels already formatted in
 * the server's timezone, the same way MachineLogService formats the chart's own points.
 */
class MachineReadingRecorded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MachineLog $log) {}

    /**
     * Private, so only a user passing the channel check in routes/channels.php (machines.view)
     * can subscribe.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("machines.{$this->log->machine_id}")];
    }

    public function broadcastAs(): string
    {
        return 'reading.recorded';
    }

    /**
     * `labels` mirrors the three label formats the chart uses (see
     * MachineLogService::buildPoints()): the chart picks one by the span its data covers.
     *
     * @return array{log_id: int, t: int, readings: array<string, mixed>, labels: array{time: string, hour: string, date: string}}
     */
    public function broadcastWith(): array
    {
        $at = $this->log->logged_at;

        return [
            'log_id' => $this->log->id,
            't' => $at->getTimestamp(),
            'readings' => $this->log->readings ?? [],
            'labels' => [
                'time' => $at->format('H:i:s'),
                'hour' => $at->format('H:i'),
                'date' => $at->format('M j H:i'),
            ],
        ];
    }
}
