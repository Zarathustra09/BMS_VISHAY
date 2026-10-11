<?php

namespace App\Notifications;

use App\Models\Alert;
use App\Models\User;
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
            ->action('View Machine', route('machines.show', $machine))
            ->line($this->reason($notifiable, $machine));
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

    /**
     * Why this recipient is being told, in their terms: a member of the machine's assignment
     * group, an admin/manager, or an address on the Settings alert list.
     */
    private function reason(object $notifiable, $machine): string
    {
        $group = $machine->assignmentGroup;

        if ($group && $notifiable instanceof User && $group->users->contains('id', $notifiable->id)) {
            return "You are receiving this because you are in the \"{$group->name}\" group, which this machine is assigned to.";
        }

        if ($notifiable instanceof User) {
            return 'You are receiving this because your role can manage alerts for this machine.';
        }

        return 'You are receiving this because your address is on the alert recipients list.';
    }

    private function alertTitle(): string
    {
        return $this->alert->metric
            ? ucfirst(str_replace('_', ' ', $this->alert->metric)).' out of range'
            : 'Device offline';
    }
}
