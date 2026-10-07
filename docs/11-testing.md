# Testing

## Running tests

```bash
php artisan test
```

Runs both suites defined in [phpunit.xml](../phpunit.xml): `tests/Unit` and `tests/Feature`. Run a single file or filter by name:

```bash
php artisan test tests/Feature/ThresholdAlertNotificationTest.php
php artisan test --filter=test_a_reading_past_the_critical_threshold
```

## Test environment

`phpunit.xml` overrides `.env` for every test run:

| Variable | Value | Why |
|---|---|---|
| `DB_CONNECTION` / `DB_DATABASE` | `sqlite` / `:memory:` | Fast, disposable — every test class that touches the database uses `RefreshDatabase` to get a clean schema per test |
| `CACHE_STORE` | `array` | In-process only; `SettingService`'s `Cache::rememberForever()` doesn't leak between test runs, but **does** persist across tests within the same class unless you call `Cache::forget()` or start from a fresh app instance (which `RefreshDatabase` alone does not give you — see below) |
| `QUEUE_CONNECTION` | `sync` | Queued jobs/notifications (`ProcessMachineAIInsight`, `MachineAlertNotification`, `WorkOrderAssignedNotification`) run inline instead of needing a worker |
| `MAIL_MAILER` | `array` | Mail is captured in memory, not sent |
| `SESSION_DRIVER` | `array` | No session file/DB writes |

Because `DB_DATABASE=:memory:`, **the SQLite schema must be buildable from the migrations alone** — no MySQL-specific SQL (`DATE_FORMAT`, etc.) in anything a test path might execute. This was a hard constraint from Phase 6 onward for the sensor chart's bucketing (see [02-architecture.md](02-architecture.md#c-a-sensor-reading-device--machine_logs)) and still applies to any new query.

## Writing a feature test

Extend `Tests\TestCase` and pull in `RefreshDatabase` for anything that touches the database:

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SomethingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_the_thing(): void
    {
        // ...
    }
}
```

### Roles & permissions

Most feature tests need real roles/permissions to exist (to `assignRole()` a user or to assert a `403`). Seed them explicitly in `setUp()` rather than the full `DatabaseSeeder` (which also creates sample buildings/floors this app's tests don't need):

```php
protected function setUp(): void
{
    parent::setUp();

    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
}
```

Then `$user->assignRole('admin')` (or `manager`/`technician`/`viewer`) works as it does in the app. See [06-security.md](06-security.md) for what each role can do — a test asserting a `403` should pick the role/permission combination that actually maps to the checked route today, not assume it.

### Factories

`database/factories/` currently has `UserFactory`, `BuildingFactory`, `MachineFactory`, `MachineLogFactory`, `MachineParameterFactory` (`key()`, `boolean()`, `text()`, `required()`, `range()`, `withThresholds()` states — a machine can only receive readings for parameters it has) and `MachineTypeFactory` (a `withFields(array $fields)` state seeds its `spec_fields`). `Machine::factory()` creates its own `Building` **and** its own `MachineType` by default (`'building_id' => Building::factory()`, `'machine_type_id' => MachineType::factory()`) unless you pass one explicitly — the seeded default types (Chiller, etc. — see `03-data-model.md`) are usually a better fit than a factory-generated type when a test cares about specific spec fields, since a plain `MachineType::factory()` has none. There's no `WorkOrderFactory` or `AlertFactory` — both are created through their **service**, not the model directly, in existing tests (`app(WorkOrderInterface::class)->create(...)`, or by calling `Alert::create()` with every required column, or by storing a `MachineLog` that breaches a `SensorThreshold` and letting `AlertService::evaluate()` raise it). Creating through the service is preferred where one exists: it exercises the same code path production traffic does (code generation, notifications, etc.) instead of just the schema — `MachineTypeCrudTest` follows this, creating test types via `app(MachineTypeInterface::class)->create(...)` rather than `MachineType::create()` directly.

If a model you need a factory for doesn't have `HasFactory` yet, add the trait to the model (see `Building`, `Machine`, `MachineLog` for the pattern) before writing `database/factories/{Model}Factory.php`.

### Testing notifications

`AlertService` and `WorkOrderService` send notifications synchronously (`QUEUE_CONNECTION=sync`), so without `Notification::fake()` they actually attempt to "send" (captured by `MAIL_MAILER=array`, harmless in tests but not asserting anything). To assert *who* got notified:

```php
use Illuminate\Support\Facades\Notification;

Notification::fake();

// ...trigger the code path...

Notification::assertSentTo($admin, MachineAlertNotification::class);
Notification::assertSentToTimes($admin, MachineAlertNotification::class, 1); // dedupe check
```

### Testing permission gates

Every CRUD controller gates its actions via `permission:` middleware in its constructor (or, for a couple of one-off actions like `machines.device-key.generate`, directly on the route — see [02-architecture.md](02-architecture.md#permission-gating-lives-on-the-controller-not-the-route)). A missing permission returns **403**, not a redirect — a guest (no session at all) gets redirected to `login` instead, since that's an authentication failure, not an authorization one. Don't conflate the two in an assertion:

```php
// authenticated but unauthorized
$this->actingAs($viewer)->post(route('buildings.store'), [...])->assertForbidden();

// no session at all
$this->get(route('buildings.index'))->assertRedirect(route('login'));
```

### Testing the device API

The sensor ingestion endpoint (`POST /api/machine-logs`) isn't session-authenticated — it needs an `X-Device-Key` header instead (see [05-device-api.md](05-device-api.md)):

```php
$machine = Machine::factory()->create();
MachineParameter::factory()->for($machine)->key('temperature')->create();
$plainKey = $machine->generateDeviceKey();

