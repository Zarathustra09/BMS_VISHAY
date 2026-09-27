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

`database/factories/` currently has `UserFactory`, `BuildingFactory`, `MachineFactory`, and `MachineLogFactory`. `Machine::factory()` creates its own `Building` by default (`'building_id' => Building::factory()`) unless you pass one explicitly. There's no `WorkOrderFactory` or `AlertFactory` — both are created through their **service**, not the model directly, in existing tests (`app(WorkOrderInterface::class)->create(...)`, or by calling `Alert::create()` with every required column, or by storing a `MachineLog` that breaches a `SensorThreshold` and letting `AlertService::evaluate()` raise it). Creating through the service is preferred where one exists: it exercises the same code path production traffic does (code generation, notifications, etc.) instead of just the schema.

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
$plainKey = $machine->generateDeviceKey();

$this->postJson('/api/machine-logs', [
    'log_type' => 'sensor_reading',
    'temperature' => 45,
], ['X-Device-Key' => $plainKey])->assertStatus(201);
```

## What's covered so far

| Test file | Covers |
|---|---|
| `tests/Feature/DeviceAuthTest.php` | Valid key accepted (201), missing/invalid key rejected (401), a device can only write logs for its own machine |
| `tests/Feature/DeviceKeyPermissionTest.php` | `machines.view-device-key` gates generating/viewing a device key; the machine page hides the key card without it |
| `tests/Feature/ThresholdAlertNotificationTest.php` | A breaching reading raises an alert and notifies admins; a second breaching reading does **not** raise a duplicate; a reading back within bounds auto-resolves the alert |
| `tests/Feature/PermissionDeniedTest.php` | 403s for a viewer creating a building / deleting a machine, a technician opening Settings or Roles; a guest is redirected to login instead of 403'd; an admin can reach Settings |
| `tests/Feature/WorkOrderCompletionTest.php` | Completing an alert-linked work order resolves the alert; completing a preventive work order advances the machine's maintenance dates; a technician cannot complete a work order assigned to someone else |

These aren't exhaustive coverage of every module — they're the specific flows named in the finalization plan's Phase 10 (device auth, key-view permission, threshold → alert → notification, no duplicate alerts, permission 403s, work order completion). Extend them, or add new files, as new business rules are added — see [10-conventions.md](10-conventions.md) for where new code should live so it's easy to find what to test.
