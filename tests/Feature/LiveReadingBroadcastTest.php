<?php

namespace Tests\Feature;

use App\Events\MachineReadingRecorded;
use App\Models\Machine;
use App\Models\MachineParameter;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class LiveReadingBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    private string $deviceKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->machine = Machine::factory()->create();
        $this->deviceKey = $this->machine->generateDeviceKey();

        MachineParameter::factory()->for($this->machine)->key('coolant_temp', 'Coolant temperature', '°C')->create();
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function postReading(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/machine-logs', $body, ['X-Device-Key' => $this->deviceKey]);
    }

    /**
     * Switches the app to the real Reverb driver for the rest of the test (phpunit.xml uses the
     * null driver so the suite never needs a server) with dummy credentials, so the Pusher-protocol
     * signing runs locally. The default driver is resolved lazily and routes/channels.php registers
     * its channels on whichever driver is default at boot, so both are redone here.
     */
    private function useReverbDriver(string $host = '127.0.0.1', int $port = 1): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
            'broadcasting.connections.reverb.options.host' => $host,
            'broadcasting.connections.reverb.options.port' => $port,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);

        app(BroadcastManager::class)->purge('reverb');

        require base_path('routes/channels.php');
    }

    public function test_a_stored_reading_is_broadcast_on_the_machines_private_channel(): void
    {
        Event::fake([MachineReadingRecorded::class]);

        $this->postReading([
            'log_type' => 'sensor_reading',
            'logged_at' => '2026-10-08 10:41:07',
            'readings' => ['coolant_temp' => 60],
        ])->assertCreated();

        Event::assertDispatched(MachineReadingRecorded::class, function (MachineReadingRecorded $event) {
            $payload = $event->broadcastWith();

            return $event->broadcastOn()[0]->name === 'private-machines.'.$this->machine->id
                && $event->broadcastAs() === 'reading.recorded'
                && $payload['log_id'] === $event->log->id
                && $payload['t'] === strtotime('2026-10-08 10:41:07')
                && $payload['readings'] == ['coolant_temp' => 60]
                && $payload['labels'] === ['time' => '10:41:07', 'hour' => '10:41', 'date' => 'Oct 8 10:41'];
        });
    }

    public function test_a_log_without_readings_is_not_broadcast(): void
    {
        Event::fake([MachineReadingRecorded::class]);

        $this->postReading(['log_type' => 'alarm', 'event' => 'Door ajar', 'severity' => 'warning'])->assertCreated();

        Event::assertNotDispatched(MachineReadingRecorded::class);
    }

    public function test_a_rejected_reading_is_not_broadcast(): void
    {
        Event::fake([MachineReadingRecorded::class]);

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 'hot']])
            ->assertUnprocessable();

        Event::assertNotDispatched(MachineReadingRecorded::class);
    }

    public function test_a_failing_broadcast_never_fails_the_devices_post(): void
    {
        Event::listen(MachineReadingRecorded::class, fn () => throw new RuntimeException('broadcast failed'));

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 60]])->assertCreated();

        $this->assertSame(['coolant_temp' => 60], $this->machine->logs()->sole()->readings);
    }

    public function test_after_a_failed_broadcast_the_next_readings_skip_the_attempt_for_a_while(): void
    {
        $attempts = 0;
        Event::listen(MachineReadingRecorded::class, function () use (&$attempts) {
            $attempts++;

            throw new RuntimeException('Reverb is down');
        });

        foreach ([10, 11, 12] as $value) {
            $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => $value]])->assertCreated();
        }

        // One failed attempt opened the breaker; the other two readings were stored without trying.
        $this->assertSame(1, $attempts);
        $this->assertSame(3, $this->machine->logs()->count());

        // Half a minute later it tries again (Reverb may be back).
        $this->travel(31)->seconds();
        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 13]])->assertCreated();
        $this->assertSame(2, $attempts);
    }

    public function test_an_unreachable_reverb_server_never_fails_the_devices_post(): void
    {
        // Port 1 on loopback: nothing is listening, exactly like Reverb being stopped.
        $this->useReverbDriver();

        $this->postReading(['log_type' => 'sensor_reading', 'readings' => ['coolant_temp' => 60]])->assertCreated();

        $this->assertSame(['coolant_temp' => 60], $this->machine->logs()->sole()->readings);
    }

    public function test_a_user_who_can_view_machines_may_subscribe_to_a_machines_channel(): void
    {
        $this->useReverbDriver();

        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-machines.'.$this->machine->id,
        ]);

        $response->assertOk()->assertJsonStructure(['auth']);
        $this->assertStringStartsWith('test-key:', $response->json('auth'));
    }

    public function test_a_user_without_the_machines_view_permission_cannot_subscribe(): void
    {
        $this->useReverbDriver();

        $this->actingAs(User::factory()->create())->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-machines.'.$this->machine->id,
        ])->assertForbidden();
    }

    public function test_a_guest_cannot_subscribe(): void
    {
        $this->useReverbDriver();

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-machines.'.$this->machine->id,
        ])->assertForbidden();
    }
}
