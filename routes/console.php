<?php

use App\Enums\AppModule;
use App\Models\MachineLog;
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

// Preventive work orders only need to be (re)checked once a day — a machine's
// next_maintenance_date doesn't move minute to minute, and the generator's own dedupe
// (an existing open preventive work order blocks a new one) makes a missed day harmless.
// Skipped entirely when Work Orders is switched off (config/modules.php) — otherwise a lite
// edition would quietly keep creating work orders nobody can see.
Schedule::command('app:generate-preventive-work-orders')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->when(fn () => AppModule::WorkOrders->isEnabled());

// Deletes machine_logs older than Settings' log_retention_days (MachineLog::prunable()).
// --model is explicit rather than letting model:prune auto-discover every Prunable model in
// app/Models, so adding a second Prunable model later can't silently start getting pruned
// by this same schedule entry without a deliberate change here.
Schedule::command('model:prune', ['--model' => [MachineLog::class]])->daily();
