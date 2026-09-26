<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registered first deliberately: schedule:run executes due events sequentially in
// registration order, and app:process-queue can hold the process for up to ~50s.
// runInBackground() is intentionally not used — it shells out with POSIX syntax and
// misbehaves under Windows Task Scheduler, a supported deployment target here (see
// docs/09-setup-and-operations.md).
Schedule::command('app:check-offline-devices')->everyMinute()->withoutOverlapping();
Schedule::command('app:process-queue')->everyMinute()->withoutOverlapping();
