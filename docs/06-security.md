# Security

## Session authentication

Standard Laravel UI session auth (`laravel/ui`, `Auth::routes()` in [routes/web.php](../routes/web.php)). Every route except `/`, `/login`, `/register`, and `/password/*` requires an authenticated session, enforced by wrapping the whole route group in `Route::middleware('auth')`.

`/` shows the login view to guests and redirects authenticated users straight to `home` — it never shows a "logged in as a guest" state.

⚠ **Public registration is currently open** (`Auth::routes()` with default options). Phase 4 disables it (`Auth::routes(['register' => false])`) so accounts are only created by an admin from the Users page.

## CSRF

Standard Laravel CSRF protection on all `web`-group routes. Two patterns are used in this codebase to attach the token:

1. **Traditional forms** (buildings, machines, device-key generation): `@csrf` Blade directive inside the `<form>`.
2. **Fetch-based AJAX** (AI chat in [machines/show.blade.php](../resources/views/machines/show.blade.php)): reads the token from the `<meta name="csrf-token">` tag in the layout head and sends it as an `X-CSRF-TOKEN` header manually. Note: `resources/js/bootstrap.js` configures `axios` for cookie-based XSRF, but the AI chat code uses raw `fetch()`, not axios — if you add a new AJAX feature, either follow the existing manual-header pattern for consistency, or switch to axios and get CSRF handling for free.

The sensor ingestion API (`/api/machine-logs`) is **not** in the `web` group and has no CSRF protection — it doesn't need it, because it isn't driven by a browser session; see below.

## Device authentication (sensor API)

The hardware ingestion endpoint is deliberately **not** protected by user sessions — a temperature sensor can't log in. Instead:

- Each **machine** has its own API key, generated from the web UI (`Machine::generateDeviceKey()`).
- The key is stored two ways on the `machines` table:
  - `device_key`: `hash('sha256', $plainKey)` — a one-way hash used to authenticate incoming requests. Even a full database dump doesn't reveal a usable key.
  - `device_key_encrypted`: `Crypt::encryptString($plainKey)` — a two-way encrypted copy (using the app's `APP_KEY`), decrypted only when an authorized user wants to view/copy the key again.
- Incoming requests to `/api/machine-logs` go through the `AuthenticateDevice` middleware (alias `device.auth`, registered in [bootstrap/app.php](../bootstrap/app.php)): it reads the `X-Device-Key` header, looks up `Machine::findByDeviceKey()`, and returns `401` if it doesn't match. On success, it attaches the resolved machine to the request (`$request->attributes->set('device_machine', $machine)`) and stamps `last_seen_at`.
- The controller (`API\MachineLogController@store`) takes `machine_id` **only** from the authenticated device, never from the request body — a device physically cannot write logs for a machine other than its own, even if it tried to spoof `machine_id` in the payload.
- The route also has `throttle:120,1` (120 requests/minute).

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
| `manager` | Oversight role; currently gets `machines.view-device-key` |
| `technician` | Field/hardware role; currently gets `machines.view-device-key` (technicians are the ones physically wiring up sensors) |
| `viewer` | Read-only; no permissions currently assigned |

The seeded test user (`test@example.com`) is assigned `admin`.

### Permissions defined so far

| Permission | Gates | Granted to |
|---|---|---|
| `machines.view-device-key` | Viewing a machine's device API key (the persistent Show/Copy control) and generating/regenerating it (`POST /machines/{machine}/device-key`, route middleware `permission:machines.view-device-key`) | admin, manager, technician |

More permissions (`buildings.manage`, `machines.manage`, `work-orders.manage`, `work-orders.update-status`, `alerts.acknowledge`, `reports.view`, `users.manage`, `settings.manage`) are planned for Phase 4 as each module gets real CRUD gating.

### How to add a new permission

1. Add the permission name to the `$permissions` array in `RolesAndPermissionsSeeder::run()`, and call `->givePermissionTo()` on whichever roles should have it.
2. Re-run the seeder: `php artisan db:seed --class=RolesAndPermissionsSeeder` (safe to re-run — everything uses `firstOrCreate`).
3. Gate a route: `->middleware('permission:your.permission')`.
4. Gate a Blade section: `@can('your.permission') ... @endcan`.
5. Gate a check in PHP: `auth()->user()->can('your.permission')`.

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
