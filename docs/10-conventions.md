# Conventions

## Naming

- **Interfaces**: `{Noun}Interface` in `app/Interfaces` (e.g. `MachineInterface`, `AIInterface`). No `I` prefix.
- **Services**: `{Noun}Service` in `app/Services`, one per interface (e.g. `MachineService implements MachineInterface`). Where a module has multiple interchangeable implementations (like AI providers), name them after the provider, not generically (`OpenAIService`, `GeminiService`, both `implements AIInterface`).
- **Shared logic between services**: a trait in `app/Services/Concerns/` (e.g. `BuildsMachineContext`), not a base class — services should implement their interface directly, not inherit from a shared abstract.
- **Controllers**: `{Noun}Controller` in `app/Http/Controllers`, or `app/Http/Controllers/API/{Noun}Controller` for API-only controllers (e.g. `API\MachineLogController`).
- **Routes**: dot-notation names matching the resource (`machines.index`, `machines.show`), sub-actions appended (`machines.confirm-delete`, `machines.device-key.generate`). API route names get an `api.` prefix (`api.machine-logs.store`) — this only applies when the route genuinely lives in `routes/api.php`; don't prefix a `routes/web.php` route with `api.` just because it returns JSON (the AI chat routes return JSON but live in `web.php` and are named plainly, e.g. `ai.chat`).
- **Permissions**: `{module}.{action}` (e.g. `machines.view-device-key`, and the planned `buildings.manage`, `work-orders.update-status`).
- **Migrations**: Laravel's default timestamp-prefixed naming. A migration that adds columns to an existing table is named `add_{column(s)}_to_{table}_table` and only ever adds/removes what its name says.

## Folder layout

```
app/
  Console/Commands/     — custom artisan commands (app:process-queue, etc.)
  Enums/                 — backed enums (MachineStatus, MachineTypeIcon, SpecFieldType, AppModule)
  Exceptions/            — domain exceptions a service throws and its controller catches (RoleActionException, MachineTypeActionException)
  Http/
    Controllers/         — web controllers
    Controllers/API/     — API-only controllers
    Middleware/          — custom middleware (AuthenticateDevice, EnsureModuleEnabled)
  Interfaces/            — one file per module contract
  Jobs/                  — queued jobs
  Models/                — Eloquent models
  Providers/             — AppServiceProvider (all interface↔service bindings)
  Services/              — one file per module implementation
  Services/Concerns/     — traits shared between services
config/
  modules.php            — APP_EDITION / MODULE_* switches for the modules a deployment can turn off (see AppModule)
database/
  migrations/
  seeders/
resources/
  views/{module}/        — index/create/edit/show/delete per module (see 08-frontend.md)
  js/, css/, sass/
docs/                    — this documentation
```

## Migration rules

- **Never edit a migration that may already have run** against any environment (yours, Heal's, production). If a table needs to change, write a new `add_*`/`modify_*` migration.
- One logical change per migration — don't bundle an unrelated table's changes into a migration named for something else.
- Add explicit indexes for any column you'll filter/sort/join on (see `machines` and `machine_logs` for examples of intentional indexing).
- Prefer `nullable()` for optional attributes over defaulting to empty string — the codebase consistently treats "no value" as `null`, not `''`.

## Validation placement

Validation lives in the **controller**, using either `$request->validate([...])` inline or (for larger rule sets, as in `MachineController::validateMachine()`) a private helper method on the controller. This codebase does not currently use Form Request classes (`php artisan make:request`) — if a validation rule set grows large enough to want extracting, prefer a private controller method first, and only reach for a dedicated Form Request class if multiple controllers need the exact same rules.

Whatever the validated data is, pass it straight into the service method as an array — the service should not re-validate; validation is the controller's job, business rules are the service's job.

## Commit / phase workflow

This project is being built in **small, sequential phases**, each scoped to one coherent piece of work (see the project's plan document, not checked into this repo). Convention for each phase:

1. Implement the phase's changes.
2. Verify (lint, `route:list`, `php artisan test`, and a manual check of the specific behavior the phase added).
3. **Update every `docs/*.md` file affected by the change**, plus add a dated entry to [CHANGELOG.md](CHANGELOG.md).
4. Stop and let the change be reviewed/tested/committed before starting the next phase.

Docs are updated in the *same* phase as the code change, not batched up for later — a doc that's one phase behind the code is already a source of confusion.

## AI agent rules

The conventions in this file are also mirrored, per-glob, into `.ai/rules/*.md` so an AI agent (see [09-setup-and-operations.md](09-setup-and-operations.md#ai-agent-tooling-laravel-boost)) is pointed at the relevant rule before it edits a matching file, rather than needing this whole document loaded upfront. `.ai/rules/index.md` maps globs to rule files and is regenerated automatically — never hand-edit it or add a rule file by hand.

To add or change a rule, ask the agent to remember it (or use its `record-rule` tool directly) rather than editing `.ai/rules/*` yourself:

```
Remember that all money values are stored as integer cents, never as floats.
```

This file (`10-conventions.md`) stays the human-readable source of truth; `.ai/rules` is a derived, agent-facing index of the same conventions plus anything recorded since. If they drift, this file wins — update `.ai/rules` (via `record-rule`, not by hand) to match.

## Docs-update rule (what "affected" means)

When you change something, update the doc that describes it:

| You changed... | Update |
|---|---|
| A route, its middleware, or its name | `04-routes.md` |
| A table's columns, a model's relationships/casts | `03-data-model.md` |
| An interface, a service, a container binding | `02-architecture.md` registry table |
| A permission or role | `06-security.md` |
| The sensor payload/validation rules | `05-device-api.md` |
| AI provider/prompt/context logic | `07-ai-insights.md` |
| A Blade convention or shared UI pattern | `08-frontend.md` |
| A console command, env var, or deployment step | `09-setup-and-operations.md` |
| A module's status (planned → in progress → done) | `01-overview.md` |
| Anything at all | `CHANGELOG.md` |
