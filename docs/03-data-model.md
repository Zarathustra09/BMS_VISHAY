# Data Model

## Entity relationship diagram

```mermaid
erDiagram
    BUILDINGS ||--o{ FLOORS : has
    BUILDINGS ||--o{ MACHINES : has
    FLOORS ||--o{ MACHINES : has
    MACHINES ||--o{ MACHINE_LOGS : has
    MACHINES ||--o{ SENSOR_THRESHOLDS : has
    MACHINES ||--o{ ALERTS : has
    MACHINES ||--o{ WORK_ORDERS : has
    MACHINE_LOGS ||--o| ALERTS : "raised by"
    ALERTS ||--o| WORK_ORDERS : "resolved by"
    USERS ||--o{ WORK_ORDERS : "assigned to"
    USERS }o--o{ ROLES : "assigned (Spatie)"
    ROLES }o--o{ PERMISSIONS : "granted (Spatie)"

    BUILDINGS {
        bigint id PK
        string name
        string code
        string type
        string image
        string status
        timestamps timestamps
    }
    FLOORS {
        bigint id PK
        bigint building_id FK
        string name
        int floor_number
        string status
    }
    MACHINES {
        bigint id PK
        string name
        string machine_code UK
        string type
        bigint building_id FK "nullable"
        bigint floor_id FK "nullable"
        enum status
        string device_key "sha256 hash, unique, nullable"
        text device_key_encrypted "nullable"
        timestamp last_seen_at "nullable"
        boolean is_critical
        timestamp deleted_at "soft delete"
    }
    MACHINE_LOGS {
        bigint id PK
        bigint machine_id FK
        enum log_type
        enum severity
        decimal temperature
        decimal humidity
        decimal pressure
        decimal power_consumption
        decimal voltage
        decimal current
        decimal vibration_rms
        timestamp logged_at
    }
    SENSOR_THRESHOLDS {
        bigint id PK
        bigint machine_id FK
        enum metric
        decimal warning_min "nullable"
        decimal warning_max "nullable"
        decimal critical_min "nullable"
        decimal critical_max "nullable"
    }
    ALERTS {
        bigint id PK
        bigint machine_id FK
        bigint machine_log_id FK "nullable"
        enum type "threshold or offline"
        string metric "nullable"
        decimal value "nullable"
        enum severity
        enum status
        text message
        bigint acknowledged_by FK "nullable, users.id"
    }
    WORK_ORDERS {
        bigint id PK
        string code UK
        string title
        bigint machine_id FK
        bigint alert_id FK "nullable"
        enum type "preventive/corrective/emergency/inspection"
        enum priority
        enum status
        bigint assigned_to FK "nullable, users.id"
        bigint created_by FK "nullable, users.id"
        date due_date "nullable"
        timestamp completed_at "nullable"
        timestamp deleted_at "soft delete"
    }
```

`settings` and `activity_log` aren't shown above — neither has a foreign key into this graph (`settings` is a flat key-value store, `activity_log` references other tables only via a polymorphic `subject_type`/`subject_id`, not a real FK) — see their sections below. The `machine_statuses` and `log_types` placeholder tables (empty, `id`+`timestamps` only, never referenced by any query) were dropped in Phase 10.

## Tables

### `buildings`

Migration: [2026_02_18_074259_create_buildings_table.php](../database/migrations/2026_02_18_074259_create_buildings_table.php)

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| name | string | | |
| code | string | ✓ | building code, unique in practice (enforced at controller-validation level, not a DB constraint) |
| type | string | ✓ | one of: Production Facility, Warehouse, Office Building, R&D Center, Distribution Center (enforced by controller validation, not a DB enum) |
| image | string | ✓ | path under `storage/app/public/buildings` |
| address, city, state, country | string | ✓ | |
| year_built | integer | ✓ | |
| emergency_contact_name, emergency_contact_phone | string | ✓ | |
| status | string | ✓ | Operational, Under Construction, Maintenance, Inactive (controller-validated) |
| notes | text | ✓ | |
| timestamps | | | |

