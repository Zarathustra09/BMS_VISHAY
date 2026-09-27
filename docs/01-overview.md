# Overview

## What this is

NexBuild BMS is a Building Management System for manufacturing facilities. It tracks a hierarchy of **Buildings → Floors → Machines**, ingests sensor readings from hardware devices attached to those machines (temperature, humidity, pressure, vibration, power, etc.), and provides an AI assistant that can analyze a machine's log history and answer natural-language questions about it.

The system was built for Vishay's manufacturing site. The temperature sensor integration was validated by Justin against the `POST /api/machine-logs` endpoint before this documentation was written.

## Tech stack

| Layer | Technology |
|---|---|
| Backend framework | Laravel 12 (PHP ^8.2) |
| Auth | Laravel UI (session-based), `laravel/sanctum` ^4.0 (installed, used for `/api/user` only) |
| Authorization | `spatie/laravel-permission` ^6.25 (roles + permissions) |
| Database | MySQL |
| Queue | `database` driver |
| Frontend | Blade templates, Tailwind CSS v4 + DaisyUI v5 (via CDN), Alpine.js (via CDN); light theme `corporate` is the default, dark theme `forest` is opt-in via a header toggle (see [08-frontend.md](08-frontend.md#theming)) |
| Data tables | `yajra/laravel-datatables` (server-side PHP), jQuery + DataTables JS (via CDN) — high-volume list pages (buildings, machines, machine logs, alerts, work orders, maintenance) |
| Build tooling | Vite |
| AI providers | OpenAI (`gpt-4o`, Responses API) and Google Gemini (`gemini-2.0-flash-exp`), switchable via `AI_PROVIDER` |
| Audit log | `spatie/laravel-activitylog` ^4.12 (v5 needs PHP ^8.4, which this app doesn't target) |
| Testing | PHPUnit, `tests/Feature` + `tests/Unit`, SQLite `:memory:` — see [11-testing.md](11-testing.md) |

## Module status

| Module | Status | Notes |
|---|---|---|
| Buildings | ✅ Done | Full CRUD, image upload, cascading delete of floors/machines; list is a server-side DataTable |
| Floors | ✅ Done | Full CRUD (JSON API style, used by the machine create/edit forms) |
| Machines | ✅ Done | Full CRUD, filters, stats, device API key management; list is a server-side DataTable |
| Machine Logs / sensor ingestion | ✅ Done | `POST /api/machine-logs`, authenticated via per-machine device key; the log history on `machines/show` is a server-side DataTable (severity/type/date filters) — the highest-volume table in the app |
| AI Insights (chat) | ✅ Done | Queued job + cache-polling UI, OpenAI/Gemini switchable |
| Roles & permissions | ✅ Done | `admin`/`manager`/`technician`/`viewer`, CRUD-granular permissions (`{module}.view/create/update/delete`) across buildings, floors, machines, users, plus the special `machines.view-device-key`. More modules gain their own set as they're built (work orders, alerts, reports, settings). |
| Users management page | ✅ Done | Full CRUD (create, edit name/email/role, reset password, delete), self-delete blocked, gated by permission; list is a server-side DataTable with shared, JS-populated modals |
| User profile (self-service) | ✅ Done | Any logged-in user can update their own name/email and change their own password (requires current password) |
| Roles management page | ✅ Done | Full CRUD: create custom roles, edit any non-`admin` role's permissions via a checkbox matrix, delete an unused role, **assign existing users to a role directly from the list** (moves them off their current role — one role per user). `admin` is protected (can't be renamed/deleted/edited); can't delete a role that still has users; can't reassign your own account via the picker. List is a client-side DataTable (small, fixed-size). No "remove from role" yet — reassign the user to a different role instead. |
| Permissions page | ✅ Done | Read-only list of every permission grouped by module, showing which roles hold each one — new permissions are still added in code/seeder, not the UI |
| Sensor thresholds & alerts | ✅ Done | Per-metric warning/critical bounds on the machine edit page; `AlertService::evaluate()` raises/escalates/auto-resolves alerts against every reading; in-app (header bell) + email notifications to admin/manager; alerts list is a server-side DataTable |
| Offline device detection | ✅ Done | Scheduled `app:check-offline-devices` (every minute) raises an "offline" alert when a machine goes quiet for 10+ minutes; auto-resolves on the next reading; Online/Offline badge + live sensor chart on the machine page |
| Work Orders | ✅ Done | Full CRUD (create/edit/show/delete, soft deletes) with a status workflow (pending → in_progress → completed/cancelled); can originate from a machine page or an open alert; completing an alert-linked work order resolves the alert, completing a preventive one advances the machine's maintenance schedule; assignee gets an in-app + email notification; list is a server-side DataTable; a technician may only update the status of a work order assigned to them |
| Maintenance dashboard | ✅ Done | Overdue/due-in-7-days/due-in-30-days/completed-this-month stats, a per-building breakdown, and a server-side DataTable of machines due for maintenance; a daily `app:generate-preventive-work-orders` command auto-creates a preventive work order for machines approaching their `next_maintenance_date`, deduped so it never double-creates |
| Reports & exports | ✅ Done | Four report types (sensor summary, alert history, work order summary, maintenance compliance) with date-range + building filters, an on-page stats/preview view, and a streamed CSV download per report |
| Home dashboard | ✅ Done | Every stat, chart, and list is real (`DashboardInterface`): equipment availability, active/pending/open counts, a 7-day alerts trend, per-building machine health, and the 5 most recent alerts/work orders. Fabricated content with no real data source (revenue, "production units", facility occupancy, server/backup status, "Manage Tenants") was removed rather than kept as placeholders. |
| Settings | ✅ Done | Admin-only `settings/index` page backed by a cached `SettingInterface`/`SettingService`: company name, extra alert emails, offline timeout, preventive lead days, auto-fault-on-critical toggle, default alert thresholds for new machines, and log retention days. `CheckOfflineDevices` and `app:generate-preventive-work-orders` read their defaults from Settings when `--minutes`/lead days aren't passed on the command line. |
| Audit log | ✅ Done | `spatie/laravel-activitylog` on `Machine`, `Building`, `WorkOrder` (full fillable, dirty-only) and `Alert` (narrowed to `status`/`severity` only, to avoid one log entry per sensor reading). A combined Activity feed on the machine page shows changes to the machine plus its work orders and alerts. |
| Data retention | ✅ Done | `MachineLog` is `Prunable`, keeping rows newer than the `log_retention_days` setting (365 by default); a daily `model:prune --model=App\Models\MachineLog` schedule entry runs the cleanup. |

See [CHANGELOG.md](CHANGELOG.md) for what's shipped so far and the plan document for what's next.

## Glossary

| Term | Meaning |
|---|---|
| **Building** | A physical facility (e.g. a factory or warehouse). Has floors and machines. |
| **Floor** | A floor within a building. Belongs to a building, has machines. |
| **Machine** | A piece of industrial equipment (HVAC, generator, elevator, pump, cooling tower, etc.) tracked by the system. Optionally assigned to a building/floor. |
| **Device** | The physical hardware (e.g. a temperature sensor module) attached to a machine that reports sensor readings over HTTP. |
| **Device key** | A per-machine secret (`X-Device-Key` header) that authenticates a device's POST requests to the sensor ingestion API. Stored as a SHA-256 hash for lookup, plus an encrypted copy so it can be viewed again by an authorized user. |
| **Machine log** | A single event or sensor reading recorded against a machine: a status change, a maintenance action, an alarm, or a raw sensor reading. |
| **Severity** | One of `info`, `warning`, `error`, `critical` — set on a machine log to indicate how serious it is. |
| **Alert** | A record raised by `AlertService` when a sensor reading crosses a configured threshold, or when a device stops reporting — has a severity (warning/critical) and a status (open/acknowledged/resolved), and triggers an in-app + email notification to admins/managers. |
| **Sensor threshold** | Per-machine, per-metric warning/critical bounds (e.g. `temperature.critical_max`), configured on the machine edit page — see [03-data-model.md](03-data-model.md#sensor_thresholds). |
| **Work Order** | A maintenance or repair task against a machine — created manually (from the Work Orders page, a machine page, or an open alert), and from Phase 8 also generated automatically from a due maintenance schedule. Has a type (preventive/corrective/emergency/inspection), a priority, and a status (pending/in_progress/completed/cancelled), and is optionally assigned to a technician. |
| **Setting** | A key-value row in the `settings` table (e.g. `offline_timeout_minutes`), read/written through the cached `SettingInterface` — see [03-data-model.md](03-data-model.md#settings). |
| **Activity log** | A `spatie/laravel-activitylog` entry recording a field-level change to a model (who changed what, from what, to what) — see [02-architecture.md](02-architecture.md#audit-logging). |
