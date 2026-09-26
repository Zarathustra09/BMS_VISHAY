<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MachineAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Alert $alert) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $machine = $this->alert->machine;
        $level = $this->alert->severity === 'critical' ? 'Critical' : 'Warning';

        return (new MailMessage)
            ->subject("[{$level}] {$machine->name} — {$this->alertTitle()}")
            ->greeting("{$level} alert on {$machine->name}")
            ->line($this->alert->message)
            ->action('View Alerts', route('alerts.index'))
            ->line('You are receiving this because your role can manage alerts for this machine.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'machine_id' => $this->alert->machine_id,
            'machine_name' => $this->alert->machine->name,
            'metric' => $this->alert->metric,
            'value' => $this->alert->value,
            'severity' => $this->alert->severity,
            'message' => $this->alert->message,
        ];
    }

    private function alertTitle(): string
    {
        return $this->alert->metric
            ? ucfirst(str_replace('_', ' ', $this->alert->metric)) . ' out of range'
            : 'Device offline';
    }
}
