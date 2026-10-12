# LogXense Enterprise — Documentation

This is the technical documentation for LogXense Enterprise (formerly the NexBuild BMS), a Laravel 12 application for tracking buildings, floors, and industrial machines, ingesting sensor readings (temperature, humidity, vibration, power, etc.) from hardware devices, and (in later phases) alerting on abnormal readings and managing work orders.

## Reading order

If you're new to the project, read in this order:

1. **[01-overview.md](01-overview.md)** — what the system is, what's built vs. planned, glossary.
2. **[02-architecture.md](02-architecture.md)** — the Interface → Service → Controller pattern this codebase follows. Read this before writing any code.
3. **[03-data-model.md](03-data-model.md)** — database schema and model relationships.
4. **[04-routes.md](04-routes.md)** — every route in the app.

Then, as needed:

- **[05-device-api.md](05-device-api.md)** — if you're integrating a sensor/device (this is what Justin needs for the temperature sensor).
- **[06-security.md](06-security.md)** — auth, permissions, device keys.
- **[07-ai-insights.md](07-ai-insights.md)** — the AI chat/insights feature.
- **[08-frontend.md](08-frontend.md)** — Blade/Tailwind/DaisyUI/Alpine conventions.
- **[09-setup-and-operations.md](09-setup-and-operations.md)** — local setup, queue worker, scheduler, deployment.
- **[10-conventions.md](10-conventions.md)** — naming, folder layout, and the phase-based workflow this project is being built under.
- **[11-testing.md](11-testing.md)** — how to run the test suite and write new feature tests.
- **[CHANGELOG.md](CHANGELOG.md)** — what changed, phase by phase.

## Who this is for

- **New developers** joining the project: read 01 → 02 → 03 → 04 in full.
- **Hardware/firmware engineers** (e.g. wiring up a new sensor): read 05 only.
- **Anyone reviewing security**: read 06.
- **Anyone picking up the next phase of work**: read 10, then the CHANGELOG to see what's already done.
- **Anyone adding or running tests**: read 11.

## Keeping these docs accurate

Every phase of work on this project updates the docs files it touches, plus an entry in `CHANGELOG.md`, in the same commit as the code change. If you find a doc that's out of date, that's a bug — fix it in the same PR as whatever you were doing when you noticed.
