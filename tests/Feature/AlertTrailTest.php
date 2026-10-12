<?php

namespace Tests\Feature;

use App\Interfaces\AlertInterface;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\WorkOrderInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlertTrailTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->machine = Machine::factory()->create();
        MachineParameter::factory()->for($this->machine)->key('temperature')
            ->withThresholds(['warning_max' => 30, 'critical_max' => 40])->create();
    }

    private function reading(float $temperature): void
    {
        app(MachineLogInterface::class)->store([
            'machine_id' => $this->machine->id,
            'log_type' => 'sensor_reading',
            'readings' => ['temperature' => $temperature],
        ]);
    }

    private function admin(): User
    {
        return tap(User::factory()->create(['name' => 'Ada Admin']))->assignRole('admin');
    }

    /** @return array<int, string> */
    private function actions(Alert $alert): array
    {
        return $alert->events()->reorder('id')->pluck('action')->all();
    }

    public function test_raising_and_escalating_are_recorded_as_system_events_with_the_reading(): void
    {
        $this->reading(35);
        $alert = Alert::sole();

        $this->reading(45);

        $this->assertSame(['raised', 'escalated'], $this->actions($alert));
        $this->assertNull($alert->events()->reorder('id')->first()->user_id);
        $this->assertEquals(35, $alert->events()->reorder('id')->first()->value);
        $this->assertEquals(45, $alert->events()->reorder('id')->get()[1]->value);
        $this->assertSame('System', $alert->events()->reorder('id')->first()->actorName());
    }

    public function test_acknowledging_records_who_did_it(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert))->assertRedirect();

        $event = $alert->events()->first();
        $this->assertSame('acknowledged', $event->action);
        $this->assertSame($admin->id, $event->user_id);
        $this->assertSame('open', $event->from_status);
        $this->assertSame('acknowledged', $event->to_status);
        $this->assertSame($admin->id, $alert->fresh()->acknowledged_by);
    }

    public function test_a_manual_resolve_records_who_resolved_it(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('alerts.resolve', $alert))->assertRedirect();

        $alert = $alert->fresh();
        $this->assertSame($admin->id, $alert->resolved_by);
        $this->assertSame('manual', $alert->resolution);
        $this->assertSame('resolved', $alert->events()->first()->action);
        $this->assertSame($admin->id, $alert->events()->first()->user_id);
    }

    public function test_a_reading_back_in_range_auto_resolves_and_the_system_gets_the_credit(): void
    {
        $this->reading(45);
        $alert = Alert::sole();

        $this->reading(22);

        $alert = $alert->fresh();
        $this->assertSame('resolved', $alert->status);
        $this->assertNull($alert->resolved_by);
        $this->assertSame('auto', $alert->resolution);
        $this->assertSame(['raised', 'auto_resolved'], $this->actions($alert));
        $this->assertEquals(22, $alert->events()->first()->value);
    }

    public function test_completing_a_work_order_records_the_person_and_the_work_order_path(): void
    {
        config(['modules.work-orders' => true]);
        $this->reading(45);
        $alert = Alert::sole();
        $manager = tap(User::factory()->create())->assignRole('manager');

        $workOrders = app(WorkOrderInterface::class);
        $workOrder = $workOrders->create([
            'title' => 'Investigate overheating',
            'machine_id' => $this->machine->id,
            'alert_id' => $alert->id,
            'type' => 'corrective',
            'priority' => 'high',
        ], $manager);
        $workOrders->updateStatus($workOrder, 'completed', $manager, 'Replaced the sensor.');

        $alert = $alert->fresh();
        $this->assertSame($manager->id, $alert->resolved_by);
        $this->assertSame('work_order', $alert->resolution);
        $this->assertSame('resolved_by_work_order', $alert->events()->first()->action);
    }

    public function test_an_offline_alert_and_the_device_coming_back_are_both_recorded(): void
    {
        $this->machine->forceFill(['last_seen_at' => now()->subHour()])->save();

        app(AlertInterface::class)->raiseOfflineAlerts(10);
        $alert = Alert::where('type', 'offline')->sole();

        $this->reading(20);

        $this->assertSame(['raised', 'device_back_online'], $this->actions($alert));
        $this->assertSame('resolved', $alert->fresh()->status);
    }

    public function test_the_alert_page_shows_the_trail_with_names(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert));

        $this->actingAs($admin)->get(route('alerts.show', $alert))
            ->assertOk()
            ->assertSee('Ada Admin')
            ->assertSee('Acknowledged')
            ->assertSee('Raised')
            ->assertSee('System');
    }

    public function test_the_machine_page_shows_the_alert_trail_only_to_those_who_can_view_alerts(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert));

        $this->actingAs($admin)->get(route('machines.show', $this->machine))
            ->assertOk()
            ->assertSee('Alert trail')
            ->assertSee('Ada Admin');

        $noAlerts = User::factory()->create();
        $noAlerts->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));

        $this->actingAs($noAlerts)->get(route('machines.show', $this->machine))
            ->assertOk()
            ->assertDontSee('Alert trail')
            ->assertDontSee('Active Alerts');
    }

    public function test_the_alert_page_and_live_endpoints_need_alerts_view(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));

        $this->actingAs($user)->get(route('alerts.show', $alert))->assertForbidden();
        $this->actingAs($user)->getJson(route('alerts.stats'))->assertForbidden();
        $this->actingAs($user)->get(route('machines.alerts-panel', $this->machine))->assertForbidden();
        $this->actingAs($user)->getJson(route('home.alerts-live'))->assertForbidden();
    }

    public function test_the_live_endpoints_return_fresh_data(): void
    {
        $this->reading(45);
        $admin = $this->admin();

        $this->actingAs($admin)->getJson(route('alerts.stats'))
            ->assertOk()
            ->assertJson(['open' => 1, 'critical' => 1]);

        $this->actingAs($admin)->get(route('machines.alerts-panel', $this->machine))
            ->assertOk()
            ->assertSee('Active Alerts')
            ->assertSee('Alert trail');

        $this->actingAs($admin)->getJson(route('home.alerts-live'))
            ->assertOk()
            ->assertJson(['open' => 1, 'critical' => 1])
            ->assertJsonStructure(['recent_html']);
    }

    public function test_the_alerts_table_says_who_acknowledged_and_who_resolved(): void
    {
        $this->reading(45);
        $alert = Alert::sole();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert));

        $rows = $this->actingAs($admin)->getJson(route('alerts.data'))->assertOk()->json('data');
        $this->assertStringContainsString('by Ada Admin', $rows[0]['status']);

        $this->actingAs($admin)->post(route('alerts.resolve', $alert));

        $rows = $this->actingAs($admin)->getJson(route('alerts.data'))->json('data');
        $this->assertStringContainsString('by Ada Admin', $rows[0]['status']);
    }

    public function test_a_notification_opens_the_alerts_trail(): void
    {
        $admin = $this->admin();
        $this->reading(45);
        $alert = Alert::sole();
        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($admin)->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('alerts.show', $alert));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