**Relationships** ([Building.php](../app/Models/Building.php)): `hasMany(Floor)`, `hasMany(Machine)`.

**Delete behaviour**: `Building::boot()` registers a `deleting` event that calls `$building->floors()->delete()` and `$building->machines()->delete()`. Floors have no soft-deletes, so this is a **hard delete**. Machines use `SoftDeletes`, so this is a **soft delete** (sets `deleted_at`). There is no DB-level `ON DELETE CASCADE` on these — the cascade is application-level, in the model's boot method.

### `floors`

Migration: [2026_02_18_082718_create_floors_table.php](../database/migrations/2026_02_18_082718_create_floors_table.php)

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| building_id | bigint FK → buildings.id | | `onDelete('cascade')` at the DB level |
| name | string | | |
| floor_number | integer | ✓ | |
| description | text | ✓ | |
| status | string | ✓ | active/inactive/maintenance (controller-validated) |
| timestamps | | | |

**Relationships** ([Floor.php](../app/Models/Floor.php)): `belongsTo(Building)`, `hasMany(Machine)`. No `SoftDeletes`.

### `machines`

Migrations: [2026_02_19_142824_create_machines_table.php](../database/migrations/2026_02_19_142824_create_machines_table.php), [add_device_key_to_machines_table](../database/migrations/2026_09_26_063758_add_device_key_to_machines_table.php), [add_device_key_encrypted_to_machines_table](../database/migrations/2026_09_26_070637_add_device_key_encrypted_to_machines_table.php)

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| name | string | | |
| machine_code | string | | **unique** |
| type | string | | HVAC, Generator, Elevator, Pump, etc. — free text, indexed |
| manufacturer, model_number, serial_number | string | ✓ | |
| building_id | bigint FK → buildings.id | ✓ | `onDelete('set null')`, indexed |
| floor_id | bigint FK → floors.id | ✓ | `onDelete('set null')`, indexed |
| description | text | ✓ | |
| status | enum | | `active`, `inactive`, `maintenance`, `fault` — default `active` |
| power_rating | decimal(10,2) | ✓ | kW |
| ip_address | string | ✓ | |
| installation_date, last_maintenance_date, next_maintenance_date | date | ✓ | |
| maintenance_interval_days | integer | ✓ | |
| capacity_tons, spec_voltage, fuel, autostart | mixed | ✓ | HVAC/generator-specific specs |
| capacity_persons, speed_m_s | mixed | ✓ | elevator-specific specs |
| flow_lpm, head_m | mixed | ✓ | pump-specific specs |
| cells, makeup_valve | mixed | ✓ | cooling-tower-specific specs |
| is_critical | boolean | | default `false`, indexed |
| **device_key** | string(64) | ✓ | SHA-256 hash of the device's plaintext API key. **Unique.** Never exposed as plaintext — see below. |
| **device_key_encrypted** | text | ✓ | `Crypt::encryptString()` of the plaintext key, so it can be decrypted and shown again to an authorized user. |
| **last_seen_at** | timestamp | ✓ | Stamped to `now()` (not the reading's own `logged_at`) inside `MachineLogService::store()` every time any log is stored for this machine — a replayed backlog of old readings still proves the device is alive *now*. Not written by `AuthenticateDevice`; moved into the service in Phase 6 so every caller of `store()`, not just the sensor API, keeps offline detection accurate. Null means never commissioned/never reported — see `Machine::isOnline()` and the `alerts` "offline" type below. |
| deleted_at | timestamp | ✓ | `SoftDeletes` |
| timestamps | | | |

**Why two device-key columns?** `device_key` (a one-way hash) is what `AuthenticateDevice::handle()` looks up against on every sensor request — fast, indexed, unique, and even a full DB dump doesn't reveal a usable key. `device_key_encrypted` is a two-way encrypted copy (using the app's `APP_KEY`) that exists purely so a user with the `machines.view-device-key` permission can view/copy the key again later without regenerating it. See [Machine.php](../app/Models/Machine.php) (`generateDeviceKey()`, `decryptDeviceKey()`, `findByDeviceKey()`) and [06-security.md](06-security.md).

