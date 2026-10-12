<?php

namespace Tests\Feature;

use App\Events\AlertChanged;
use App\Interfaces\MachineLogInterface;
use App\Models\Alert;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use App\Notifications\MachineAlertNotification;
use App\Notifications\WorkOrderAssignedNotification;
use App\Services\SafeBroadcaster;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LiveAlertBroadcastTest extends TestCase
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

    /** Same switch to the real driver as LiveReadingBroadcastTest, so channel signing runs locally. */
    private function useReverbDriver(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 1,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);

        app(BroadcastManager::class)->purge('reverb');

        require base_path('routes/channels.php');
    }

    public function test_every_alert_change_is_broadcast_on_the_alerts_and_machine_channels(): void
    {
        Event::fake([AlertChanged::class]);

        $this->reading(35);
        $this->reading(45);
        $alert = Alert::sole();
        $admin = tap(User::factory()->create(['name' => 'Ada Admin']))->assignRole('admin');
        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert));

        Event::assertDispatched(AlertChanged::class, 3);

        Event::assertDispatched(AlertChanged::class, function (AlertChanged $e) {
            $channels = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return $e->event->action === 'raised'
                && $channels === ['private-alerts', 'private-machines.'.$this->machine->id]
                && $e->broadcastAs() === 'alert.changed'
                && $e->broadcastWith()['severity'] === 'warning'
                && $e->broadcastWith()['actor'] === 'System'
                && $e->broadcastWith()['machine_name'] === $this->machine->name;
        });

        Event::assertDispatched(AlertChanged::class, fn (AlertChanged $e) => $e->event->action === 'escalated');

        Event::assertDispatched(AlertChanged::class, fn (AlertChanged $e) => $e->event->action === 'acknowledged'
            && $e->broadcastWith()['actor'] === 'Ada Admin'
            && $e->broadcastWith()['status'] === 'acknowledged');
    }

    public function test_a_broken_reverb_never_breaks_the_device_request_or_the_button_click(): void
    {
        Event::listen(AlertChanged::class, fn () => throw new \RuntimeException('Reverb is down'));

        $this->reading(45);

        $alert = Alert::sole();
        $this->assertSame('open', $alert->status);
        $this->assertTrue(Cache::has(SafeBroadcaster::DOWN_KEY));

        $admin = tap(User::factory()->create())->assignRole('admin');
        $this->actingAs($admin)->post(route('alerts.acknowledge', $alert))->assertRedirect();

        $this->assertSame('acknowledged', $alert->fresh()->status);
    }

    public function test_one_outage_pauses_live_alerts_and_live_readings_together(): void
    {
        Cache::put(SafeBroadcaster::DOWN_KEY, true, 30);
        Event::fake([AlertChanged::class]);

        $this->reading(45);

        Event::assertNotDispatched(AlertChanged::class);
        $this->assertSame(1, Alert::count());
    }

    public function test_a_user_who_can_view_alerts_may_subscribe_to_the_alerts_channel(): void
    {
        $this->useReverbDriver();

        $manager = tap(User::factory()->create())->assignRole('manager');

        $response = $this->actingAs($manager)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-alerts',
        ]);

        $response->assertOk()->assertJsonStructure(['auth']);
    }

    public function test_a_user_without_alerts_view_cannot_subscribe_to_the_alerts_channel(): void
    {
        $this->useReverbDriver();

        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));

        $this->actingAs($user)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-alerts',
        ])->assertForbidden();

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-alerts',
        ])->assertForbidden();
    }

    public function test_the_bell_row_is_written_immediately_and_email_and_push_are_queued(): void
    {
        $admin = tap(User::factory()->create())->assignRole('admin');
        $alert = Alert::create([
            'machine_id' => $this->machine->id, 'type' => 'threshold', 'metric' => 'temperature',
            'value' => 45, 'severity' => 'critical', 'status' => 'open', 'message' => 'Too hot.',
        ]);
        $notification = new MachineAlertNotification($alert);

        $this->assertSame(['database', 'broadcast', 'mail'], $notification->via($admin));
        $this->assertSame(['mail'], $notification->via(new \Illuminate\Notifications\AnonymousNotifiable));
        $this->assertSame(['database' => 'sync'], $notification->viaConnections());
        $this->assertSame(3, $notification->tries);
        $this->assertSame([10, 60], $notification->backoff());
        $this->assertTrue($notification->afterCommit);

        $this->assertSame(['database', 'broadcast', 'mail'], (new WorkOrderAssignedNotification(
            new \App\Models\WorkOrder
        ))->via($admin));
    }

    public function test_a_breach_leaves_a_bell_row_for_the_admin(): void
    {
        $admin = tap(User::factory()->create())->assignRole('admin');

        $this->reading(45);

        $row = $admin->notifications()->sole();
        $this->assertSame('alert', $row->data['kind']);
        $this->assertSame(Alert::sole()->id, $row->data['alert_id']);
        $this->assertSame('critical', $row->data['severity']);
    }
}
