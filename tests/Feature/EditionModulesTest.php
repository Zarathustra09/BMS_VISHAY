<?php

namespace Tests\Feature;

use App\Interfaces\RoleInterface;
use App\Models\Building;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\SensorThreshold;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The "lite" edition (config/modules.php): machines, machine types, alerts, users/roles and the
 * sensor API stay; Buildings & Floors, Work Orders, Maintenance and Reports are switched off.
 * Every other test file runs as the default full edition, which is what proves the toggles
 * change nothing there.
 */
class EditionModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function useLiteEdition(): void
    {
        config([
            'modules.buildings' => false,
            'modules.work-orders' => false,
            'modules.maintenance' => false,
            'modules.reports' => false,
        ]);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function machinePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Chiller Unit A',
            'machine_code' => 'MC-1001',
            'machine_type_id' => MachineType::where('name', 'Chiller')->value('id'),
            'status' => 'active',
        ], $overrides);
    }

    #[DataProvider('switchedOffRoutes')]
    public function test_a_switched_off_modules_pages_return_404_even_for_admin(string $routeName): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();

        $this->get(route($routeName))->assertNotFound();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function switchedOffRoutes(): array
    {
        return [
            'buildings' => ['buildings.index'],
            'work orders' => ['work-orders.index'],
            'maintenance' => ['maintenance.index'],
            'reports' => ['reports.index'],
        ];
    }

    public function test_the_full_edition_still_serves_every_module(): void
    {
        $this->actingAsAdmin();

        foreach (['buildings.index', 'work-orders.index', 'maintenance.index', 'reports.index'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_is_denied_a_switched_off_modules_abilities_but_keeps_the_core_ones(): void
    {
        $this->useLiteEdition();
        $admin = $this->actingAsAdmin();

        // Spatie's own Gate::before would grant these to an admin who holds every permission —
        // this proves ours runs first and wins.
        $this->assertFalse(Gate::forUser($admin)->allows('work-orders.create'));
        $this->assertFalse(Gate::forUser($admin)->allows('buildings.view'));
        $this->assertFalse(Gate::forUser($admin)->allows('floors.view'));
        $this->assertFalse(Gate::forUser($admin)->allows('maintenance.view'));
        $this->assertFalse(Gate::forUser($admin)->allows('reports.view'));

        $this->assertTrue(Gate::forUser($admin)->allows('machines.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('machine-types.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('alerts.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('users.view'));
    }

    public function test_the_sidebar_hides_switched_off_modules_and_keeps_the_core_ones(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();

        $response = $this->get(route('home'));

        $response->assertOk();

        foreach (['buildings.index', 'work-orders.index', 'maintenance.index', 'reports.index'] as $routeName) {
            $response->assertDontSee(route($routeName), false);
        }

        foreach (['machines.index', 'machine-types.index', 'alerts.index', 'users.index', 'roles.index'] as $routeName) {
            $response->assertSee(route($routeName), false);
        }
    }

    public function test_the_dashboard_renders_without_building_or_work_order_sections(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Building Health');
        $response->assertDontSee('Pending Work Orders');
        $response->assertDontSee('Recent Work Orders');
        $response->assertSee('Open Alerts');
        $response->assertSee('Recent Alerts');
    }

    public function test_the_machine_form_has_no_building_or_floor_selects(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();

        $response = $this->get(route('machines.create'));

        $response->assertOk();
        $response->assertDontSee('name="building_id"', false);
        $response->assertDontSee('name="floor_id"', false);
        $response->assertSee('name="machine_type_id"', false);
    }

    public function test_a_posted_building_is_ignored_when_buildings_is_switched_off(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();
        $building = Building::factory()->create();

        $this->post(route('machines.store'), $this->machinePayload(['building_id' => $building->id]));

        $this->assertNull(Machine::sole()->building_id);
    }

    public function test_editing_a_machine_keeps_its_existing_building_when_buildings_is_switched_off(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();
        $original = Building::factory()->create();
        $other = Building::factory()->create();
        $machine = Machine::factory()->create(['building_id' => $original->id]);

        $this->put(route('machines.update', $machine), $this->machinePayload([
            'name' => 'Renamed',
            'building_id' => $other->id,
        ]));

        $machine->refresh();
        $this->assertSame('Renamed', $machine->name);
        $this->assertSame($original->id, $machine->building_id);
    }

    public function test_machine_pages_render_without_work_order_links(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();
        $machine = Machine::factory()->create();

        $show = $this->get(route('machines.show', $machine));
        $show->assertOk();
        $show->assertDontSee('Create Work Order');
        $show->assertDontSee('Building:');

        $this->get(route('machines.index'))->assertOk();
        $this->get(route('machines.edit', $machine))->assertOk();
        $this->get(route('machines.confirm-delete', $machine))->assertOk();
    }

    public function test_the_role_matrix_hides_switched_off_permissions(): void
    {
        $this->useLiteEdition();
        $this->actingAsAdmin();

        $response = $this->get(route('roles.create'));

        $response->assertOk();
        $response->assertDontSee('value="work-orders.create"', false);
        $response->assertDontSee('value="buildings.view"', false);
        $response->assertDontSee('value="reports.view"', false);
        $response->assertSee('value="machines.create"', false);
    }

    public function test_saving_a_role_keeps_the_permissions_it_holds_for_a_switched_off_module(): void
    {
        $this->useLiteEdition();
        $admin = $this->actingAsAdmin();
        $role = Role::create(['name' => 'supervisor']);
        $role->syncPermissions(['machines.view', 'machines.update', 'work-orders.view', 'buildings.view']);

        // The matrix never lists work-orders/buildings permissions in lite, so they're absent
        // from what the form posts — saving must not strip them.
        app(RoleInterface::class)->update($role, 'supervisor', ['machines.view'], $admin);

        $role = $role->fresh();
        $this->assertTrue($role->hasPermissionTo('machines.view'));
        $this->assertFalse($role->hasPermissionTo('machines.update'));
        $this->assertTrue($role->hasPermissionTo('work-orders.view'));
        $this->assertTrue($role->hasPermissionTo('buildings.view'));
    }

    public function test_the_sensor_api_still_stores_readings_and_raises_alerts(): void
    {
        $this->useLiteEdition();
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $machine = Machine::factory()->create();
        SensorThreshold::create(['machine_id' => $machine->id, 'metric' => 'temperature', 'critical_max' => 40]);
        $plainKey = $machine->generateDeviceKey();

        $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'temperature' => 45,
        ], ['X-Device-Key' => $plainKey])->assertStatus(201);

        $this->assertDatabaseHas('machine_logs', ['machine_id' => $machine->id, 'temperature' => 45]);
        $this->assertDatabaseHas('alerts', ['machine_id' => $machine->id, 'metric' => 'temperature', 'status' => 'open']);
    }

    public function test_the_preventive_work_order_job_only_runs_when_work_orders_is_enabled(): void
    {
        // Loads routes/console.php, which is where the schedule is defined.
        Artisan::call('list');

        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command, 'app:generate-preventive-work-orders'));

        $this->assertNotNull($event);
        $this->assertTrue($event->filtersPass($this->app));

        $this->useLiteEdition();

        $this->assertFalse($event->filtersPass($this->app));
    }
}
