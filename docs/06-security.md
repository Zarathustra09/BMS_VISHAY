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
- What a device may report is limited to **its own machine's parameters** (`MachineParameterService::readingRules()`): an unknown key, a wrong type or an out-of-range value is a `422`, so a compromised or buggy device can't write arbitrary keys into its logs.
- The machine page's **API Integration** card (endpoint, example body, `curl`) is visible to anyone with `machines.view`, so it never embeds the real device key — the `curl` example carries a `YOUR_DEVICE_KEY` placeholder. The key itself stays behind `machines.view-device-key`.
- `GET /machines/{machine}/parameters` (the "copy parameters" JSON) needs `machines.view`; it returns parameter definitions only, never readings or keys.

**Machine liveness (online/offline) is intentionally not permission-gated.** `last_seen_at`'s existing display was behind `machines.view-device-key` only because that's where it happened to be printed on `machines/show`; the Online/Offline badge in the page header (Phase 6) is visible to anyone who can view the machine at all (`machines.view`) — "this machine stopped reporting" is operational information, not a secret tied to the device key.

Regenerating a key (`POST /machines/{machine}/device-key`) immediately invalidates the old one — there's exactly one valid key per machine.

## Roles & permissions (Spatie)

`spatie/laravel-permission` is installed and wired up:

- `User` uses the `HasRoles` trait ([User.php](../app/Models/User.php)).
- Middleware aliases `role`, `permission`, `role_or_permission` are registered manually in [bootstrap/app.php](../bootstrap/app.php) — **this is required** on Laravel 11+'s new bootstrap style; Spatie's package does not auto-register them the way it did on older Laravel versions with the `Kernel.php` middleware groups.
- `@can('permission-name')` works in Blade without any extra setup, because Spatie's `PermissionRegistrar` hooks into Laravel's `Gate::before()`.
- **A switched-off module's permissions are denied even to admin.** `AppServiceProvider::register()` adds its own `Gate::before` *ahead of* Spatie's that returns `false` for any ability whose prefix belongs to a module disabled by `config/modules.php` (`buildings.*`, `floors.*`, `work-orders.*`, `maintenance.view`, `reports.view` in a lite edition). The permissions still exist in the database and on their roles — this only changes what the Gate answers while the module is off. See [02-architecture.md](02-architecture.md#editions--module-toggles-two-layer-enforcement) for why the registration order matters.

### Roles (seeded by [RolesAndPermissionsSeeder.php](../database/seeders/RolesAndPermissionsSeeder.php))

| Role | Intent |
|---|---|
| `admin` | Full access — gets every permission that exists (`syncPermissions(Permission::all())`) |
| `manager` | Oversight of buildings/floors/machines (view/create/update, not delete); can view users but not manage accounts; views and can acknowledge/resolve alerts; full work order CRUD + status control |
| `technician` | Field/hardware role — views buildings/floors, views + updates machines, wires up sensors, acts on alerts day-to-day (acknowledge/resolve), and works through their assigned work orders (view + status updates only — not create/edit/delete/reassign) |
| `viewer` | Dashboard, machines (view, edit, delete and the device key - not create) and alerts (view, acknowledge, resolve). `floors.view` is held only so the machine edit form can load its Floor dropdown. No other module is reachable |

The seeded accounts are `admin@example.com` (`admin`) and `viewer@example.com` (`viewer`), both with password `password`. More roles can be created from the Roles page — see below.

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
| `settings` (special) | `.view`, `.update` | The Settings page (company name, alert emails, offline timeout, preventive lead days, auto-fault toggle, default thresholds, log retention). **Admin-only** — unlike every other module above, neither permission is granted to `manager`, `technician`, or `viewer`, since this is system-wide configuration rather than data any role operates on. |

### Role → permission matrix

| Permission | admin | manager | technician | viewer |
|---|:---:|:---:|:---:|:---:|
| `buildings.view` | ✅ | ✅ | ✅ | – |
| `buildings.create` | ✅ | ✅ | – | – |
| `buildings.update` | ✅ | ✅ | – | – |
| `buildings.delete` | ✅ | – | – | – |
| `floors.view` | ✅ | ✅ | ✅ | ✅ |
| `floors.create` | ✅ | ✅ | – | – |
| `floors.update` | ✅ | ✅ | – | – |
| `floors.delete` | ✅ | – | – | – |
| `machines.view` | ✅ | ✅ | ✅ | ✅ |
| `machines.create` | ✅ | ✅ | – | – |
| `machines.update` | ✅ | ✅ | ✅ | ✅ |
| `machines.delete` | ✅ | – | – | ✅ |
| `machines.view-device-key` | ✅ | ✅ | ✅ | ✅ |
| `machine-types.view` | ✅ | ✅ | ✅ | – |
| `machine-types.create` | ✅ | ✅ | – | – |
| `machine-types.update` | ✅ | ✅ | – | – |
| `machine-types.delete` | ✅ | – | – | – |
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
| `alerts.acknowledge` | ✅ | ✅ | ✅ | ✅ |
| `alerts.resolve` | ✅ | ✅ | ✅ | ✅ |
| `work-orders.view` | ✅ | ✅ | ✅ | – |
| `work-orders.create` | ✅ | ✅ | – | – |
| `work-orders.update` | ✅ | ✅ | – | – |
| `work-orders.delete` | ✅ | – | – | – |
| `work-orders.update-status` | ✅ | ✅ | ✅ | – |
| `maintenance.view` | ✅ | ✅ | ✅ | – |
| `reports.view` | ✅ | ✅ | ✅ | – |
| `settings.view` | ✅ | – | – | – |
| `settings.update` | ✅ | – | – | – |

Deletion of any kind is admin-only across every module except machines, which the seeded `viewer` can also delete — it's the one action with no undo from the UI (only `machines` and `work_orders` use `SoftDeletes`; every other table here is a hard delete), so it isn't delegated to manager/technician even though they can create/update. This table itself is generated by hand from `RolesAndPermissionsSeeder`; if you change the seeder, update this table in the same commit.

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

## Groups

A **group** holds roles and direct permissions, and every member inherits them; a user can be in several groups. Access is managed by moving a user in or out of a group instead of editing the user.

- `User::hasPermissionTo()` returns true when the user's own check does, or any of their groups' does. Spatie's `Gate::before` goes through that method, so `@can`, `$user->can()` and the `permission:` middleware all honour groups. `hasRole()`, `getRoleNames()` and `User::role()` do **not** - they still report only roles assigned directly.
- A switched-off module's permissions stay denied even when a group grants them (the app's own `Gate::before` runs first).
- Only an `admin` can create or change a group that has the `admin` role (`GroupService`), so editing groups can't be used to become an admin.
- A role held by a group can't be deleted (`RoleService::delete`), and a group used as a machine's assignment group can't be deleted.
- Permissions: `groups.view/create/update/delete`; admin has all, manager has `groups.view`.

