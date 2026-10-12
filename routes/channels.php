<?php

use App\Models\Machine;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Every alert change (raised, escalated, acknowledged, resolved) - the Alerts page, the header bell,
// the dashboard and the machine page all listen here (App\Events\AlertChanged). Same permission that
// lets a user open the Alerts page at all.
Broadcast::channel('alerts', function ($user) {
    return $user->can('alerts.view');
});

// Live sensor readings for one machine (App\Events\MachineReadingRecorded) - the same
// permission that lets a user open the machine's page at all.
Broadcast::channel('machines.{machine}', function ($user, Machine $machine) {
    return $user->can('machines.view');
});
