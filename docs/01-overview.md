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
| Frontend | Blade templates, Tailwind CSS v4 + DaisyUI v5 (via CDN), Alpine.js (via CDN) |
| Data tables | `yajra/laravel-datatables` (server-side PHP), jQuery + DataTables JS (via CDN) — high-volume list pages (buildings, machines, machine logs) |
| Build tooling | Vite |
| AI providers | OpenAI (`gpt-4o`, Responses API) and Google Gemini (`gemini-2.0-flash-exp`), switchable via `AI_PROVIDER` |

## Module status

| Module | Status | Notes |
|---|---|---|
| Buildings | ✅ Done | Full CRUD, image upload, cascading delete of floors/machines; list is a server-side DataTable |
| Floors | ✅ Done | Full CRUD (JSON API style, used by the machine create/edit forms) |
| Machines | ✅ Done | Full CRUD, filters, stats, device API key management; list is a server-side DataTable |
| Machine Logs / sensor ingestion | ✅ Done | `POST /api/machine-logs`, authenticated via per-machine device key; the log history on `machines/show` is a server-side DataTable (severity/type/date filters) — the highest-volume table in the app |
| AI Insights (chat) | ✅ Done | Queued job + cache-polling UI, OpenAI/Gemini switchable |
| Roles & permissions | ✅ Done | `admin`/`manager`/`technician`/`viewer`, CRUD-granular permissions (`{module}.view/create/update/delete`) across buildings, floors, machines, users, plus the special `machines.view-device-key`. More modules gain their own set as they're built (work orders, alerts, reports, settings). |
| Users management page | ✅ Done | Full CRUD (create, edit name/email/role, reset password, delete), self-delete blocked, gated by permission |
| User profile (self-service) | ✅ Done | Any logged-in user can update their own name/email and change their own password (requires current password) |
| Roles management page | ✅ Done | Full CRUD: create custom roles, edit any non-`admin` role's permissions via a checkbox matrix, delete an unused role. `admin` is protected (can't be renamed/deleted/edited); can't delete a role that still has users |
| Permissions page | ✅ Done | Read-only list of every permission grouped by module, showing which roles hold each one — new permissions are still added in code/seeder, not the UI |
| Sensor thresholds & alerts | 🔴 Planned | Phase 5 |
| Offline device detection | 🔴 Planned | Phase 6 |
| Work Orders | 🔴 Planned | `work_orders` table exists but is empty (`id` + timestamps only); Phase 7 |
| Maintenance dashboard | 🔴 Planned | View is a static mockup; Phase 8 |
| Reports & exports | 🔴 Planned | View is a static mockup; Phase 9 |
| Home dashboard | 🟡 Partial | Real layout, but metrics are hardcoded placeholders; Phase 9 |
| Settings | 🔴 Planned | View is a static mockup; Phase 10 |
| Audit log | 🔴 Planned | Phase 10 |

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
| **Alert** *(planned, Phase 5)* | A notification raised when a sensor reading crosses a configured threshold, or a device stops reporting. |
| **Work Order** *(planned, Phase 7)* | A maintenance task, either created manually or generated automatically (e.g. from an alert or a due maintenance schedule), assigned to a technician. |