**Alert recipients** are now: admins and managers, plus every member of the machine's assignment group, plus the Settings "extra alert recipients" list - each person at most once per alert.

## Endpoint and UI guarding

Every route behind login carries a `permission:` middleware, either from its controller constructor (`$this->middleware('permission:x')->only([...])`) or on the route itself. `tests/Feature/RoutePermissionCoverageTest.php` fails if a new authenticated route has none, apart from a short allowlist (`home`, `profile.*`, `notifications.*`, Laravel UI's logout/password-confirm).

Routes that need **two** permissions because they change something a single permission doesn't cover:

| Route | Permissions |
|---|---|
| `roles.assign-users` | `roles.update` + `users.update` (it changes users' roles) |
| `machines.device-key.generate` | `machines.view-device-key` + `machines.update` (regenerating breaks the device's current key) |
| `ai.questions`, `ai.chat`, `ai.check-status` | `machines.view`; `ai.chat` is throttled to 20/min, and an answer can only be collected by the user who asked (`check-status` is a 404 for anyone else, and only `ai_insight_{uuid}` keys are accepted) |

The UI follows the same rule: a button, link, tab, modal or card is hidden (`@can`) unless the user could actually use it - and a link to a machine becomes plain text without `machines.view`. Sidebar items, dashboard cards, the machine page's Active Alerts card, the buildings page's floor/machine tabs and actions, and the Delete links on edit forms are all gated this way.

## Error pages

`resources/views/errors/` replaces Laravel's stock screens: `401`, `403`, `404`, `419`, `429`, `500`, `503`, plus `4xx`/`5xx` fallbacks for any other code. They share one standalone layout (`errors/layout.blade.php`) that does not extend the app layout - that layout queries the database for the bell and company name, so it could not render on the 500s it exists for. The 403 page never prints the exception text. JSON/AJAX requests still get JSON errors.

## Alert notifications (Phase 5, extended Phase 10)

`AlertService::notify()` sends `MachineAlertNotification` to every user holding the `admin` or `manager` role — resolved fresh via `User::role(['admin', 'manager'])` on each alert, not a stored subscriber list, so adding/removing someone from those roles immediately changes who gets notified going forward. `technician` and `viewer` don't receive notifications even though they can view/act on alerts in the UI — they're expected to work from the alerts list or the machine page directly rather than being paged. See [02-architecture.md](02-architecture.md#d-threshold-evaluation-alertserviceevaluate-phase-5) for the full evaluate/notify sequence.

As of Phase 10, `notify()` **also** routes the same notification to every address in the `alert_emails` setting (`Notification::route('mail', $email)->notify(...)`) — for recipients who need alert emails without holding `admin`/`manager` (an external contractor, a shared ops inbox). Configured on the Settings page, admin-only.

## Live alerts and the alert trail

- **Trail.** Every change to an alert is appended to `alert_events` with the user who did it (null = the system), so there is a record of how people and the system interacted with a machine's alerts. `alerts.show` and the machine page's Alert trail need `alerts.view`; the trail card is hidden from users without it.
- **Channels.** `private-alerts` (everyone with `alerts.view`) and `private-machines.{id}` carry `App\Events\AlertChanged`; each person's bell uses `private-App.Models.User.{id}`. Subscribing needs the same permission as seeing the page (`routes/channels.php`). The payload only names the alert, machine, severity, status, action and actor - pages re-fetch their own (permission-checked) HTML/JSON, so nothing is exposed that the page endpoints wouldn't show.
- **Reverb never blocks anything.** Broadcasts go through `SafeBroadcaster` (a 30 s circuit breaker shared with live readings), so a stopped Reverb cannot fail a device's POST or a user's Acknowledge click.
- **A user can't be notified about an alert they can't see:** an address on the Settings recipients list is email-only (no bell, no push).

## Live updates (Reverb, Phase 12)

- **Private channels.** A machine's readings are broadcast on `private-machines.{id}`. Subscribing needs a signature from `POST /broadcasting/auth`, which only a logged-in user holding `machines.view` — the same permission that opens the page — can get (`routes/channels.php`). A guest gets `403`, as does a user without that permission.
- **What's public by design.** `REVERB_APP_KEY` is sent to every browser (it identifies the app to the WebSocket server, like a Pusher key). `REVERB_APP_SECRET` signs subscriptions and the server-to-Reverb publish API and **never leaves the PC** — it is not a `VITE_` variable. Generate your own values per install (`.env.example` ships placeholders).
- **Network exposure.** Reverb listens on `0.0.0.0:8080` so browsers elsewhere on the LAN can connect; the installer's firewall rule opens that port on the **Private** network profile only. Don't port-forward it to the internet from the router — this deployment is LAN-only by design. `allowed_origins` in `config/reverb.php` is `*` because the page's origin is the PC's own (changing) IP; the channel authorization above is what actually protects the data.
- **No new device attack surface.** Devices still only call the HTTP API; they never touch the WebSocket.

## Secrets in `.env`

| Variable | Purpose |
|---|---|
| `APP_KEY` | Laravel's encryption key — **this is what protects `device_key_encrypted`.** If it's ever rotated, every stored encrypted device key becomes undecryptable and must be regenerated. |
| `DB_*` | Database credentials |
| `OPENAI_API_KEY`, `GEMINI_API_KEY` | AI provider credentials — see [07-ai-insights.md](07-ai-insights.md) |
| `AI_PROVIDER` | `openai` or `gemini` — which `AIInterface` implementation is bound |
| `REVERB_APP_SECRET` | Signs private-channel subscriptions and the app→Reverb publish API — see [Live updates](#live-updates-reverb-phase-12). Keep it server-side; the `REVERB_APP_KEY` next to it is the public half |

`.env` is git-ignored; `.env.example` documents every key with placeholder values and should be kept in sync whenever a new config value is added.

## Known gaps

- No 2FA / password policy beyond Laravel UI defaults.
- No idempotency protection on the sensor ingestion endpoint (a resent reading creates a duplicate row) — acceptable for telemetry, called out in [05-device-api.md](05-device-api.md).
- Rate limiting on `/api/machine-logs` is per-IP (Laravel's default throttle key), not per-device — a shared NAT/gateway in front of many devices would share one bucket. Not an issue at current scale.
