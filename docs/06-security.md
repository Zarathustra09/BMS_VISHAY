# Security

## Session authentication

Standard Laravel UI session auth (`laravel/ui`, `Auth::routes(['register' => false])` in [routes/web.php](../routes/web.php)). Every route except `/`, `/login`, and `/password/*` requires an authenticated session, enforced by wrapping the whole route group in `Route::middleware('auth')`.

`/` shows the login view to guests and redirects authenticated users straight to `home` — it never shows a "logged in as a guest" state.

Public registration is **disabled** (as of Phase 4) — there is no `/register` route. Accounts are created by an admin from the Users page (`users.create` permission). The login page's `@if (Route::has('register'))` check already handles this gracefully — the "Register" link simply doesn't render.

## CSRF

Standard Laravel CSRF protection on all `web`-group routes. Two patterns are used in this codebase to attach the token:

1. **Traditional forms** (buildings, machines, device-key generation, users, profile): `@csrf` Blade directive inside the `<form>`.
2. **Fetch-based AJAX** (AI chat in [machines/show.blade.php](../resources/views/machines/show.blade.php)): reads the token from the `<meta name="csrf-token">` tag in the layout head and sends it as an `X-CSRF-TOKEN` header manually. Note: `resources/js/bootstrap.js` configures `axios` for cookie-based XSRF, but the AI chat code uses raw `fetch()`, not axios — if you add a new AJAX feature, either follow the existing manual-header pattern for consistency, or switch to axios and get CSRF handling for free.

The sensor ingestion API (`/api/machine-logs`) is **not** in the `web` group and has no CSRF protection — it doesn't need it, because it isn't driven by a browser session; see below.

## Device authentication (sensor API)

The hardware ingestion endpoint is deliberately **not** protected by user sessions — a temperature sensor can't log in. Instead:

