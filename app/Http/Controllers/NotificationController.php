<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Marks the current user's own database notifications (see MachineAlertNotification) as read
 * for the header bell. A thin wrapper over Laravel's own Notifiable API — there's no domain
 * rule to justify an interface/service here (see docs/08-frontend.md).
 */
class NotificationController extends Controller
{
    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;

        // An alert opens its trail, a work order its page; anything else falls back to the machine.
        $target = match (true) {
            isset($data['alert_id']) => route('alerts.show', $data['alert_id']),
            isset($data['work_order_id']) && auth()->user()->can('work-orders.view') => route('work-orders.show', $data['work_order_id']),
            isset($data['machine_id']) && auth()->user()->can('machines.view') => route('machines.show', $data['machine_id']),
            default => route('alerts.index'),
        };

        return redirect($target);
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
