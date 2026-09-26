# Routes

Generated from `php artisan route:list` against the codebase as of Phase 3. All routes below (except `/`, `/login`, `/register`, `/password/*`, and the sensor ingestion API) require an authenticated session (`auth` middleware, applied via `Route::middleware('auth')->group()` in `routes/web.php`).

## Auth

| Method | URI | Name | Controller@Method | Middleware | Permission | Purpose |
|---|---|---|---|---|---|---|
| GET | `/` | – | closure | `web` | – | Login page for guests; redirects to `home` if already logged in |
| GET/POST | `/login` | `login` | `Auth\LoginController` | `guest` (RedirectIfAuthenticated) | – | Session login |
| POST | `/logout` | `logout` | `Auth\LoginController@logout` | `auth` | – | Session logout |
| GET/POST | `/register` | `register` | `Auth\RegisterController` | `guest` | – | Public registration (⚠ to be disabled in Phase 4) |
| GET/POST | `/password/confirm` | `password.confirm` | `Auth\ConfirmPasswordController` | `auth` | – | Re-confirm password for sensitive actions |
| POST | `/password/email` | `password.email` | `Auth\ForgotPasswordController` | – | – | Send reset link |
| GET | `/password/reset` | `password.request` | `Auth\ForgotPasswordController` | – | – | Request-reset form |
| GET | `/password/reset/{token}` | `password.reset` | `Auth\ResetPasswordController` | – | – | Reset form |
| POST | `/password/reset` | `password.update` | `Auth\ResetPasswordController` | – | – | Perform reset |

## Dashboard

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/home` | `home` | `HomeController@index` | `auth` | Main dashboard (currently hardcoded metrics — Phase 9) |

## Buildings

| Method | URI | Name | Controller@Method | Purpose |
|---|---|---|---|---|
| GET | `/buildings` | `buildings.index` | `BuildingController@index` | List + stats |
| GET | `/buildings/create` | `buildings.create` | `BuildingController@create` | Create form |
| POST | `/buildings` | `buildings.store` | `BuildingController@store` | Create |
| GET | `/buildings/{building}` | `buildings.show` | `BuildingController@show` | Detail |
| GET | `/buildings/{building}/edit` | `buildings.edit` | `BuildingController@edit` | Edit form |
| PUT/PATCH | `/buildings/{building}` | `buildings.update` | `BuildingController@update` | Update |
| DELETE | `/buildings/{building}` | `buildings.destroy` | `BuildingController@destroy` | Delete (requires typed confirmation + 3 acknowledgement checkboxes — see [08-frontend.md](08-frontend.md)) |
| GET | `/buildings/{building}/delete` | `buildings.confirm-delete` | `BuildingController@confirmDelete` | Confirmation page |

All require `auth`; none require a specific permission yet (Phase 4 adds `buildings.manage`).

## Floors

Defined under `Route::prefix('floors')->name('floors.')`. JSON-only endpoints, consumed by JS in the machine create/edit forms (not full-page views).

| Method | URI | Name | Controller@Method | Purpose |
|---|---|---|---|---|
| GET | `/floors` | `floors.floors.index` ⚠ | `FloorController@index` | List floors (optionally filtered by `?building_id=`) as JSON |
| POST | `/floors` | `floors.store` | `FloorController@store` | Create (JSON) |
| GET | `/floors/{floor}` | `floors.show` | `FloorController@show` | Detail (JSON) |
| PUT | `/floors/{floor}` | `floors.update` | `FloorController@update` | Update (JSON) |
| DELETE | `/floors/{floor}` | `floors.destroy` | `FloorController@destroy` | Delete (JSON) |

⚠ **Known issue**: the index route resolves to the name `floors.floors.index` (the `Route::prefix(...)->name('floors.')` group prefix combines with the route's own `->name('floors.index')`). It works — nothing currently calls `route('floors.index')` — but the double prefix is confusing. Worth a one-line fix in a future cleanup phase.

## Machines

| Method | URI | Name | Controller@Method | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/machines` | `machines.index` | `MachineController@index` | `auth` | List + filters + stats |
| GET | `/machines/create` | `machines.create` | `MachineController@create` | `auth` | Create form |
| POST | `/machines` | `machines.store` | `MachineController@store` | `auth` | Create |
| GET | `/machines/{machine}` | `machines.show` | `MachineController@show` | `auth` | Detail + logs + device key card |
| GET | `/machines/{machine}/edit` | `machines.edit` | `MachineController@edit` | `auth` | Edit form |
| PUT/PATCH | `/machines/{machine}` | `machines.update` | `MachineController@update` | `auth` | Update |
| DELETE | `/machines/{machine}` | `machines.destroy` | `MachineController@destroy` | `auth` | Delete |
| GET | `/machines/{machine}/delete` | `machines.confirm-delete` | `MachineController@confirmDelete` | `auth` | Confirmation page |
| POST | `/machines/{machine}/device-key` | `machines.device-key.generate` | `MachineController@generateDeviceKey` | `auth` + **`permission:machines.view-device-key`** | Generate/regenerate this machine's sensor device API key |

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
| GET | `/profile` | `profile.index` | `ProfileController@index` |
| GET | `/users` | `users.index` | `UserController@index` |

All require `auth`; none have permission middleware yet (Phase 4).

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
