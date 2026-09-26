# Routes

Generated from `php artisan route:list` against the codebase as of Phase 4. All routes below (except `/`, `/login`, `/password/*`, and the sensor ingestion API) require an authenticated session (`auth` middleware, applied via `Route::middleware('auth')->group()` in `routes/web.php`). Most also require a specific Spatie permission — see the **Permission** column and [06-security.md](06-security.md) for the full roles → permissions matrix.

## Auth

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/` | – | closure | `web` | Login page for guests; redirects to `home` if already logged in |
| GET/POST | `/login` | `login` | `Auth\LoginController` | `guest` (RedirectIfAuthenticated) | Session login |
| POST | `/logout` | `logout` | `Auth\LoginController@logout` | `auth` | Session logout |
| GET/POST | `/password/confirm` | `password.confirm` | `Auth\ConfirmPasswordController` | `auth` | Re-confirm password for sensitive actions |
| POST | `/password/email` | `password.email` | `Auth\ForgotPasswordController` | – | Send reset link |
| GET | `/password/reset` | `password.request` | `Auth\ForgotPasswordController` | – | Request-reset form |
| GET | `/password/reset/{token}` | `password.reset` | `Auth\ResetPasswordController` | – | Reset form |
| POST | `/password/reset` | `password.update` | `Auth\ResetPasswordController` | – | Perform reset |

⚠ **Public registration is disabled** as of Phase 4 (`Auth::routes(['register' => false])` in `routes/web.php`) — there is no `/register` route. Accounts are created by an admin from the Users page (`users.create` permission).

## Dashboard

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/home` | `home` | `HomeController@index` | `auth` | Main dashboard (currently hardcoded metrics — Phase 9) |

## Buildings

Permission gating is applied in the controller constructor (`BuildingController::__construct()`) via `$this->middleware('permission:...')->only([...])`, not on the route definitions — see [02-architecture.md](02-architecture.md) for why this pattern is used instead of splitting `Route::resource()` apart.

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/buildings` | `buildings.index` | `BuildingController@index` | `buildings.view` | List page shell + stat tiles — the table itself loads via `buildings.data` |
| GET | `/buildings/data` | `buildings.data` | `BuildingController@data` | `buildings.view` | **Ajax source for the buildings DataTable** (server-side: search, sort, page, status/type filters). Registered *before* `Route::resource('buildings', ...)` — see the comment in `routes/web.php` on why the ordering matters. |
| GET | `/buildings/create` | `buildings.create` | `BuildingController@create` | `buildings.create` | Create form |
| POST | `/buildings` | `buildings.store` | `BuildingController@store` | `buildings.create` | Create |
| GET | `/buildings/{building}` | `buildings.show` | `BuildingController@show` | `buildings.view` | Detail |
| GET | `/buildings/{building}/edit` | `buildings.edit` | `BuildingController@edit` | `buildings.update` | Edit form |
| PUT/PATCH | `/buildings/{building}` | `buildings.update` | `BuildingController@update` | `buildings.update` | Update |
| DELETE | `/buildings/{building}` | `buildings.destroy` | `BuildingController@destroy` | `buildings.delete` | Delete (requires typed confirmation + 3 acknowledgement checkboxes — see [08-frontend.md](08-frontend.md)) |
| GET | `/buildings/{building}/delete` | `buildings.confirm-delete` | `BuildingController@confirmDelete` | `buildings.delete` | Confirmation page |

## Floors

Defined under `Route::prefix('floors')->name('floors.')`. JSON-only endpoints, consumed by JS in the machine create/edit forms (not full-page views). Same controller-constructor permission-gating pattern as Buildings.

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/floors` | `floors.floors.index` ⚠ | `FloorController@index` | `floors.view` | List floors (optionally filtered by `?building_id=`) as JSON |
| POST | `/floors` | `floors.store` | `FloorController@store` | `floors.create` | Create (JSON) |
| GET | `/floors/{floor}` | `floors.show` | `FloorController@show` | `floors.view` | Detail (JSON) |
| PUT | `/floors/{floor}` | `floors.update` | `FloorController@update` | `floors.update` | Update (JSON) |
| DELETE | `/floors/{floor}` | `floors.destroy` | `FloorController@destroy` | `floors.delete` | Delete (JSON) |