Both columns are in the model's `$hidden` array, so they never appear in `toArray()`/`toJson()` output even accidentally.

**Relationships** ([Machine.php](../app/Models/Machine.php)): `belongsTo(Building)`, `belongsTo(Floor)`, `hasMany(MachineLog)` (as `logs()`), `hasMany(SensorThreshold)` (as `thresholds()`), `hasMany(Alert)` (as `alerts()`).

**Scopes**: `active()`, `critical()`, `byBuilding($id)`, `byFloor($id)`.

**Casts**: decimals cast to `decimal:2`/`decimal:3` strings, dates cast to `date`, `last_seen_at` cast to `datetime`, booleans cast to `boolean`.

### `machine_logs`

Migration: [2026_02_19_142853_create_machine_logs_table.php](../database/migrations/2026_02_19_142853_create_machine_logs_table.php)

This is the sensor/event log table — every reading from a device and every manual event (status change, maintenance note, alarm) is one row here.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| machine_id | bigint FK → machines.id | | `onDelete('cascade')` |
| log_type | enum | | `status_change`, `maintenance`, `alarm`, `sensor_reading`, `operation`, `error` |
| event | string | ✓ | short description |
| severity | enum | | `info` (default), `warning`, `error`, `critical` |
| previous_status, current_status | string | ✓ | used for `status_change` logs |
| temperature | decimal(8,2) | ✓ | °C, can be negative |
| humidity | decimal(5,2) | ✓ | 0–100% |
| pressure | decimal(10,2) | ✓ | bar, can be negative |
| power_consumption | decimal(10,2) | ✓ | kW, non-negative |
| voltage | decimal(8,2) | ✓ | non-negative |
| current | decimal(8,2) | ✓ | non-negative |
| frequency | decimal(8,2) | ✓ | non-negative |
| runtime_hours | integer | ✓ | non-negative |
| vibration_rms | decimal(8,3) | ✓ | non-negative |
| bearing_temp | decimal(8,2) | ✓ | °C, can be negative |
| oil_level | decimal(6,2) | ✓ | non-negative |
| notes | text | ✓ | |
| performed_by | string | ✓ | free text — a user's name, or "system"/the device identifier |
| logged_at | timestamp | | defaults to `CURRENT_TIMESTAMP` at the DB level; app also defaults it in `MachineLogService::store()` if omitted |
| timestamps | | | separate from `logged_at` — `created_at`/`updated_at` track when the *row* was written, `logged_at` is when the *event* happened |

Indexes: `(machine_id, logged_at)`, `(log_type, severity)`, `logged_at`, `severity`.

**Relationships** ([MachineLog.php](../app/Models/MachineLog.php)): `belongsTo(Machine)`.

**Scopes**: `alerts()` (severity in error/critical), `byType($type)`, `recent($days = 7)`, `bySeverity($severity)`.

**Retention**: `MachineLog` uses Laravel's `Prunable` trait — rows older than the `log_retention_days` setting (default 365) are deleted daily by the scheduled `model:prune` command. See [02-architecture.md](02-architecture.md#data-retention-prunable-machinelog).

### `sensor_thresholds`

Migration: [2026_09_26_191411_create_sensor_thresholds_table.php](../database/migrations/2026_09_26_191411_create_sensor_thresholds_table.php)

Per-machine, per-metric warning/critical bounds, configured on the machine edit page. `AlertService::evaluate()` checks every incoming `machine_logs` row against these.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| machine_id | bigint FK → machines.id | | `onDelete('cascade')` |
| metric | enum | | one of `temperature`, `humidity`, `pressure`, `power_consumption`, `voltage`, `current`, `frequency`, `vibration_rms`, `bearing_temp`, `oil_level` — matches a `machine_logs` column |
| warning_min, warning_max | decimal(12,3) | ✓ | a null bound means that side isn't checked |
| critical_min, critical_max | decimal(12,3) | ✓ | checked before the warning bounds — see `AlertService::determineSeverity()` |
| timestamps | | | |

