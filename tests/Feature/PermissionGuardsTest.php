<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionGuardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        config(['modules.buildings' => true, 'modules.work-orders' => true]);
    }

    /** @param  array<int, string>  $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'role-'.uniqid()])->givePermissionTo($permissions));

        return $user;
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_the_ai_endpoints_require_machines_view(): void
    {
        $machine = Machine::factory()->create();
        $user = $this->userWithPermissions([]);

        $this->actingAs($user)->getJson(route('ai.questions', $machine))->assertForbidden();
        $this->actingAs($user)->postJson(route('ai.chat'), ['machine_id' => $machine->id, 'message' => 'hi'])->assertForbidden();
        $this->actingAs($user)->postJson(route('ai.check-status'), ['cache_key' => 'ai_insight_'.fake()->uuid()])->assertForbidden();
    }

    public function test_an_ai_answer_can_only_be_collected_by_the_user_who_asked(): void
    {
        Queue::fake();
        $machine = Machine::factory()->create();
        $asker = $this->userWithPermissions(['machines.view']);
        $other = $this->userWithPermissions(['machines.view']);

        $key = $this->actingAs($asker)
            ->postJson(route('ai.chat'), ['machine_id' => $machine->id, 'message' => 'How is it?'])
            ->assertOk()
            ->json('cache_key');

        $this->actingAs($asker)->postJson(route('ai.check-status'), ['cache_key' => $key])->assertOk();
        $this->actingAs($other)->postJson(route('ai.check-status'), ['cache_key' => $key])->assertNotFound();
    }

    public function test_check_status_refuses_arbitrary_cache_keys(): void
    {
        Cache::put('settings_secret', ['ready' => true, 'message' => 'secret'], 60);
        $user = $this->userWithPermissions(['machines.view']);

        $this->actingAs($user)
            ->postJson(route('ai.check-status'), ['cache_key' => 'settings_secret'])
            ->assertUnprocessable();
    }

    public function test_assigning_users_to_a_role_also_requires_users_update(): void
    {
        $target = Role::create(['name' => 'target']);
        $user = $this->userWithPermissions(['roles.view', 'roles.update']);

        $this->actingAs($user)
            ->post(route('roles.assign-users', $target), ['user_ids' => [$user->id]])
            ->assertForbidden();
    }

    public function test_regenerating_a_device_key_also_requires_machines_update(): void
    {
        $machine = Machine::factory()->create();
        $user = $this->userWithPermissions(['machines.view', 'machines.view-device-key']);

        $this->actingAs($user)
            ->post(route('machines.device-key.generate', $machine))
            ->assertForbidden();
    }

    public function test_a_technician_does_not_see_floor_management_buttons(): void
    {
        $building = Building::factory()->create();
        Floor::create(['building_id' => $building->id, 'name' => 'Ground', 'floor_number' => 0, 'status' => 'active']);

        $response = $this->actingAs($this->userWithRole('technician'))->get(route('buildings.show', $building));

        $response->assertOk()
            ->assertSee('onclick="viewFloorDetails(', false)
            ->assertDontSee('Add Floor')
            ->assertDontSee('onclick="openEditFloorModal(', false)
            ->assertDontSee('onclick="openDeleteFloorModal(', false)
            ->assertDontSee('id="createFloorModal"', false);

        $this->actingAs($this->userWithRole('admin'))->get(route('buildings.show', $building))
            ->assertSee('Add Floor')
            ->assertSee('onclick="openEditFloorModal(', false)
            ->assertSee('onclick="openDeleteFloorModal(', false);
    }

    public function test_a_manager_editing_does_not_get_delete_links_they_cannot_use(): void
    {
        $manager = $this->userWithRole('manager');
        $building = Building::factory()->create();
        $type = MachineType::factory()->create();

        $this->actingAs($manager)->get(route('buildings.edit', $building))
            ->assertOk()
            ->assertDontSee(route('buildings.confirm-delete', $building), false);

        $this->actingAs($manager)->get(route('machine-types.edit', $type))
            ->assertOk()
            ->assertDontSee(route('machine-types.confirm-delete', $type), false);

        $this->actingAs($this->userWithRole('admin'))->get(route('buildings.edit', $building))
            ->assertSee(route('buildings.confirm-delete', $building), false);
    }

    public function test_the_dashboard_hides_cards_the_user_has_no_permission_for(): void
    {
        $user = $this->userWithPermissions(['machines.view']);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertDontSee('Open Alerts')
            ->assertDontSee('Recent Alerts')
            ->assertDontSee('Pending Work Orders')
            ->assertDontSee('Recent Work Orders')
            ->assertDontSee('Quick Actions')
            ->assertDontSee('Building Health');
    }

    public function test_the_sidebar_only_lists_modules_the_user_can_open(): void
    {
        $user = $this->userWithPermissions(['alerts.view']);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('machines.index'), false)
            ->assertSee(route('alerts.index'), false);
    }

    public function test_the_active_alerts_card_on_a_machine_needs_alerts_view(): void
    {
        $machine = Machine::factory()->create();
        $machine->alerts()->create([
            'type' => 'threshold', 'metric' => 'temperature', 'severity' => 'critical',
            'status' => 'open', 'message' => 'Temperature too high', 'value' => 99,
        ]);

        $this->actingAs($this->userWithPermissions(['machines.view']))
            ->get(route('machines.show', $machine))
            ->assertOk()
            ->assertDontSee('Active Alerts');

        $this->actingAs($this->userWithPermissions(['machines.view', 'alerts.view']))
            ->get(route('machines.show', $machine))
            ->assertSee('Active Alerts');
    }
}