⚠ **Known issue**: the index route resolves to the name `floors.floors.index` (the `Route::prefix(...)->name('floors.')` group prefix combines with the route's own `->name('floors.index')`). It works — nothing currently calls `route('floors.index')` — but the double prefix is confusing. Worth a one-line fix in a future cleanup phase.

## Machines

Same controller-constructor permission-gating pattern; `machines.device-key.generate` is the one exception, gated directly on the route definition since it isn't a standard resource action.

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/machines` | `machines.index` | `MachineController@index` | `machines.view` | List page shell + stat tiles — the table loads via `machines.data` |
| GET | `/machines/data` | `machines.data` | `MachineController@data` | `machines.view` | **Ajax source for the machines DataTable.** Registered before `Route::resource('machines', ...)`, same ordering reason as `buildings.data`. |
| GET | `/machines/create` | `machines.create` | `MachineController@create` | `machines.create` | Create form |
| POST | `/machines` | `machines.store` | `MachineController@store` | `machines.create` | Create |
| GET | `/machines/{machine}` | `machines.show` | `MachineController@show` | `machines.view` | Detail + device key card — the logs table loads via `machines.logs.data` |
| GET | `/machines/{machine}/edit` | `machines.edit` | `MachineController@edit` | `machines.update` | Edit form |
| PUT/PATCH | `/machines/{machine}` | `machines.update` | `MachineController@update` | `machines.update` | Update |
| DELETE | `/machines/{machine}` | `machines.destroy` | `MachineController@destroy` | `machines.delete` | Delete |
| GET | `/machines/{machine}/delete` | `machines.confirm-delete` | `MachineController@confirmDelete` | `machines.delete` | Confirmation page |
| GET | `/machines/{machine}/logs/data` | `machines.logs.data` | `MachineController@logsData` | `machines.view` | **Ajax source for the machine-logs DataTable** on `machines/show` — severity/log_type/date-range filters. The highest-volume table in the app (see [02-architecture.md](02-architecture.md)). |
| POST | `/machines/{machine}/device-key` | `machines.device-key.generate` | `MachineController@generateDeviceKey` | `machines.view-device-key` | Generate/regenerate this machine's sensor device API key |
| PUT | `/machines/{machine}/thresholds` | `machines.thresholds.update` | `MachineController@updateThresholds` | `machines.update` | Save this machine's alert warning/critical bounds (Phase 5) — a separate form/route from the edit wizard, since it persists through `AlertService` rather than `MachineService` |
| GET | `/machines/{machine}/readings` | `machines.readings` | `MachineController@readings` | `machines.view` | **JSON source for the Live Sensor Readings chart** (Phase 6) — `?metric=` and `?range=` (`1h`\|`24h`\|`7d`) query params, both validated against a fixed list; polled every 30s by the chart's Alpine component |

## Users

New in Phase 4. Same controller-constructor permission-gating pattern. The index page is modal-driven (add/edit/reset-password/delete all happen via DaisyUI `<dialog>` modals on the one page) rather than separate create/edit pages — see [08-frontend.md](08-frontend.md). As of the DataTables pass, the index table itself loads via `users.data`, and the edit/reset-password/delete modals are a single shared instance each, populated by JS from the clicked row (ajax-rendered rows can't carry a pre-built modal per row).

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/users` | `users.index` | `UserController@index` | `users.view` | List page shell + per-role stats — the table loads via `users.data` |
| GET | `/users/data` | `users.data` | `UserController@data` | `users.view` | **Ajax source for the users DataTable** — role filter + name/email search |
| POST | `/users` | `users.store` | `UserController@store` | `users.create` | Create (name, email, password, one role) |
| PUT | `/users/{user}` | `users.update` | `UserController@update` | `users.update` | Update name/email/role |
| PUT | `/users/{user}/password` | `users.reset-password` | `UserController@resetPassword` | `users.update` | Admin sets a new password for the user (no current-password check, unlike self-service) |
| DELETE | `/users/{user}` | `users.destroy` | `UserController@destroy` | `users.delete` | Delete — blocked server-side if `{user}` is the acting user (can't delete yourself) |

## Roles

New in Phase 4b. Full `Route::resource` + a confirm-delete page, same pattern as Buildings/Machines. Business rules (admin protected, can't delete a role with users, can't drop your own `roles.update`) live in `RoleService` and throw `RoleActionException`, which the controller catches and turns into a flashed error — see [06-security.md](06-security.md). The index table is a **client-side** DataTable (roles are a small, fixed-size list — all rows render in the initial page load, no ajax endpoint) — see [02-architecture.md](02-architecture.md) for when server-side vs. client-side applies.

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/roles` | `roles.index` | `RoleController@index` | `roles.view` | List roles with user/permission counts, plus an "Add Users" action per row |
| GET | `/roles/create` | `roles.create` | `RoleController@create` | `roles.create` | Create form (name + permission matrix) |
| POST | `/roles` | `roles.store` | `RoleController@store` | `roles.create` | Create |
| GET | `/roles/{role}` | `roles.show` | `RoleController@show` | `roles.view` | Detail: read-only permission matrix + users holding this role |
| GET | `/roles/{role}/edit` | `roles.edit` | `RoleController@edit` | `roles.update` | Edit form — redirects to `roles.show` with an error if `{role}` is `admin` |
| PUT/PATCH | `/roles/{role}` | `roles.update` | `RoleController@update` | `roles.update` | Update name/permissions |
| POST | `/roles/{role}/users` | `roles.assign-users` | `RoleController@assignUsers` | `roles.update` | **Assign existing users to this role** — since a user holds exactly one role, this moves each selected user off whatever role they currently have. Blocked if the acting user's own id is among those selected (reassign yourself from the Users page instead). Triggered from the "Add Users" button on `roles.index`. |
| DELETE | `/roles/{role}` | `roles.destroy` | `RoleController@destroy` | `roles.delete` | Delete — blocked if `admin` or if any user still holds the role |
| GET | `/roles/{role}/delete` | `roles.confirm-delete` | `RoleController@confirmDelete` | `roles.delete` | Confirmation page; shows the blocking user list when not deletable |

## Permissions

New in Phase 4b. Read-only — there is no create/update/delete, since a permission only does something once code checks it (see [02-architecture.md](02-architecture.md) for how permissions gate controllers).

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/permissions` | `permissions.index` | `PermissionController@index` | `permissions.view` | Every permission, grouped by module, with the roles that hold each one |

## Alerts

New in Phase 5. Same controller-constructor permission-gating pattern; `alerts.acknowledge`/`alerts.resolve` are their own permissions rather than part of a CRUD set, since alerts are raised by the system (`AlertService::evaluate()`), never manually created or deleted — see [06-security.md](06-security.md). The index table is a **server-side** DataTable (grows with every sensor reading).

| Method | URI | Name | Controller@Method | Permission | Purpose |
|---|---|---|---|---|---|
| GET | `/alerts` | `alerts.index` | `AlertController@index` | `alerts.view` | List page shell + stat tiles — the table loads via `alerts.data` |
| GET | `/alerts/data` | `alerts.data` | `AlertController@data` | `alerts.view` | **Ajax source for the alerts DataTable** — status/severity/machine filters |
| POST | `/alerts/{alert}/acknowledge` | `alerts.acknowledge` | `AlertController@acknowledge` | `alerts.acknowledge` | Mark an open alert as being handled |
| POST | `/alerts/{alert}/resolve` | `alerts.resolve` | `AlertController@resolve` | `alerts.resolve` | Manually resolve an open or acknowledged alert (readings back within bounds also auto-resolve via `AlertService::evaluate()`) |

The same acknowledge/resolve actions are also available inline on `machines/show`'s "Active Alerts" card, scoped to that one machine.

## Notifications

Backs the header bell. No permission gate beyond `auth` — a route only ever acts on `auth()->user()`'s own notifications, never another user's. Thin wrapper over Laravel's own Notifiable API (see [08-frontend.md](08-frontend.md)) — no interface/service, since there's no domain logic to abstract.

| Method | URI | Name | Controller@Method | Purpose |
|---|---|---|---|---|
| POST | `/notifications/{id}/read` | `notifications.read` | `NotificationController@read` | Marks one notification read, then redirects to the machine it's about (or the alerts index) |
| POST | `/notifications/read-all` | `notifications.read-all` | `NotificationController@readAll` | Marks all of the current user's notifications read |

## Profile

Every authenticated user can manage their own account regardless of role — no permission gate, since these routes only ever act on `auth()->user()`, never another user's record.

| Method | URI | Name | Controller@Method | Purpose |
|---|---|---|---|---|
| GET | `/profile` | `profile.index` | `ProfileController@index` | View/edit own name, email; change own password |
| PUT | `/profile` | `profile.update` | `ProfileController@update` | Update own name/email |
| PUT | `/profile/password` | `profile.update-password` | `ProfileController@updatePassword` | Change own password (requires current password) |

## AI Insights

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/ai/questions/{machine}` | `ai.questions` | `AIController@getSuggestedQuestions` | `auth` | Context-aware suggested questions for the chat UI |
| POST | `/ai/chat` | `ai.chat` | `AIController@chat` | `auth` | Dispatch an AI analysis job, returns a cache key |
| POST | `/ai/check-status` | `ai.check-status` | `AIController@checkStatus` | `auth` | Poll for the job's result |

These live in `routes/web.php`, not `routes/api.php`, because they're called from a Blade view and need the `web` middleware group (session auth + CSRF). See [07-ai-insights.md](07-ai-insights.md).

## Other module pages (currently static views — see [01-overview.md](01-overview.md) for status)

| Method | URI | Name | Controller@Method |
|---|---|---|---|
| GET | `/work-orders` | `work-orders.index` | `WorkOrderController@index` |
| GET | `/reports` | `reports.index` | `ReportController@index` |
| GET | `/maintenance` | `maintenance.index` | `MaintenanceController@index` |
| GET | `/settings` | `settings.index` | `SettingController@index` |

All require `auth`; no permission middleware yet — these modules don't have real CRUD to gate until their respective phases (5–10) build them out.

## Hardware / sensor ingestion API (`routes/api.php`)

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| POST | `/api/machine-logs` | `api.machine-logs.store` | `API\MachineLogController@store` | `device.auth`, `throttle:120,1` | Sensor devices POST readings here. **No user session involved** — authenticated purely via the `X-Device-Key` header. See [05-device-api.md](05-device-api.md). |
| GET | `/api/user` | – | closure | `auth:sanctum` | Returns the authenticated Sanctum user (currently unused by the frontend) |

## Framework routes

| Method | URI | Purpose |
|---|---|---|
| GET | `/up` | Laravel health check |
| GET | `/storage/{path}` | Public storage symlink (building images, etc.) |
| GET | `/sanctum/csrf-cookie` | Sanctum SPA CSRF cookie endpoint (unused by this app currently) |