$this->postJson('/api/machine-logs', [
    'log_type' => 'sensor_reading',
    'readings' => ['temperature' => 45],
], ['X-Device-Key' => $plainKey])->assertStatus(201);
```

## AI agents querying the database

Laravel Boost's Database Query / Database Schema MCP tools (see [09-setup-and-operations.md](09-setup-and-operations.md#ai-agent-tooling-laravel-boost)) run against whatever connection is currently configured — that's the real dev database outside of a test run, **not** the `sqlite`/`:memory:` connection `phpunit.xml` uses. An agent reasoning about test data or debugging a failing test should read fixtures/factories and the test's own setup, not query the live database and assume it reflects what a test sees.

## What's covered so far

| Test file | Covers |
|---|---|
| `tests/Feature/MachineParameterReadingTest.php` | The sensor API against a machine's own parameters: typed storage, unknown key / wrong type / out-of-range / missing required → 422, required not enforced on non-sensor logs, a top-level legacy field → 422, alerts raised/resolved on a dynamic parameter, the chart series |
| `tests/Feature/MachineParameterBuilderTest.php` | The machine form's parameter builder: create/update/reorder/delete, key and data type immutable once saved, another machine's parameter id rejected, duplicate keys, validation, the copy-parameters JSON endpoint, the edit form seed, the API Integration card (never embeds the real key) |
| `tests/Feature/DeviceAuthTest.php` | Valid key accepted (201), missing/invalid key rejected (401), a device can only write logs for its own machine |
| `tests/Feature/DeviceKeyPermissionTest.php` | `machines.view-device-key` gates generating/viewing a device key; the machine page hides the key card without it |
| `tests/Feature/ThresholdAlertNotificationTest.php` | A breaching reading raises an alert and notifies admins; a second breaching reading does **not** raise a duplicate; a reading back within bounds auto-resolves the alert |
| `tests/Feature/PermissionDeniedTest.php` | 403s for a viewer creating a building / deleting a machine, a technician opening Settings or Roles; a guest is redirected to login instead of 403'd; an admin can reach Settings |
| `tests/Feature/WorkOrderCompletionTest.php` | Completing an alert-linked work order resolves the alert; completing a preventive work order advances the machine's maintenance dates; a technician cannot complete a work order assigned to someone else |
| `tests/Feature/MachineValidationTest.php` | Machine create/edit validation (invalid `machine_type_id`, invalid IP, cross-building floor, date ordering, unchecked-toggle persistence); a non-numeric value for a number spec field is rejected with its label in the message; a required spec field is enforced; another type's spec keys are stripped from what's actually stored; a select value outside its options, an invalid multiselect option, an invalid date, and a `javascript:` URL are each rejected; an unchecked boolean spec is stored as `false` and shown as "No"; a multiselect is stored as an array; the create page lists database-created machine types (proof the form is no longer enum-driven); the show page renders stored specs with their unit; the edit page prefills a machine's stored spec values; the activity feed renders after a spec change — including a multiselect/boolean change — without throwing; the create/edit pages render (regression guard for a fatal null-machine bug, and for the `@js()`-vs-`@json()` Alpine-seeding bug) |
| `tests/Feature/MachineTypeCrudTest.php` | Guest redirect and 403s (viewer create, manager delete); name required/unique; icon must be one of the predetermined `MachineTypeIcon` keys; a spec field's `type` must be one of `SpecFieldType`'s cases; a select/multiselect field with no options is rejected; a Number field's `max` below its own `min` is rejected; options are trimmed, de-duplicated, and stripped from a non-choice kind; a Boolean field's `required` is always stored as `false`; the seeded Generator's `autostart` field is `boolean` after the conversion migration; two fields whose labels would generate the same key are rejected; creating a type generates each field's key from its label; renaming a field's label keeps its key; a key that never belonged to the type is rejected; number-only attributes (unit/step/min) are stripped from a text field; the seeded default types hold icon keys, not emoji; the index page renders each type's icon as an SVG; an unused type can be deleted; a type used by a machine (including a soft-deleted one) can't be deleted; index/create/edit pages render |
| `tests/Feature/EditionModulesTest.php` | The lite edition (`config/modules.php`): each switched-off module's pages 404 even for admin, while the full edition still serves them; admin is denied a disabled module's abilities (guards the `Gate::before` registration order against Spatie's) but keeps the core ones; the sidebar and dashboard drop the disabled modules and keep Machines/Machine Types/Alerts/Users; the machine form has no Building/Floor selects, a posted building is ignored on create and an existing one is kept on edit; machine pages render without "Create Work Order"/Building rows; the role matrix hides disabled permissions and saving a role keeps the ones it already held; the sensor API still stores readings and raises alerts; the preventive-work-order job is skipped by the scheduler when Work Orders is off |

**Testing an edition.** Every other test file runs as the default full edition, which is what proves the toggles change nothing there. A test of a lite behavior flips the switches in the test itself (`config(['modules.work-orders' => false, …])` — see `EditionModulesTest::useLiteEdition()`); config is read at request/render time, so this takes effect immediately with no need to rebuild the app. Assert a module's absence from a page by its route URL (`assertDontSee(route('buildings.index'), false)`) rather than a label, and use `{{-- --}}` rather than `<!-- -->` for section comments around switchable UI, or the comment text leaks into the page and trips the assertion.

These aren't exhaustive coverage of every module — they're the specific flows named in the finalization plan's Phase 10 (device auth, key-view permission, threshold → alert → notification, no duplicate alerts, permission 403s, work order completion). Extend them, or add new files, as new business rules are added — see [10-conventions.md](10-conventions.md) for where new code should live so it's easy to find what to test.
