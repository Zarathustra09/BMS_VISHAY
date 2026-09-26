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

Neither is currently registered in [routes/console.php](../routes/console.php)'s scheduler — that file only has the default `inspire` command. Phase 6 will add a `Schedule::command('app:process-queue')->everyMinute()` entry alongside the offline-device-check command.

## Scheduler

Not yet wired up (see above) — `routes/console.php` has no `Schedule::` calls yet. Once Phase 5/6 add scheduled commands (alert evaluation, offline detection, preventive work orders), production deployments need a single cron entry:

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

For local development, `php artisan schedule:work` runs the scheduler in the foreground without needing cron/Task Scheduler.

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
