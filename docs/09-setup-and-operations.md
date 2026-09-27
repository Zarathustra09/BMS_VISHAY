# Setup & Operations

## Local setup

```bash
composer install
npm install

cp .env.example .env         # if .env doesn't already exist
php artisan key:generate

# Edit .env: set DB_* to a running MySQL instance, and DB_DATABASE (default: bms_vishay)

php artisan migrate
php artisan db:seed          # creates roles/permissions + the default admin user + sample buildings/floors

npm run build                # or `npm run dev` while actively working on resources/js or resources/css
php artisan serve
```

`composer.json` also defines a `composer run dev` script that runs the PHP server, a queue listener, and Vite concurrently — convenient for local development:

```bash
composer run dev
```

## Default accounts

After `php artisan db:seed`:

| Email | Password | Role |
|---|---|---|
| `test@example.com` | `password` | `admin` |

Change this password (or delete the account and create a real one) before any non-local deployment.

## Environment variables

See `.env.example` for the full list. Application-specific ones worth knowing:

| Variable | Purpose | Default |
|---|---|---|
| `AI_PROVIDER` | `openai` or `gemini` | `openai` |
| `OPENAI_API_KEY` / `GEMINI_API_KEY` | AI provider credentials | placeholder — must be set for AI chat to work |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `MAIL_MAILER` | Mail driver | `log` (emails write to `storage/logs/laravel.log` instead of sending — fine for local dev, must be changed before Phase 5's email alerts are useful in production) |

## Queue worker

AI insight generation ([07-ai-insights.md](07-ai-insights.md)) runs through the `database` queue driver — nothing is processed until a worker is running.

**Local development**: `php artisan queue:listen --tries=1` (already included in `composer run dev`), or `php artisan queue:work`.

**Custom commands** ([app/Console/Commands](../app/Console/Commands)):

- `php artisan app:process-queue` — checks `Queue::size()` first and skips entirely if empty (logs to the `queue_processing` channel), otherwise runs `queue:work --stop-when-empty --max-time=50 --timeout=60`. Designed to be triggered on a schedule (e.g. every minute) without leaving a worker running permanently.
- `php artisan app:run-queue-worker` — simpler variant, always runs `queue:work --stop-when-empty` and logs start/finish.
- `php artisan app:check-offline-devices {--minutes=}` — raises an `offline` alert for any machine whose `last_seen_at` is older than `--minutes` (default 10, see `AlertInterface::OFFLINE_AFTER_MINUTES`). Prints a summary either way, but only writes to `storage/logs/laravel.log` when it actually raises something — see [02-architecture.md](02-architecture.md) for the full flow.
- `php artisan app:generate-preventive-work-orders {--lead-days=}` — creates a `preventive` work order for any machine whose `next_maintenance_date` falls within `--lead-days` (default: the `preventive_lead_days` setting, see [Settings](#settings) below), skipping `inactive` machines and any machine that already has an open preventive work order (so a missed day, or running it twice, never produces a duplicate). The work order is system-generated (`created_by` null) and unassigned unless a technician is added afterward.
- `php artisan model:prune {--model=}` — Laravel's built-in pruning command; `--model=App\Models\MachineLog` (as passed on the scheduler entry below) deletes `machine_logs` rows older than the `log_retention_days` setting (default 365).

`app:process-queue` and `app:check-offline-devices` run every minute; `app:generate-preventive-work-orders` and `model:prune` run once daily — all four are registered on the scheduler (below).

## Scheduler

`routes/console.php` registers four `Schedule::command(...)` entries:

```php
Schedule::command('app:check-offline-devices')->everyMinute()->withoutOverlapping();
Schedule::command('app:process-queue')->everyMinute()->withoutOverlapping();
Schedule::command('app:generate-preventive-work-orders')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('model:prune', ['--model' => [MachineLog::class]])->daily();
```

The offline check is listed **first** among the per-minute entries deliberately — `schedule:run` runs due commands in registration order, and `app:process-queue` can hold the process for up to ~50s, which would otherwise delay the offline check by that long on every run. The preventive-work-order generator only needs to run once a day since `next_maintenance_date` doesn't move minute to minute, and its own dedupe (an existing open preventive work order blocks a new one) makes a missed day harmless — it just catches up the next run. `model:prune` is passed `--model` explicitly (rather than relying on auto-discovery of every `Prunable` model) so it only ever touches `MachineLog`. `withoutOverlapping()` uses a cache-backed lock, which works out of the box here since `CACHE_STORE=database` and the cache table is already migrated. None of the four entries use `runInBackground()` — it shells out with POSIX-style command construction that doesn't work correctly under Windows Task Scheduler, a supported production target for this app.

A single cron/Task Scheduler entry drives all of them:

**Linux / cPanel**:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler)**: create a task that runs every minute:
```
Program: php.exe
Arguments: artisan schedule:run
Start in: C:\path\to\BMS_VISHAY
```

For local development, `php artisan schedule:work` runs the scheduler in the foreground without needing cron/Task Scheduler. Useful commands while developing: `php artisan schedule:list` (shows all four entries and their next run time) and `php artisan schedule:test` (runs a scheduled command on demand, without waiting for its next due time).

## Settings

The admin-only Settings page (`/settings`, `settings.view`/`settings.update`) holds operational configuration that used to be hardcoded: company name, extra alert-notification emails, the offline-detection timeout (`offline_timeout_minutes`, read by `app:check-offline-devices` when `--minutes` isn't passed), the preventive-maintenance lead time (`preventive_lead_days`, read by `app:generate-preventive-work-orders` when `--lead-days` isn't passed), an auto-fault-on-critical toggle, default alert thresholds applied to newly-created machines, and the machine-log retention window (`log_retention_days`, read by `MachineLog::prunable()`). Backed by `SettingInterface`/`SettingService`, cached forever and invalidated on every save — see [02-architecture.md](02-architecture.md#container-binding-registry). A change takes effect on the *next* run of the affected command/query; nothing needs restarting.

## Logs

- Application logs: `storage/logs/laravel.log` (default `single` channel). This is also where mail bodies land while `MAIL_MAILER=log`.
- A dedicated `queue_processing` log channel is referenced by `app:process-queue` — add it to [config/logging.php](../config/logging.php) if you want it split into its own file (`storage/logs/queue_processing.log`) instead of falling back to the default channel.

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| AI chat stays "processing..." forever | No queue worker running — start `php artisan queue:work` (or `queue:listen`) |
| AI chat returns a generic error message | Check `storage/logs/laravel.log` for `Gemini API...`/`OpenAI...` log entries — usually a missing/invalid API key |
| Sensor POST returns 401 | Device key missing, wrong, or regenerated since the device last got it — see [05-device-api.md](05-device-api.md) |
| Sensor POST returns 422 | A field failed validation — check the response body's `errors` object against the field reference in [05-device-api.md](05-device-api.md) |
| `permission:xxx` middleware always 403s for everyone including admin | Run `php artisan db:seed --class=RolesAndPermissionsSeeder`, and if permissions were changed without re-seeding, clear Spatie's cache: `php artisan permission:cache-reset` |
| Device key card missing entirely on a machine page | The logged-in user doesn't have the `machines.view-device-key` permission — this is by design, see [06-security.md](06-security.md) |
| New migration errors about a table already existing | Someone ran `migrate:fresh` or the DB is out of sync — check `php artisan migrate:status` before creating a new migration that touches the same table |
| A list page's table never loads (spinner or blank) | Open devtools → Network and check the `/…/data` ajax request. A 403 means the role lacks the `.view` permission for that module; a 500 usually means a column name mismatch between the JS `columns` array and the DataTable class's `addColumn`/`editColumn` names — see [02-architecture.md](02-architecture.md) for the DataTables pattern |
| A table's columns look squeezed into the left half on first load | The main layout reveals page content ~300ms after load; the shared DataTables assets partial re-adjusts column widths ~400ms after load as a safety net. If it's still off, check the page sets `autoWidth: false` in its `.DataTable({...})` call |
| A machine shows "Offline" on the page but the device is actively sending readings | Either the scheduler isn't running in this environment (`schedule:run`/`schedule:work`/cron/Task Scheduler — see Scheduler above), or check `machines.last_seen_at` directly: it's only stamped by `MachineLogService::store()`, so a reading inserted any other way (a seeder, a manual DB edit) won't update it |
| The Live Sensor Readings chart on a machine page is empty even though `machine_logs` has rows for it | The selected/default metric has no non-null values in the last 7 days for that machine (`getReadingSeries()`'s `availableMetrics()` only offers metrics with data in that window) — check which of the 10 monitorable columns (`AlertInterface::METRICS`) are actually populated for that machine's logs |
| A machine due for maintenance never gets a preventive work order | Either the scheduler isn't running (see Scheduler above), the machine has no `next_maintenance_date` set (nothing to compare against), the machine's `status` is `inactive` (deliberately excluded), or it already has an open preventive work order — `app:generate-preventive-work-orders` only ever creates one at a time per machine |
| The maintenance dashboard shows a different overdue count than expected | It excludes `inactive` machines from every count (matching the work-order generator's own exclusion) — an inactive machine past its `next_maintenance_date` won't appear as overdue, since it's deliberately out of service |
