# Changelog

Entries are added phase by phase (see [10-conventions.md](10-conventions.md) for the workflow this follows). Newest first.

## Phase 4c — Yajra DataTables on every real list page (2026-09-26)

`yajra/laravel-datatables` was already in `composer.json` (oracle + html/buttons/editor/export/fractal, auto-discovered) but had zero usage — no `app/DataTables/`, no published config, no endpoint returning DataTables JSON. It's also the server-side PHP half only: the project had no jQuery and no DataTables JS at all (`resources/js/app.js` was 0 bytes).

- Published `config/datatables.php`. New shared partial [`partials/_datatables-assets.blade.php`](../resources/views/partials/_datatables-assets.blade.php) loads jQuery + DataTables via CDN (`@push('styles')`/`@push('scripts')`), included only on pages with a table. Handles two layout-specific issues once instead of per-page: the main layout hides page content for ~300ms after load (tables set `autoWidth: false` + a delayed `columns.adjust()` safety net), and the Tailwind v4 browser-CDN compiler can't generate utility classes for DOM DataTables injects after page load (DataTables' own stylesheet is used instead, blended with the `forest` theme via a small CSS override).
- **Services gained `getQueryForDataTable()` methods** returning a query `Builder` (never a `JsonResponse` — services still don't touch HTTP): `BuildingInterface`, `MachineInterface`, and `MachineLogInterface` (scoped to one machine, with severity/log_type/date-range filters). This **replaced and removed** three now-fully-unused methods: `BuildingService::getAllBuildings()` (an unbounded `->get()` that the controller then summed in PHP for stats), `MachineService::getAllPaginated()`, and `MachineService::getLogsForMachine()`. `BuildingService` also gained a proper `getStats()` (count queries) to replace the PHP-side summing.
- **New `app/DataTables/`** — `BuildingsDataTable`, `MachinesDataTable`, `MachineLogsDataTable`, each a thin Yajra `Services\DataTable` wiring a service's query into column formatting (`addColumn`/`editColumn`/`rawColumns`) and a custom `filter()` override that replicates the old multi-field OR search. Action-button and formatted-cell columns render through small Blade partials (e.g. `buildings/_datatable-actions.blade.php`) so `@can` gating still applies per row in the ajax response.
- **New endpoints**: `GET /buildings/data`, `GET /machines/data` (both registered *before* their `Route::resource()` calls — otherwise the resource's `show` route would swallow `/data` as the `{id}` parameter), and `GET /machines/{machine}/logs/data`. All three carry the same `permission:{module}.view` middleware as their page.
- **Converted to server-side tables**: Buildings and Machines index pages (previously DaisyUI card grids — Buildings' filter dropdowns had no `name` attributes and did nothing before this; they're real filters now), and the machine-logs table on `machines/show` (previously Laravel-paginated at 50/page, the highest-volume table in the app since it grows with every sensor reading, and rendered without eager-loading — mildly N+1-prone).
- **Converted to client-side tables** (small, fixed-size, no ajax endpoint): `roles/show`'s "users with this role" table and `permissions/index`.
- Verified the full stack against a temporary SQLite database (migrations run clean; seeded real records; called each DataTable's `ajax()` directly and confirmed correct JSON, working status/type/building/severity filters, working custom search override, and correctly rendered computed columns like `floors_count`/`machines_count`/`building_name`) rather than relying on `php -l` alone.
- Docs: `02-architecture.md` (new "List pages: server-side DataTables" section, updated "adding a new module" checklist and anti-patterns), `04-routes.md` (new `/data` endpoints), `08-frontend.md` (stack table, view-folder conventions), `09-setup-and-operations.md` (troubleshooting), `01-overview.md` (tech stack + module notes), CHANGELOG.

## Phase 4b — Roles & Permissions pages (2026-09-26)

- Extended `RolesAndPermissionsSeeder`: added `roles` to the CRUD module set (`roles.view/create/update/delete`) and the read-only `permissions.view`. `manager` now also gets `roles.view`/`permissions.view`.
- **`RoleInterface`/`RoleService`** (bound in `AppServiceProvider`): full role CRUD plus the protection rules documented in [06-security.md](06-security.md) — the `admin` role can't be renamed/deleted/modified, a role can't be deleted while any user holds it, and an admin can't edit their own role to drop its `roles.update` permission. Violations throw the new `App\Exceptions\RoleActionException`, caught by `RoleController` and turned into a flashed error (see the new "services throw, controllers catch" pattern in [02-architecture.md](02-architecture.md)).
- **`PermissionInterface`/`PermissionService`** (read-only): groups permissions by module for the matrix UI, and lists every permission with its roles for the Permissions page.
- **`RoleController`** (`Route::resource('roles')` + `roles.confirm-delete`) and **`PermissionController`** (`permissions.index`), both constructor-gated the same way as the other CRUD controllers.
- **Views**: `roles/index`, `create`, `edit`, `show`, `delete`, plus a shared `roles/_permission-matrix.blade.php` partial (module × view/create/update/delete checkboxes, with row/column/select-all toggles) reused across create/edit (editable) and show (read-only). `permissions/index` is a plain grouped table.
- Sidebar gained an "ACCESS CONTROL" section (Users, Roles, Permissions), each link gated by its own `@can`, the whole section hidden via `@canany` if a user has none of the three `.view` permissions.
- Docs: `04-routes`, `06-security` (protection rules + expanded matrix), `02-architecture` (registry + new exception-handling pattern), `08-frontend` (permission-matrix partial), `01-overview`, CHANGELOG.

## Phase 4a — Roles, permissions & real Users page (2026-09-26)

- **CRUD-granular permissions**, not one blanket `.manage` per module: `RolesAndPermissionsSeeder` now generates `{module}.view/create/update/delete` for `buildings`, `floors`, `machines`, `users` (16 permissions), plus the existing `machines.view-device-key`. `manager`/`technician`/`viewer` role grants rebuilt against this set (see the matrix in [06-security.md](06-security.md)); `admin` still gets everything.
- `BuildingController`, `FloorController`, `MachineController` now gate each action with `permission:` middleware registered in the constructor (`$this->middleware(...)->only([...])`), rather than being wide open to any logged-in user. Create/edit/delete buttons in the Buildings and Machines views, and the "Users" sidebar link, are hidden with `@can` to match.
- Public registration disabled (`Auth::routes(['register' => false])`) — no more `/register` route.
- **Users module built out**: `UserInterface`/`UserService`/`UserController`, bound in `AppServiceProvider`. Real CRUD — create (name/email/password/role), edit (name/email/role), reset password, delete (blocked server-side for your own account). Rewrote `users/index.blade.php` to show real seeded users instead of six hardcoded fake ones; stats tiles now reflect actual per-role counts.
- **Profile page trimmed to what's real**: kept/wired up "update your name & email" and "change your password" (with current-password check); removed decorative sections that didn't map to any actual column or feature (department, job title, employee ID, 2FA, active sessions, notification preferences, activity log) rather than leaving fake controls that silently do nothing.
- Docs: `04-routes.md`, `06-security.md` (full CRUD permission matrix), `02-architecture.md` (registry + new "permission gating lives on the controller" section), `01-overview.md`, `08-frontend.md` (Users' modal-based CRUD as a documented exception to the index/create/edit/show/delete convention).

## Phase 3 — System documentation (2026-09-26)

Added the full `docs/` folder documenting the system as it stood after Phase 2: architecture (Interface → Service → Controller pattern, layer diagram, three request-lifecycle sequence diagrams, container binding registry, "adding a new module" checklist, anti-patterns), data model (ERD + every table), routes (every web/API route with middleware and permissions), the device/sensor API integration guide, security, AI insights, frontend conventions, and setup/operations. Replaced the default Laravel `README.md` with a project-specific one.

No code changes in this phase — documentation only, describing the codebase as fixed in Phase 2.

Known issues documented (not fixed): `floors.floors.index` double route-name prefix ([04-routes.md](04-routes.md)); `Machine::location` referenced but not a real attribute in the AI context builder ([07-ai-insights.md](07-ai-insights.md)).

## Phase 2 — Interface → Service → Controller consistency (2026-09-26)

Fixed every place the codebase deviated from the layered pattern that Phase 3 then documented:

- `BuildingController`/`FloorController` now inject `BuildingInterface`/`FloorInterface` instead of concrete services.
- `FloorInterface` is now bound in `AppServiceProvider` (previously unbound — masked by `FloorController` injecting the concrete class instead).
- API `MachineLogController` injects `MachineLogInterface` instead of `MachineLogService`.
- `ProcessMachineAIInsight` job now type-hints `AIInterface` instead of the concrete `OpenAIService`.
- Extracted `buildMachineContext()`/`buildPrompt()` into a shared trait (`App\Services\Concerns\BuildsMachineContext`) used by both AI providers; made `GeminiService implements AIInterface` (it previously didn't implement the interface at all and was unreachable dead code). Added `AI_PROVIDER` env var to switch providers at runtime via a container-binding closure.
- Extracted the ~260-line suggested-questions algorithm out of `AIController` into `SuggestedQuestionInterface`/`SuggestedQuestionService`.
- Removed the dead `ai.quick-insight` route (pointed at a non-existent controller method) and the unused duplicate `api.ai.*` route group.
- Added real return/param types to `BuildingInterface`/`FloorInterface` to match `MachineInterface`'s style.

## Phase 1 — Security lockdown (2026-09-26)

- Locked down every route except `/`, login, and register behind `auth` middleware (previously only `/home` required login).
- Added per-machine device API key authentication for the sensor ingestion endpoint (`POST /api/machine-logs`): `AuthenticateDevice` middleware validates an `X-Device-Key` header against a SHA-256 hash on the `machines` table, rather than trusting a `machine_id` in the request body. Added `throttle:120,1`.
- Device key is also stored encrypted (`device_key_encrypted`) so it can be viewed again later, gated behind a new `machines.view-device-key` permission (installed `spatie/laravel-permission`, seeded `admin`/`manager`/`technician`/`viewer` roles).
- Machine page gained a "Device API Key" card (generate/regenerate, Show/Hide/Copy) visible only to permitted users.
