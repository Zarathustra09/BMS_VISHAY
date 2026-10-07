<?php

use App\Models\Machine;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Live sensor readings for one machine (App\Events\MachineReadingRecorded) - the same
// permission that lets a user open the machine's page at all.
Broadcast::channel('machines.{machine}', function ($user, Machine $machine) {
    return $user->can('machines.view');
});
