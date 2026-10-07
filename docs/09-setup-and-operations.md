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
| `APP_EDITION` | `full` or `lite` — see [Editions & module toggles](#editions--module-toggles) below | `full` |
| `MODULE_BUILDINGS` / `MODULE_WORK_ORDERS` / `MODULE_MAINTENANCE` / `MODULE_REPORTS` | Force one module on or off regardless of edition | follows `APP_EDITION` |
| `QUEUE_CONNECTION` | Queue driver | `database` |
| `BROADCAST_CONNECTION` | `reverb` for live updates; `null`/`log` turns them off (the machine page just polls) | `reverb` in `.env.example` |
| `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` | This install's Reverb credentials — make up your own random values; nothing is registered with anyone | placeholders |
| `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME` | Where **Laravel itself** reaches Reverb to publish — same PC, so `127.0.0.1` / `8080` / `http` | see `.env.example` |
| `REVERB_SERVER_HOST` / `REVERB_SERVER_PORT` | What the Reverb process listens on — `0.0.0.0` so browsers on the LAN can connect | `0.0.0.0` / `8080` |
| `VITE_REVERB_HOST` | **Leave blank**: the browser then connects to the host the page was opened from, so the build works unchanged if the PC's IP changes. Any `VITE_*` change needs `npm run build` | blank |
| `MAIL_MAILER` | Mail driver | `log` (emails write to `storage/logs/laravel.log` instead of sending — fine for local dev, must be changed before Phase 5's email alerts are useful in production) |

## Editions & module toggles

One codebase, two (or more) deployments. Rather than keeping a "lite" copy of the app in a separate repo or a long-lived branch — both of which drift from `master` the moment a machine, user or API fix lands in only one of them — a deployment chooses which modules it exposes through its own `.env`:

```
APP_EDITION=lite
```

| Edition | What's on |
|---|---|
| `full` (default) | Everything. Behaves exactly as the app did before editions existed — no `.env` change needed. |
| `lite` | The always-on core: **Machines, Machine Types, Alerts, Users/Roles/Permissions, Settings, Profile, AI chat, and the sensor API** (`POST /api/machine-logs`). **Buildings & Floors, Work Orders, Maintenance and Reports are switched off.** |

Any single module can still be forced independently of the edition — e.g. a lite install that also wants Buildings is `APP_EDITION=lite` plus `MODULE_BUILDINGS=true`. The switchable modules are the cases of `App\Enums\AppModule` (`buildings`, `work-orders`, `maintenance`, `reports`); the core isn't listed there because it can't be switched off. `config/modules.php` is where the env values are read; run `php artisan config:clear` after changing them if config is cached.

**Nothing is deleted.** Every migration still runs and every table still exists in a lite database — `machines.building_id`/`floor_id` are simply left null, and switching a module back on later restores it with its data intact. Disabling a module changes what the app *shows and accepts*, not what it stores.

**What "off" means for a module** (see [02-architecture.md](02-architecture.md#editions--module-toggles-two-layer-enforcement) for how it's enforced):
- Its pages and endpoints answer **404**, for every role including admin.
- Its sidebar entries, dashboard cards, quick actions and "Create Work Order" buttons disappear.
- The machine form drops its Building/Floor selects, and the machines list its Building filter/columns; a Building/Floor value posted anyway is ignored, and a machine's existing location is left untouched on edit.
- Its permissions (`buildings.*`, `floors.*`, `work-orders.*`, `maintenance.view`, `reports.view`) are denied at the Gate and hidden from the role matrix and Permissions page — but kept in the database, and kept on any role that holds them when that role is saved from the (now shorter) matrix.
- `app:generate-preventive-work-orders` is skipped by the scheduler when Work Orders is off. Settings → Preventive Lead Time is hidden (its value is still carried through unchanged, since the Settings form requires it).

**Deploying two editions** is the same `master` checked out twice (or built into two images) with a different `.env` each — typically a separate database per deployment too, since the two have different users and data. Fixes to machines, users or the API land in both on the next deploy of each.

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
Schedule::command('app:generate-preventive-work-orders')->dailyAt('01:00')->withoutOverlapping()
    ->when(fn () => AppModule::WorkOrders->isEnabled());
Schedule::command('model:prune', ['--model' => [MachineLog::class]])->daily();
```

(The `->when(...)` on the preventive-work-order entry is what skips it in a deployment with Work Orders switched off — see [Editions & module toggles](#editions--module-toggles). The entry still shows up in `schedule:list` either way.)

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

## Live updates (Laravel Reverb) and hosting on the plant PC

**What it is.** The machine page's sensor chart, Online/Offline badge and logs table update the moment an ESP32's reading is stored, instead of on a poll. It uses [Laravel Reverb](https://laravel.com/docs/reverb): a free, open-source WebSocket server that is just another PHP process (`php artisan reverb:start`) on the same PC as the app. Nothing is rented or registered — no Pusher, no cloud account, no per-message fees. (The browser side uses the `laravel-echo` and `pusher-js` npm packages; `pusher-js` is only a client library for the Pusher *protocol* that Reverb implements and never contacts Pusher.)

It is deliberately an **add-on**: if Reverb isn't running, readings are still accepted and stored, and the page falls back to polling (the footer under the chart says `Live · instant` when pushed, `Live · every 5s` when polling).

```
ESP32 --HTTP POST--> Apache/Nginx (Laragon) --> Laravel --stores--> database
                                                   |
                                                   +--publishes--> Reverb :8080 --WebSocket--> browsers on the LAN
```

### Setting up the plant PC (Windows, same LAN as the ESP32s)

1. **Fixed IP.** Give the PC a DHCP reservation on the router (or a static IP). The ESP32 firmware posts to `http://<pc-ip>/api/machine-logs` and browsers open `http://<pc-ip>/`; if the address changes, devices go silent.
2. **Web server.** Install [Laragon](https://laragon.org) (free) and let it serve `public/` of this app (Apache or Nginx, PHP 8.2+, MySQL). In Laragon's preferences enable *Run Laragon when Windows starts* and *Start All automatically* — or install its Apache/MySQL as services. Don't use `php artisan serve` for production: it handles one request at a time, so several ESP32s and browsers would queue behind each other.
3. **The app.** `composer install --no-dev --optimize-autoloader`, copy `.env.example` to `.env` and fill it in (`APP_ENV=production`, `APP_DEBUG=false`, database, the `REVERB_*` secrets), `php artisan key:generate`, `php artisan migrate --force`, `php artisan storage:link`, then `npm ci && npm run build`. Make sure there is **no `public/hot` file** (a leftover from `npm run dev` makes pages load assets from a dev server that isn't running).
4. **Always-on processes.** Download [NSSM](https://nssm.cc/download) (free, a single `nssm.exe`), edit the three paths at the top of `deploy\windows\install-services.bat`, and run it **as Administrator**. It installs `BMS-Reverb` (`reverb:start`) and `BMS-Queue` (`queue:work`) as Windows services that start with Windows and restart if they crash, and opens port 8080 on the **Private** network profile only. `deploy\windows\uninstall-services.bat` reverses it. With a real queue worker running, AI chat replies and alert e-mails no longer wait for the per-minute scheduler (which stays in place as a backup).
5. **Scheduler.** Add the Task Scheduler entry from [Scheduler](#scheduler) (every minute) — offline detection and preventive work orders depend on it.
6. **Power.** Windows power options: never sleep or hibernate, and set Windows Update's active hours so restarts don't land mid-shift (the services come back on their own after a reboot).
7. **Check it.** Open a machine page from another PC on the LAN: the footer should read `Live · instant`. Smoke test: post a reading (see [05-device-api.md](05-device-api.md)) and watch the chart move with no refresh.

**After changing code or `.env`:** `php artisan reverb:restart` and `php artisan queue:restart` (the long-running processes only re-read config when restarted), and `npm run build` if any `VITE_*` value changed.

**Capacity.** One Reverb process comfortably serves the handful of dashboards a plant keeps open; Reverb's horizontal scaling (Redis) is not needed here.

**Cost.** None beyond the PC: Reverb, NSSM, Laragon, Laravel Echo and `pusher-js` are all free and open source.

## Settings

The admin-only Settings page (`/settings`, `settings.view`/`settings.update`) holds operational configuration that used to be hardcoded: company name, extra alert-notification emails, the offline-detection timeout (`offline_timeout_minutes`, read by `app:check-offline-devices` when `--minutes` isn't passed), the preventive-maintenance lead time (`preventive_lead_days`, read by `app:generate-preventive-work-orders` when `--lead-days` isn't passed), an auto-fault-on-critical toggle, default alert thresholds applied to newly-created machines, and the machine-log retention window (`log_retention_days`, read by `MachineLog::prunable()`). Backed by `SettingInterface`/`SettingService`, cached forever and invalidated on every save — see [02-architecture.md](02-architecture.md#container-binding-registry). A change takes effect on the *next* run of the affected command/query; nothing needs restarting.

## AI agent tooling (Laravel Boost)

[laravel/boost](https://laravel.com/framework/docs/boost) is a dev-only dependency that gives AI coding agents (currently just Claude Code) Laravel-ecosystem guidelines, on-demand skills, and an MCP server for inspecting this app directly (schema, queries, logs, installed package versions) instead of guessing.

```bash
php artisan boost:mcp     # starts the MCP server manually (usually launched by the agent via .mcp.json)
php artisan boost:update  # refreshes guidelines/skills after a package upgrade (also runs on every `composer update` via post-update-cmd)
```

Generated, machine-regenerated files are gitignored (`.mcp.json`, `boost.json`) and re-created by `boost:install`/`boost:update` — don't hand-edit them. `AGENTS.md` (Boost's guidelines file for this app) and `.claude/skills/**` **are** committed, same as `docs/`.

App-specific conventions ([10-conventions.md](10-conventions.md)) are additionally mirrored into `.ai/rules/*` so an agent consults them automatically per file glob — see that doc's "AI agent rules" section for how a rule gets recorded.

Set `BOOST_RULES_ENABLED=false` to disable the `.ai/rules` feature entirely (removes the `record-rule` MCP tool).

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
| The Live Sensor Readings chart on a machine page is empty even though `machine_logs` has rows for it | The chart only offers the machine's **number parameters** (edit page → Sensor Parameters) — a machine with none shows "no numeric parameters to chart". If it has some, the selected one has no `readings.<key>` values in the chosen range — the empty state says when it last reported and offers a "Show it" button; **Auto** (the default range) opens on the smallest window that contains the latest reading, so only a manual range can come up empty. If the parameter *never* reports, check the logs' `readings` JSON actually uses the parameter's `key` |
| The chart footer says `Live · every 5s` instead of `Live · instant` | The browser couldn't open the WebSocket, so it is polling (nothing is lost). Check, on the PC: `sc query BMS-Reverb` is RUNNING; `storage\logs\reverb-service.log`; that port 8080 is open on the Private network profile (`netsh advfirewall firewall show rule name="BMS Reverb (private network)"`); that `VITE_REVERB_APP_KEY` was present when `npm run build` ran (without it Echo isn't created at all); and that `public/hot` doesn't exist in production |
| `laravel.log` shows `Live broadcast failed (is Reverb running?)` | Reverb isn't reachable from Laravel (`REVERB_HOST`/`REVERB_PORT` wrong, or the service is stopped). Readings are unaffected; after the first failure Laravel stops trying for 30s, so expect at most a couple of these lines per minute while it's down |
| A device gets `422` for a reading it used to send fine | Since Phase 10 sensor values go inside a `readings` object and must match the machine's parameter keys — the response message lists the accepted keys. Check the machine's API Integration card |
| A machine due for maintenance never gets a preventive work order | Either the scheduler isn't running (see Scheduler above), the machine has no `next_maintenance_date` set (nothing to compare against), the machine's `status` is `inactive` (deliberately excluded), or it already has an open preventive work order — `app:generate-preventive-work-orders` only ever creates one at a time per machine |
| The maintenance dashboard shows a different overdue count than expected | It excludes `inactive` machines from every count (matching the work-order generator's own exclusion) — an inactive machine past its `next_maintenance_date` won't appear as overdue, since it's deliberately out of service |