- Each **machine** has its own API key, generated from the web UI (`Machine::generateDeviceKey()`).
- The key is stored two ways on the `machines` table:
  - `device_key`: `hash('sha256', $plainKey)` — a one-way hash used to authenticate incoming requests. Even a full database dump doesn't reveal a usable key.
  - `device_key_encrypted`: `Crypt::encryptString($plainKey)` — a two-way encrypted copy (using the app's `APP_KEY`), decrypted only when an authorized user wants to view/copy the key again.
- Incoming requests to `/api/machine-logs` go through the `AuthenticateDevice` middleware (alias `device.auth`, registered in [bootstrap/app.php](../bootstrap/app.php)): it reads the `X-Device-Key` header, looks up `Machine::findByDeviceKey()`, and returns `401` if it doesn't match. On success, it attaches the resolved machine to the request (`$request->attributes->set('device_machine', $machine)`). It does **not** stamp `last_seen_at` — that happens inside `MachineLogService::store()` (see [03-data-model.md](03-data-model.md)) so every caller of `store()`, not just this endpoint, keeps offline detection in sync.
- The controller (`API\MachineLogController@store`) takes `machine_id` **only** from the authenticated device, never from the request body — a device physically cannot write logs for a machine other than its own, even if it tried to spoof `machine_id` in the payload.
- The route also has `throttle:120,1` (120 requests/minute).

**Machine liveness (online/offline) is intentionally not permission-gated.** `last_seen_at`'s existing display was behind `machines.view-device-key` only because that's where it happened to be printed on `machines/show`; the Online/Offline badge in the page header (Phase 6) is visible to anyone who can view the machine at all (`machines.view`) — "this machine stopped reporting" is operational information, not a secret tied to the device key.

Regenerating a key (`POST /machines/{machine}/device-key`) immediately invalidates the old one — there's exactly one valid key per machine.

## Roles & permissions (Spatie)

`spatie/laravel-permission` is installed and wired up:

- `User` uses the `HasRoles` trait ([User.php](../app/Models/User.php)).
- Middleware aliases `role`, `permission`, `role_or_permission` are registered manually in [bootstrap/app.php](../bootstrap/app.php) — **this is required** on Laravel 11+'s new bootstrap style; Spatie's package does not auto-register them the way it did on older Laravel versions with the `Kernel.php` middleware groups.
- `@can('permission-name')` works in Blade without any extra setup, because Spatie's `PermissionRegistrar` hooks into Laravel's `Gate::before()`.

### Roles (seeded by [RolesAndPermissionsSeeder.php](../database/seeders/RolesAndPermissionsSeeder.php))

| Role | Intent |
|---|---|
| `admin` | Full access — gets every permission that exists (`syncPermissions(Permission::all())`) |
| `manager` | Oversight of buildings/floors/machines (view/create/update, not delete); can view users but not manage accounts; views and can acknowledge/resolve alerts; full work order CRUD + status control |
| `technician` | Field/hardware role — views buildings/floors, views + updates machines, wires up sensors, acts on alerts day-to-day (acknowledge/resolve), and works through their assigned work orders (view + status updates only — not create/edit/delete/reassign) |
| `viewer` | Read-only across buildings/floors/machines, alerts, work orders, the maintenance dashboard, and reports; no access to Users at all |

The seeded test user (`test@example.com`) is assigned `admin`. More roles can be created from the Roles page — see below.

### Permissions — CRUD-granular, not one blanket "manage" per module

Permissions follow `{module}.{action}` where `{action}` is `view`, `create`, `update`, or `delete` — deliberately **not** a single `{module}.manage` permission — so a role can, for example, view and update machines without being able to delete them. See `RolesAndPermissionsSeeder::run()` for how the set is generated (`$crudModules × $actions`).

| Module | Permissions | Gates |
|---|---|---|
| `buildings` | `.view`, `.create`, `.update`, `.delete` | Buildings CRUD routes/buttons |
| `floors` | `.view`, `.create`, `.update`, `.delete` | Floors CRUD routes (JSON endpoints used by the machine forms) |
| `machines` | `.view`, `.create`, `.update`, `.delete` | Machines CRUD routes/buttons |
| `machines` (special) | `.view-device-key` | Viewing a machine's device API key (persistent Show/Copy control) and generating/regenerating it. Separate from the CRUD set on purpose — it's about hardware credential exposure, not record editing. |
| `users` | `.view`, `.create`, `.update`, `.delete` | Users page: list, create accounts, edit/reassign role/reset password, delete accounts |
| `roles` | `.view`, `.create`, `.update`, `.delete` | Roles page: list, create custom roles, edit a role's permissions, delete an unused role |
| `permissions` (special) | `.view` | The read-only Permissions list page. No `.create`/`.update`/`.delete` — a permission does nothing until code checks it, so it can't be managed from the UI (see [02-architecture.md](02-architecture.md)) |
| `alerts` (no CRUD set) | `.view`, `.acknowledge`, `.resolve` | Alerts are raised by `AlertService::evaluate()`, never created or deleted by a user, so this module skips `.create`/`.delete` entirely in favor of the two actions a person actually takes on an open alert. `.view` gates the alerts list, the header bell, and the "Active Alerts" card on `machines/show`. |
| `work-orders` | `.view`, `.create`, `.update`, `.delete` | Work Orders CRUD routes/buttons |
| `work-orders` (special) | `.update-status` | Transitioning a work order's status (start/complete/cancel), separate from `.update` on purpose — a technician needs this to work through their own assigned tasks without also being able to edit/reassign/delete *any* work order. `WorkOrderService::updateStatus()` uses `can('work-orders.update')` (not `.update-status`) as the signal that an actor is unrestricted — see [02-architecture.md](02-architecture.md). |
| `maintenance` (special) | `.view` | The read-only maintenance dashboard. No CRUD — the dates it reports on live on the machine record, and the tasks it links to live in Work Orders, so there's nothing this module itself creates/updates/deletes. |
| `reports` (special) | `.view` | The Reports page and its CSV export. No CRUD — it's a read-only rollup (with a date-range/building filter) of data every role can already see individually via `alerts.view`/`work-orders.view`/`machines.view`/`maintenance.view`. |

### Role → permission matrix

| Permission | admin | manager | technician | viewer |
|---|:---:|:---:|:---:|:---:|
| `buildings.view` | ✅ | ✅ | ✅ | ✅ |
| `buildings.create` | ✅ | ✅ | – | – |
| `buildings.update` | ✅ | ✅ | – | – |
| `buildings.delete` | ✅ | – | – | – |
| `floors.view` | ✅ | ✅ | ✅ | ✅ |
| `floors.create` | ✅ | ✅ | – | – |
| `floors.update` | ✅ | ✅ | – | – |
| `floors.delete` | ✅ | – | – | – |
| `machines.view` | ✅ | ✅ | ✅ | ✅ |
| `machines.create` | ✅ | ✅ | – | – |
| `machines.update` | ✅ | ✅ | ✅ | – |
| `machines.delete` | ✅ | – | – | – |
| `machines.view-device-key` | ✅ | ✅ | ✅ | – |
| `users.view` | ✅ | ✅ | – | – |
| `users.create` | ✅ | – | – | – |
| `users.update` | ✅ | – | – | – |
| `users.delete` | ✅ | – | – | – |
| `roles.view` | ✅ | ✅ | – | – |
| `roles.create` | ✅ | – | – | – |
| `roles.update` | ✅ | – | – | – |
| `roles.delete` | ✅ | – | – | – |
| `permissions.view` | ✅ | ✅ | – | – |
| `alerts.view` | ✅ | ✅ | ✅ | ✅ |
| `alerts.acknowledge` | ✅ | ✅ | ✅ | – |
| `alerts.resolve` | ✅ | ✅ | ✅ | – |
| `work-orders.view` | ✅ | ✅ | ✅ | ✅ |
| `work-orders.create` | ✅ | ✅ | – | – |
| `work-orders.update` | ✅ | ✅ | – | – |
| `work-orders.delete` | ✅ | – | – | – |
| `work-orders.update-status` | ✅ | ✅ | ✅ | – |
| `maintenance.view` | ✅ | ✅ | ✅ | ✅ |
| `reports.view` | ✅ | ✅ | ✅ | ✅ |

Deletion of any kind is deliberately admin-only across every module — it's the one action with no undo from the UI (only `machines` and `work_orders` use `SoftDeletes`; every other table here is a hard delete), so it isn't delegated to manager/technician even though they can create/update. This table itself is generated by hand from `RolesAndPermissionsSeeder`; if you change the seeder, update this table in the same commit.

### Roles are fully manageable from the UI; permissions are read-only

Admins can create custom roles and edit any non-`admin` role's permissions from the **Roles** page (`roles.index`/`create`/`edit`/`show`, plus a confirm-delete page) using a checkbox matrix (`resources/views/roles/_permission-matrix.blade.php` — rows are modules, columns are the four CRUD actions, a fifth column holds non-CRUD extras like `machines.view-device-key`). The **Permissions** page (`permissions.index`) is read-only: it lists every permission and which roles hold it, but there's no add/edit/delete, because a brand-new permission wouldn't gate anything until a developer adds the corresponding `permission:` middleware or `@can` check somewhere — see [02-architecture.md](02-architecture.md) for the "adding a new module" checklist, which is how a permission actually becomes meaningful.

`RoleService` (bound from `RoleInterface`) enforces protection rules that hold regardless of what the UI allows, throwing `App\Exceptions\RoleActionException` (caught by `RoleController`, turned into a flashed error):

| Rule | Why |
|---|---|
| The `admin` role can't be renamed, deleted, or edited at all | It's the one role guaranteed to have every permission; editing it is how you'd accidentally lock everyone out |
| A role can't be deleted while any user still holds it | Forces an explicit reassignment step instead of silently leaving users with no role |
| You can't edit a role you hold to remove its own `roles.update` permission | Otherwise an admin editing their own custom role could lock themselves out of role management with no one left who can undo it |
| You can't include your own account in `roles.assign-users` | Since a user holds exactly one role, moving yourself into a role via this bulk picker could immediately drop your own access (e.g. moving yourself out of `admin`) with no confirmation step and no one left to undo it. Reassign your own role from the Users page instead — a deliberate, single-user action. |

Every write in `RoleService` calls `PermissionRegistrar::forgetCachedPermissions()` so permission changes take effect immediately, without waiting for Spatie's permission cache to expire or requiring affected users to log out and back in.

A user holds **exactly one role** in this app's UI (`syncRoles([$role])` on create/update, enforced by the single role `<select>` on the Users forms) — Spatie itself supports multiple roles per user, but roles here are meant as mutually exclusive tiers rather than composable capabilities, so the UI doesn't expose multi-select even now that custom roles can be created. This is why `RoleService::assignUsers()` — the "Add Users" action on `roles.index` — **moves** each selected user off their current role rather than adding a second one; the UI states this explicitly next to the picker.

### How to add a new permission

1. Add the permission name to the `$permissions` array in `RolesAndPermissionsSeeder::run()` (or extend `$crudModules` if it's a new module's full CRUD set), and add it to the appropriate roles' `syncPermissions()` calls. This is still done in the seeder, not the UI — see above for why.
2. Re-run the seeder: `php artisan db:seed --class=RolesAndPermissionsSeeder` (safe to re-run — everything uses `firstOrCreate`, and role permission sets are fully re-synced each run). It will also immediately show up on the Roles page's permission matrix and the Permissions list — no code change needed there, since both read from `Permission::all()`/`getGrouped()`.
3. Gate a route: in the controller's constructor, `$this->middleware('permission:your.permission')->only(['action1', 'action2'])` (see `BuildingController`/`MachineController`/`UserController`/`RoleController` for the pattern) — or directly on the route definition for a one-off action like `machines.device-key.generate`.
4. Gate a Blade section: `@can('your.permission') ... @endcan`.
5. Gate a check in PHP: `auth()->user()->can('your.permission')`.

## Alert notifications (Phase 5)

`AlertService::notify()` sends `MachineAlertNotification` to every user holding the `admin` or `manager` role — resolved fresh via `User::role(['admin', 'manager'])` on each alert, not a stored subscriber list, so adding/removing someone from those roles immediately changes who gets notified going forward. `technician` and `viewer` don't receive notifications even though they can view/act on alerts in the UI — they're expected to work from the alerts list or the machine page directly rather than being paged. See [02-architecture.md](02-architecture.md#d-threshold-evaluation-alertserviceevaluate-phase-5) for the full evaluate/notify sequence.

## Secrets in `.env`

| Variable | Purpose |
|---|---|
| `APP_KEY` | Laravel's encryption key — **this is what protects `device_key_encrypted`.** If it's ever rotated, every stored encrypted device key becomes undecryptable and must be regenerated. |
| `DB_*` | Database credentials |
| `OPENAI_API_KEY`, `GEMINI_API_KEY` | AI provider credentials — see [07-ai-insights.md](07-ai-insights.md) |
| `AI_PROVIDER` | `openai` or `gemini` — which `AIInterface` implementation is bound |

`.env` is git-ignored; `.env.example` documents every key with placeholder values and should be kept in sync whenever a new config value is added.

## Known gaps (tracked in the finalization plan)

- No 2FA / password policy beyond Laravel UI defaults.
- No audit log yet (planned Phase 10, via `spatie/laravel-activitylog`).
- No idempotency protection on the sensor ingestion endpoint (a resent reading creates a duplicate row) — acceptable for telemetry, called out in [05-device-api.md](05-device-api.md).
- Rate limiting on `/api/machine-logs` is per-IP (Laravel's default throttle key), not per-device — a shared NAT/gateway in front of many devices would share one bucket. Not an issue at current scale.
- Alert notification recipients are hardcoded to the `admin`/`manager` roles (`AlertService::notify()`) — there's no way to add extra recipient emails without holding one of those roles. Planned to become configurable via `SettingService` in Phase 10.
- An **acknowledged threshold alert never auto-resolves** even after the reading returns to normal — `AlertService::evaluate()`'s auto-resolve only matches `Alert::open()`. This is the opposite of offline alerts, which resolve from both `open` and `acknowledged` (see [03-data-model.md](03-data-model.md)). The asymmetry is intentional for now (a person who acknowledged a threshold breach is expected to also resolve it once satisfied it's handled) but is worth revisiting.