Unique on `(machine_id, metric)` — one threshold row per machine per metric. A metric with all four bounds empty has no row at all (removed by `AlertService::saveThresholds()` rather than stored as all-null).

**Relationships** ([SensorThreshold.php](../app/Models/SensorThreshold.php)): `belongsTo(Machine)`.

### `alerts`

Migration: [2026_09_26_191411_create_alerts_table.php](../database/migrations/2026_09_26_191411_create_alerts_table.php)

A threshold breach or a device-offline event raised against a machine. Created/escalated/auto-resolved by `AlertService`.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| machine_id | bigint FK → machines.id | | `onDelete('cascade')` |
| machine_log_id | bigint FK → machine_logs.id | ✓ | the reading that raised/last updated this alert; **always null for an `offline` alert** — it isn't caused by any one reading; `nullOnDelete()` |
| type | enum | | `threshold` (default) or `offline` |
| metric | string | ✓ | one of the `sensor_thresholds.metric` values for a threshold alert; **always null for `offline`** |
| value | decimal(12,3) | ✓ | the reading that triggered/last updated this alert; **always null for `offline`** |
| severity | enum | | `warning`, `critical` — no `info` level exists, so even an offline alert must pick one of these two (see below) |
| status | enum | | `open` (default), `acknowledged`, `resolved` |
| message | text | | human-readable summary; `AlertService::buildMessage()` for a threshold alert, `buildOfflineMessage()` for an offline one — the latter uses an **absolute** timestamp (`format('M d, Y H:i')`), never `diffForHumans()`, since the text is stored and would go stale |
| acknowledged_by | bigint FK → users.id | ✓ | `nullOnDelete()` |
| acknowledged_at | timestamp | ✓ | |
| resolved_at | timestamp | ✓ | |
| timestamps | | | |

Indexes: `(machine_id, metric, status)` (the lookup `AlertService::evaluate()` does on every reading), `type`, `status`.

**Relationships** ([Alert.php](../app/Models/Alert.php)): `belongsTo(Machine)`, `belongsTo(MachineLog)`, `belongsTo(User, 'acknowledged_by')` (as `acknowledgedBy()`).

**Scopes**: `open()` (status = open).

**Threshold alert rules enforced in `AlertService`, not the schema**: at most one **open** alert per `(machine_id, metric)` at a time — a second breach updates the existing row (raising `severity` from `warning` to `critical` if the new reading is worse, never downgrading it) rather than inserting a new row; a reading back within both bounds auto-resolves whatever was **open** (not acknowledged) for that metric.

