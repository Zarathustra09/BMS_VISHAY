# Changelog

Entries are added phase by phase (see [10-conventions.md](10-conventions.md) for the workflow this follows). Newest first.

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
