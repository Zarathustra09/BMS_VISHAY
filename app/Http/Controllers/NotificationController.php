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

        return redirect($notification->data['machine_id'] ?? null
            ? route('machines.show', $notification->data['machine_id'])
            : route('alerts.index'));
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