**Offline alert rules** (`AlertService::raiseOfflineAlerts()`/`resolveOfflineAlerts()`, Phase 6):
- Raised by the scheduled `app:check-offline-devices` command for any machine where `last_seen_at` is non-null, older than the threshold (10 minutes by default — `AlertInterface::OFFLINE_AFTER_MINUTES`), status isn't `inactive`, and no `offline` alert is already `open`/`acknowledged` for it. `last_seen_at IS NULL` (never commissioned) is deliberately **not** treated as offline — an outage requires having reported at least once.
- Severity is `critical` if `machine.is_critical`, else `warning` — a direct, permanent mapping (there's no third option, since the enum has no `info`).
- At most one open-or-acknowledged offline alert per machine at a time; the command's query itself is the dedupe, so running it every minute never re-raises or re-notifies for a machine already flagged.
- Resolved from **both** `open` and `acknowledged` status the moment any new log is stored for the machine (inside `AlertService::evaluate()`, which every call to `MachineLogService::store()` runs) — unlike a threshold alert, whether the device is back online is objective, so there's no reason to leave an acknowledged one hanging and every reason to let it clear so the dedupe above works correctly on the next real outage.

### `work_orders`

Migrations: [2026_02_18_080220_create_work_orders_table.php](../database/migrations/2026_02_18_080220_create_work_orders_table.php) (original placeholder, `id`+`timestamps` only) and [2026_09_26_202345_add_details_to_work_orders_table.php](../database/migrations/2026_09_26_202345_add_details_to_work_orders_table.php) (Phase 7 — every real column).

A maintenance or repair task against a machine, optionally originating from an alert.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| code | string | | unique display id, e.g. `WO-2026-0001`; generated by `WorkOrderService::generateCode()` (year + a 4-digit sequence counted within that year) |
| title | string | | |
| description | text | ✓ | |
| machine_id | bigint FK → machines.id | | `onDelete('cascade')` |
| alert_id | bigint FK → alerts.id | ✓ | set only when created directly from an open alert (e.g. the "Create Work Order" button on an alert row); `nullOnDelete()` |
| type | enum | | `preventive`, `corrective` (default), `emergency`, `inspection` |
| priority | enum | | `low`, `medium` (default), `high`, `urgent` |
| status | enum | | `pending` (default), `in_progress`, `completed`, `cancelled` |
| assigned_to | bigint FK → users.id | ✓ | the technician (or anyone) responsible; `nullOnDelete()` |
| created_by | bigint FK → users.id | ✓ | who created it; `nullOnDelete()` — nullable so a future system-generated preventive work order (Phase 8) doesn't need a synthetic user |
| due_date | date | ✓ | |
| started_at | timestamp | ✓ | stamped when status first becomes `in_progress` |
| completed_at | timestamp | ✓ | stamped when status becomes `completed` |
| resolution_notes | text | ✓ | free text describing what was done, captured on completion |
| deleted_at | timestamp | ✓ | `SoftDeletes` |
| timestamps | | | |

Indexes: `(status, priority)`, `machine_id`, `assigned_to`.

**Relationships** ([WorkOrder.php](../app/Models/WorkOrder.php)): `belongsTo(Machine)`, `belongsTo(Alert)`, `belongsTo(User, 'assigned_to')` (as `assignedTo()`), `belongsTo(User, 'created_by')` (as `createdBy()`).

**Scopes**: `open()` (status in `pending`/`in_progress`).

**Rules enforced in `WorkOrderService`, not the schema**:
- **Only the assignee, or anyone holding the broader `work-orders.update` permission, may change a work order's status** (`updateStatus()` throws `WorkOrderActionException` otherwise) — this is what "technicians update only their own" means in practice: a technician has `work-orders.update-status` but not `work-orders.update`, so the check falls through to comparing `assigned_to` against the acting user; a manager/admin has `work-orders.update` and can touch any work order.
- **Completing a work order with a non-null `alert_id` resolves that alert** via `AlertInterface::resolve()`.
- **Completing a `preventive` work order advances the machine's maintenance schedule**: `machines.last_maintenance_date` is set to today and `next_maintenance_date` to today plus `machines.maintenance_interval_days` (falling back to 90 if that column is null).
- **Creating a work order with an assignee, or reassigning one to a new assignee, sends `WorkOrderAssignedNotification`** (database + mail, `ShouldQueue`) to that one user — unlike alert notifications (broadcast to every admin/manager), a work order has exactly one assignee.
- `code` has a database-level unique constraint as a backstop, but generation itself isn't concurrency-locked — acceptable at this app's scale, the same trade-off already made for `machine_code`.
- **`generatePreventiveWorkOrders(int $leadDays)`** (Phase 8, run daily by `app:generate-preventive-work-orders`): creates a `preventive` work order, `created_by` null, for every machine with a non-null `next_maintenance_date` at or before `today + $leadDays`, excluding `inactive` machines and any machine that already has an open (`pending`/`in_progress`) preventive work order — that existing-open check is the dedupe, so running it twice in a row (or missing a day) never creates a duplicate. Priority is `high` if the machine is already overdue, else `medium`; `due_date` is set to the machine's `next_maintenance_date`. The same `inactive`-machine exclusion is applied by `MaintenanceService` to every dashboard count, so a machine the generator would skip also never shows as "overdue" on the dashboard.

### `settings`

Migration: [2026_09_27_051526_create_settings_table.php](../database/migrations/2026_09_27_051526_create_settings_table.php).

A generic key-value store — one row per setting, rather than one column per setting on some singleton table — read and written exclusively through the cached `SettingInterface`/`SettingService` (see [02-architecture.md](02-architecture.md#container-binding-registry)), never queried directly.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| key | string | | unique; one of `SettingInterface::KEYS` |
| value | text | ✓ | JSON-encoded (`json_encode`/`json_decode`), so a value can be a string, number, bool, or array (e.g. `alert_emails`, `default_thresholds`) |
| timestamps | | | |

**Keys** (`SettingInterface::KEYS` / `::DEFAULTS`): `company_name`, `alert_emails` (array of strings), `offline_timeout_minutes`, `preventive_lead_days`, `auto_fault_on_critical` (bool), `default_thresholds` (array keyed by metric, same shape as a `sensor_thresholds` row), `log_retention_days`. A key with no row falls back to its default — `SettingService::all()` merges stored values over `DEFAULTS`, so a fresh install (or a key added later without a migration) never crashes on a missing setting.

`SettingService` caches the merged result forever under `app.settings` and calls `Cache::forget()` on every `update()` — see [02-architecture.md](02-architecture.md#container-binding-registry) for consumers (the offline/preventive commands, `AlertService`, `MachineService`, `MachineLog::prunable()`, the sidebar's company name).

### `activity_log`

Migrations: [2026_09_27_052350_create_activity_log_table.php](../database/migrations/2026_09_27_052350_create_activity_log_table.php), [..._add_event_column...](../database/migrations/2026_09_27_052351_add_event_column_to_activity_log_table.php), [..._add_batch_uuid_column...](../database/migrations/2026_09_27_052352_add_batch_uuid_column_to_activity_log_table.php) — stock `spatie/laravel-activitylog` migrations, unmodified.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint PK | | |
| log_name | string | ✓ | defaults to `default`; unused here (single log name across all models) |
| description | string | | e.g. `created`, `updated` |
| subject_type / subject_id | morph | ✓ | the model the activity happened to — `Machine`, `Building`, `WorkOrder`, or `Alert` |
| event | string | ✓ | `created`/`updated`/`deleted` (added by the second migration) |
| causer_type / causer_id | morph | ✓ | who did it — the authenticated `User`, resolved automatically from the request; null for a system/console action (e.g. the preventive-work-order generator) |
| properties | json | ✓ | `{"attributes": {...new values...}, "old": {...previous values...}}` for an `updated` event |
| batch_uuid | uuid | ✓ | groups activities logged in the same `LogBatch` (added by the third migration; unused — no code opens a batch) |
| timestamps | | | |

See [02-architecture.md](02-architecture.md#audit-logging-spatielaravel-activitylog) for which models log, why `Alert` logs a narrower column set than the others, and how the machine page's combined Activity feed queries this table.

### Auth & permissions tables

- **`users`** — standard Laravel auth table (`name`, `email`, `password`, etc.). Uses Spatie's `HasRoles` trait.
- **`personal_access_tokens`** — Sanctum, used only by `GET /api/user`.
- **`permissions`**, **`roles`**, **`model_has_permissions`**, **`model_has_roles`**, **`role_has_permissions`** — standard Spatie `laravel-permission` tables, created by [create_permission_tables.php](../database/migrations/2026_09_26_070622_create_permission_tables.php). See [06-security.md](06-security.md) for the roles/permissions currently defined.

### Framework tables

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens` — standard Laravel framework tables, no application-specific columns.

`notifications` — standard Laravel notifications table ([2026_09_26_191412_create_notifications_table.php](../database/migrations/2026_09_26_191412_create_notifications_table.php)), backing the `database` channel `MachineAlertNotification` uses and the header bell's unread count/dropdown (`$user->unreadNotifications`).
