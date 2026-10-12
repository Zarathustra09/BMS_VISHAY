<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder)
    {
        $this->afterCommit();
    }

    /**
     * Email and the live push wait for the queue worker (a slow or unreachable mail server must
     * never hold up a device's request), retried with a back-off if the mail server or Reverb
     * hiccups. The bell row is written immediately instead: it is only a database insert, and
     * waiting for the worker would make it appear late, or never if no worker is running.
     */
    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    /**
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $priority = ucfirst($this->workOrder->priority);

        return (new MailMessage)
            ->subject("[{$priority}] Work order assigned: {$this->workOrder->title}")
            ->greeting("You've been assigned a work order")
            ->line("{$this->workOrder->code} — {$this->workOrder->title}")
            ->line("Machine: {$this->workOrder->machine->name}")
            ->when($this->workOrder->due_date, fn ($mail) => $mail->line("Due: {$this->workOrder->due_date->format('M d, Y')}"))
            ->action('View Work Order', route('work-orders.show', $this->workOrder))
            ->line('You are receiving this because this work order was assigned to you.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'work_order',
            'message' => "Work order {$this->workOrder->code} assigned to you: {$this->workOrder->title}",
            'severity' => 'info',
            'work_order_id' => $this->workOrder->id,
            'code' => $this->workOrder->code,
            'title' => $this->workOrder->title,
            'machine_id' => $this->workOrder->machine_id,
            'machine_name' => $this->workOrder->machine->name,
            'priority' => $this->workOrder->priority,
        ];
    }
}
