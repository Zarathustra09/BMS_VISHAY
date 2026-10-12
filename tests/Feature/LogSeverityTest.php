<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineParameter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogSeverityTest extends TestCase
{
    use RefreshDatabase;

    private Machine $machine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->machine = Machine::factory()->create();
        $this->machine->generateDeviceKey('sev-key');
        MachineParameter::factory()->for($this->machine)->key('temperature')
            ->withThresholds(['warning_max' => 30, 'critical_max' => 40])->create();
        MachineParameter::factory()->for($this->machine)->key('humidity')
            ->withThresholds(['warning_max' => 70])->create();
    }

    /** @param  array<string, mixed>  $extra */
    private function sendReading(array $readings, array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/machine-logs', [
            'log_type' => 'sensor_reading',
            'readings' => $readings,
        ] + $extra, ['X-Device-Key' => 'sev-key']);
    }

    public function test_a_critical_breach_without_a_severity_is_stored_as_a_critical_log(): void
    {
        $this->sendReading(['temperature' => 60, 'humidity' => 99])
            ->assertCreated()
            ->assertJsonPath('data.severity', 'critical');

        $this->assertSame('critical', $this->machine->logs()->sole()->severity);
    }

    public function test_a_warning_breach_is_stored_as_a_warning_log(): void
    {
        $this->sendReading(['temperature' => 35])->assertCreated()->assertJsonPath('data.severity', 'warning');

        $this->assertSame('warning', $this->machine->logs()->sole()->severity);
    }

    public function test_the_worst_breach_in_a_reading_wins(): void
    {
        $this->sendReading(['temperature' => 35, 'humidity' => 99]);
        $this->sendReading(['temperature' => 45, 'humidity' => 99]);

        $this->assertSame(['warning', 'critical'], $this->machine->logs()->orderBy('id')->pluck('severity')->all());
    }

    public function test_a_reading_within_bounds_stays_info(): void
    {
        $this->sendReading(['temperature' => 22, 'humidity' => 50])->assertCreated();

        $this->assertSame('info', $this->machine->logs()->sole()->severity);
    }

    public function test_a_severity_the_device_sends_itself_is_kept_when_it_is_higher(): void
    {
        $this->sendReading(['temperature' => 22], ['severity' => 'error']);
        $this->sendReading(['temperature' => 35], ['severity' => 'critical']);
        $this->sendReading(['temperature' => 35], ['severity' => 'error']);

        $this->assertSame(['error', 'critical', 'error'], $this->machine->logs()->orderBy('id')->pluck('severity')->all());
    }

    public function test_a_machine_without_thresholds_is_left_alone(): void
    {
        $plain = Machine::factory()->create();
        $plain->generateDeviceKey('plain-key');
        MachineParameter::factory()->for($plain)->key('temperature')->create();

        $this->postJson('/api/machine-logs', ['log_type' => 'sensor_reading', 'readings' => ['temperature' => 999]], ['X-Device-Key' => 'plain-key'])
            ->assertCreated();

        $this->assertSame('info', $plain->logs()->sole()->severity);
    }

    public function test_the_logs_table_shows_the_derived_severity(): void
    {
        $this->sendReading(['temperature' => 60]);
        $admin = tap(\App\Models\User::factory()->create())->assignRole('admin');

        $row = $this->actingAs($admin)->getJson(route('machines.logs.data', $this->machine))->assertOk()->json('data.0');

        $this->assertStringContainsString('Critical', $row['severity']);
        $this->assertStringNotContainsString('Info', $row['severity']);
    }
}
